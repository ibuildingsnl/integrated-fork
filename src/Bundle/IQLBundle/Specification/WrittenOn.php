<?php

namespace Integrated\Bundle\IQLBundle\Specification;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Stratadox\Specification\Contract\Specifies;
use Stratadox\Specification\Specification;

final class WrittenOn extends Specification
{
    public function __construct(
        private readonly string $date,
        private readonly string $format,
    ) {
    }

    public static function date(string $date, string $format = 'd-m-Y'): Specifies
    {
        return new self($date, $format);
    }

    public function isSatisfiedBy($object): bool
    {
        return $object instanceof Publication &&
            $object->getContent()->getCreatedAt()->format($this->format) === $this->date;
    }
}
