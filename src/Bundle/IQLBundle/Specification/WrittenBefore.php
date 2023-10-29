<?php

namespace Integrated\Bundle\IQLBundle\Specification;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Stratadox\Specification\Contract\Specifies;
use Stratadox\Specification\Specification;

final class WrittenBefore extends Specification
{
    public function __construct(
        private readonly \DateTimeInterface $date,
    ) {
    }

    public static function date(string $date, string $format = 'd-m-Y'): Specifies
    {
        return new self(\DateTimeImmutable::createFromFormat($format, $date));
    }

    public function isSatisfiedBy($object): bool
    {
        return $object instanceof Publication && $object->getContent()->getCreatedAt() < $this->date;
    }
}
