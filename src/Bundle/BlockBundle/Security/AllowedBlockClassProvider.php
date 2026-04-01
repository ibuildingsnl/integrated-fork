<?php

declare(strict_types=1);

namespace Integrated\Bundle\BlockBundle\Security;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;

final class AllowedBlockClassProvider
{
    /** @var array<string, bool>|null */
    private ?array $allowedBlockClasses = null;

    public function __construct(
        private readonly MetadataFactoryInterface $metadataFactory,
    ) {
    }

    /**
     * Ensure user-provided class names can only instantiate registered block classes.
     * This prevents creating arbitrary classes through request input.
     */
    public function isAllowed(string $class): bool
    {
        $class = trim($class);
        if ($class === '' || !class_exists($class) || !is_subclass_of($class, Block::class)) {
            return false;
        }

        $classKey = strtolower(ltrim($class, '\\'));

        if ($this->allowedBlockClasses === null) {
            $this->allowedBlockClasses = [];
            foreach ($this->metadataFactory->getAllMetadata() as $metadata) {
                $metadataClass = trim((string) $metadata->getClass());
                if ($metadataClass === '') {
                    continue;
                }

                $this->allowedBlockClasses[strtolower(ltrim($metadataClass, '\\'))] = true;
            }
        }

        return isset($this->allowedBlockClasses[$classKey]);
    }
}
