<?php

namespace Integrated\Bundle\IQLBundle\Specification;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
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

        return $content instanceof Article && (
            str_contains($content->getTitle(), $this->search) ||
            str_contains($content->getContent(), $this->search)
        );
    }
}
