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

use Integrated\Bundle\StorageBundle\Storage\Filesystem\CleanFilesystem;
use Integrated\Bundle\StorageBundle\Storage\Registry\FilesystemRegistry;
use Integrated\Common\Storage\Database\DatabaseInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'storage:filesystem:clean',
    description: 'Remove unused files from the storage',
)]
class CleanCommand extends Command
{
    private DatabaseInterface $database;
    private FilesystemRegistry $registry;

    public function __construct(DatabaseInterface $database, FilesystemRegistry $registry)
    {
        $this->database = $database;
        $this->registry = $registry;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filesystem', InputArgument::REQUIRED, 'Name of the filesystem to clean');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Target directory for movement of the used files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filesystem = $input->getArgument('filesystem');
        $directory = $input->getArgument('directory');

        $cleanFileSystem = new CleanFilesystem($this->registry, $this->database);
        $cleanFileSystem->clean($filesystem, $directory);

        $output->writeln(\sprintf('Cleanable files for %s have been moved to %s', $filesystem, $directory));

        return self::SUCCESS;
    }
}
