<?php

namespace Integrated\Bundle\IQLBundle\Specification;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Stratadox\Specification\Contract\Specifies;
use Stratadox\Specification\Specification;

final class WithContent extends Specification
{
    public function __construct(
        private readonly string $search,
    ) {
    }

    public static function containing(string $text): Specifies
    {
        return new self($text);
    }

    public function isSatisfiedBy($object): bool
    {
        if (!$object instanceof Publication) {
            return false;
        }
        $content = $object->getContent();

        return (method_exists($content, 'getTitle') && str_contains($content->getTitle(), $this->search)) ||
            (method_exists($content, 'getContent') && str_contains($content->getContent(), $this->search));
    }
}
