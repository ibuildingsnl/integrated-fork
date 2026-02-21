<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Command\Filesystem;

use Integrated\Bundle\StorageBundle\Storage\Collection\Map\ContentReflectionMap;
use Integrated\Bundle\StorageBundle\Storage\Collection\Map\FileMap;
use Integrated\Bundle\StorageBundle\Storage\Collection\Walk\DocumentWalk;
use Integrated\Bundle\StorageBundle\Storage\Collection\Walk\FilesystemWalk;
use Integrated\Bundle\StorageBundle\Storage\Mapping\MetadataFactoryInterface;
use Integrated\Bundle\StorageBundle\Storage\Registry\FilesystemRegistry;
use Integrated\Bundle\StorageBundle\Storage\Util\ProgressIteratorUtil;
use Integrated\Common\Storage\Database\DatabaseInterface;
use Integrated\Common\Storage\DecisionInterface;
use Integrated\Common\Storage\ManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'storage:filesystem:add',
    description: 'Add files into the filesystem',
)]
class AddCommand extends Command
{
    private DatabaseInterface $database;
    private FilesystemRegistry $registry;
    private ManagerInterface $storage;
    private DecisionInterface $decision;
    private MetadataFactoryInterface $metadata;

    public function __construct(
        DatabaseInterface $database,
        FilesystemRegistry $registry,
        ManagerInterface $storage,
        DecisionInterface $decision,
        MetadataFactoryInterface $metadata,
    ) {
        $this->database = $database;
        $this->registry = $registry;
        $this->storage = $storage;
        $this->decision = $decision;
        $this->metadata = $metadata;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filesystem', InputArgument::REQUIRED, 'Name of the filesystem add files to');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = $input->getArgument('filesystem');

        if ($this->registry->exists($filesystem)) {
            // This we'll need to do some work
            $iteratorUtil = new ProgressIteratorUtil($this->database->getObjects(), $output);

            $output->writeln('Running four steps; fetch, check for adding, writing file and save database');

            $iteratorUtil
                ->map(ContentReflectionMap::storageProperties($this->metadata))
                ->map(FileMap::documentAllowed($this->decision, $filesystem))
                ->walk(FilesystemWalk::add($this->storage, $this->metadata, $filesystem))
                ->walk(DocumentWalk::save($this->database));
        } else {
            throw new \InvalidArgumentException(\sprintf('The filesystem %s does not exist', $filesystem));
        }

        return self::SUCCESS;
    }
}
