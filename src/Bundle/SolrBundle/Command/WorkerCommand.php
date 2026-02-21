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

use Integrated\Common\Solr\Task\Worker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'solr:worker:run',
    description: 'Execute worker task from the queue',
)]
class WorkerCommand extends Command
{
    use LockableTrait;

    private Worker $worker;

    public function __construct(Worker $worker)
    {
        $this->worker = $worker;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('tasks', 't', InputOption::VALUE_REQUIRED, 'The maximum number of tasks to execute in one worker run', null)
            ->setHelp('
The <info>%command.name%</info> command starts a solr worker run.

<info>php %command.full_name%</info>
');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock(self::class.md5(__DIR__.$this->getName()))) {
            return self::SUCCESS;
        }

        try {
            if (null !== ($tasks = $input->getOption('tasks'))) {
                $this->worker->setOption('tasks', (int) $tasks);
            }

            $this->worker->execute();
        } finally {
            $this->release();
        }

        return self::SUCCESS;
    }
}
