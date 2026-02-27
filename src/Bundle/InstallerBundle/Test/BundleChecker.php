<?php

namespace Integrated\Bundle\InstallerBundle\Test;

use Symfony\Component\Finder\Finder;

class BundleChecker
{
    public const BUNDLES_DIRECTORY = '/../../';

    /** @var array<string, mixed> */
    private array $bundles;

    /**
     * Migrations constructor.
     */
    /** @param array<string, mixed> $bundles */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /** @return list<string> */
    public function execute(): array
    {
        $directory = realpath(__DIR__.self::BUNDLES_DIRECTORY);
        if (!\is_string($directory)) {
            return ['Unable to locate bundle directory'];
        }

        $finder = new Finder();

        $finder->directories()->in($directory)->depth(0);

        $errors = [];
        foreach ($finder as $directory) {
            $directory = $directory->getFilename();
            if (isset($this->bundles['Integrated'.$directory])) {
                // bundle found
                continue;
            }
            $errors[] = 'Integrated'.$directory.' has not been loaded';
        }

        return $errors;
    }
}
