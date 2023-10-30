<?php

namespace Integrated\Bundle\IQLBundle\Specification;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Stratadox\Specification\Contract\Specifies;
use Stratadox\Specification\Specification;

final class PublishedTo extends Specification
{
    public function __construct(
        private readonly string $channel,
    ) {
    }

    public static function channel(string $which): Specifies
    {
        return new self($which);
    }

    public function isSatisfiedBy($object): bool
    {
        return $object instanceof Publication && (
            $object->getChannel()->getId() === $this->channel ||
            $object->getChannel()->getName() === $this->channel
        );
    }
}
