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

use Integrated\Bundle\StorageBundle\Storage\Registry\FilesystemRegistry;
use Integrated\Bundle\StorageBundle\Storage\Resolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'storage:filesystem:list',
    description: 'Lists the configured filesystem(s)',
)]
class ListCommand extends Command
{
    private FilesystemRegistry $registry;
    private Resolver $resolverStorage;

    public function __construct(FilesystemRegistry $registry, Resolver $resolverStorage)
    {
        $this->registry = $registry;
        $this->resolverStorage = $resolverStorage;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('The <info>%command.name%</info> lists the existing filesystem(s).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->registry->getIterator() as $key => $filesystem) {
            $output->writeln(
                sprintf(
                    '<info>%s</info>: %s',
                    $key,
                    \get_class($filesystem->getAdapter())
                )
            );

            if ($options = $this->resolverStorage->getOptions($key)) {
                $output->writeln(
                    [
                        sprintf(
                            "\t resolver_class: %s",
                            $options['resolver_class']
                        ),
                        sprintf(
                            "\t public: %s",
                            $options['public']
                        ),
                    ]
                );
            }
        }

        return self::SUCCESS;
    }
}
