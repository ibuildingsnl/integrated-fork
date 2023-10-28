<?php

namespace Integrated\Bundle\IQLBundle;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Stratadox\Specification\Contract\Specifies;
use Stratadox\Specification\Specification;

final class WithContentType extends Specification
{
    public function __construct(
        public readonly string $type,
    ) {
    }

    public static function of(string $type): Specifies
    {
        return new self($type);
    }

    public function isSatisfiedBy($object): bool
    {
        return $object instanceof Content && $object->getContentType() === $this->type;
    }
}
