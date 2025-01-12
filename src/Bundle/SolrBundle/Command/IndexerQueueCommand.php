<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\SolrBundle\Command;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Queue\QueueInterface;
use Integrated\Common\Solr\Indexer\Job;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(
    name: 'solr:indexer:queue',
    description: 'Queue all the content of the given content type for solr indexing',
)]
class IndexerQueueCommand extends Command
{
    private DocumentManager $documentManager;
    private QueueInterface $queue;
    private ResolverInterface $resolver;

    public function __construct(DocumentManager $documentManager, QueueInterface $queue, ResolverInterface $resolver)
    {
        $this->documentManager = $documentManager;
        $this->queue = $queue;
        $this->resolver = $resolver;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::IS_ARRAY, 'One or more content types that need to be indexed')
            ->addOption(
                'full',
                'f',
                InputOption::VALUE_NONE,
                'Do a full index of all the content this will override any given content types'
            )
            ->addOption(
                'delete',
                null,
                InputOption::VALUE_NONE,
                'Delete all the content for the given content types or if none given clear out the whole index'
            )
            ->addOption(
                'commit',
                null,
                InputOption::VALUE_NONE,
                'Queue a commit'
            )
            ->addOption('ignore', 'i', InputOption::VALUE_NONE, 'Ignore content types that do not exist')
            ->setHelp('
The <info>%command.name%</info> command starts a index of the site.

<info>php %command.full_name%</info>
');
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //  validate the content types unless validation is ignored

        if ($input->getArgument('id') && !$input->getOption('ignore')) {
            if ($code = $this->executeValidation($input, $output)) {
                return $code;
            }
        }

        if ($input->getOption('delete')) {
            return $this->executeDelete($input, $output);
        }

        if ($input->getOption('commit')) {
            $this->doIndexCommit();

            return self::SUCCESS;
        }

        if (!$input->getArgument('id') && !$input->getOption('full')) {
            throw new \InvalidArgumentException(
                'You need to give one or more content types or choose the --full or --delete option'
            );
        }

        return $this->executeIndex($input, $output);
    }

    /**
     * validate the ids in de input.
     *
     * @throws \InvalidArgumentException
     */
    private function executeValidation(InputInterface $input, OutputInterface $output): int
    {
        $types = [];

        foreach ($this->resolver->getTypes() as $type) {
            $types[$type->getId()] = $type->getId();
        }

        $invalid = [];

        foreach ($input->getArgument('id') as $id) {
            if (!isset($types[$id])) {
                $invalid[] = $id;
            }
        }

        if ($invalid) {
            $text = sprintf('The content types "%s" do not exists', implode(', ', $invalid));

            if ($input->getOption('no-interaction')) {
                throw new \InvalidArgumentException($text);
            }

            // ask the user if he/she want to continue or not.

            $output->writeln($text);

            if (!$this->getHelper('question')->ask(
                $input,
                $output,
                new ConfirmationQuestion('Would you want to continue? [y/N] ', false)
            )) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    /**
     * queue a delete on the solr index.
     */
    private function executeDelete(InputInterface $input, OutputInterface $output): int
    {
        $this->doIndexCleanup($input->getArgument('id'));
        $this->doIndexCommit();

        return self::SUCCESS;
    }

    /**
     * queue the indexing of content in to solr.
     */
    private function executeIndex(InputInterface $input, OutputInterface $output): int
    {
        // Don't hydrate for performance reasons
        $builder = $this->documentManager->createQueryBuilder(Content::class);
        $builder->select('id', 'contentType', 'class')
            ->hydrate(false)
            ->immortal(true)
            ->setRewindable(false);

        if (!$input->getOption('full')) {
            $builder->field('contentType')->in($input->getArgument('id'));
        }

        $count = $builder->count()->getQuery()->execute();
        $result = $builder->find()->getQuery()->execute();

        if ($input->getOption('full')) {
            // The entire site is going to be reindex so everything that is now in the queue
            // will be redone so just clear it so content is not double indexed.

            $this->queue->clear();
        }

        if ($count) {
            $progress = new ProgressBar($output, $count);

            $progress->setRedrawFrequency(min(max(floor($count / 250), 1), 100));
            $progress->setFormat('verbose');

            $progress->start($count);

            // get the current time as it will be required at the end for the solr clean up.

            $date = new \DateTime();

            $this->doIndex($result, $progress);
            $this->doIndexCleanup($input->getArgument('id'), $date);
            $this->doIndexCommit();

            $progress->display();
            $progress->finish();
        } else {
            // No content was found but that does not mean nothing should be done. As the solr
            // index should reflect the database so delete all the content for the matching
            // content types out the solr index.

            $this->doIndexCleanup($input->getArgument('id'));
            $this->doIndexCommit();
        }

        $this->documentManager->clear();

        return self::SUCCESS;
    }

    /**
     * Add all the documents in the cursor to the solr queue.
     */
    private function doIndex(object $cursor, ProgressBar $progress): void
    {
        // the document manager need to be cleared from time to time so this counter keeps
        // track of that.

        $count = 0;

        foreach ($cursor as $document) {
            $progress->advance();

            $job = new Job('ADD');

            $contentType = $document['contentType'] ?? '';

            $job->setOption('document.id', $contentType.'-'.$document['_id']);

            $job->setOption('document.data', json_encode(['id' => $document['_id']]));
            $job->setOption('document.class', $document['class']);
            $job->setOption('document.format', 'json');

            $this->queue->push($job);

            if (($count++ % 1000) == 0) {
                $this->documentManager->clear();
            }
        }
    }

    /**
     * delete all the types or everything if none is given.
     */
    private function doIndexCleanup(array $types, ?\DateTime $date = null): void
    {
        $query = [];

        if ($types) {
            $query[] = 'type_name:("'.implode('" OR "', $types).'")';
        } else {
            $query[] = '*:*';
        }

        if ($date) {
            $date = clone $date;
            $date->setTimezone(new \DateTimeZone('UTC'));

            $query[] = '-_time_:['.$date->format('Y-m-d\TG:i:s\Z').' TO *]';
        }

        // Delete everything else that did not got a update or does not exist anymore in the
        // database.

        $job = new Job('DELETE');
        $job->setOption('query', implode(' ', $query));

        $this->queue->push($job, 1);
    }

    /**
     * close up with a commit.
     */
    protected function doIndexCommit(): void
    {
        $this->queue->push(new Job('COMMIT', ['softcommit' => 'true']), 2);
    }
}
