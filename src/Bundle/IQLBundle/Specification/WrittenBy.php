<?php

namespace Integrated\Bundle\IQLBundle\Specification;

use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\Author;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Stratadox\Specification\Contract\Specifies;
use Stratadox\Specification\Specification;

final class WrittenBy extends Specification
{
    public function __construct(
        private readonly string $author,
    ) {
    }

    public static function author(string $who): Specifies
    {
        return new self($who);
    }

    public function isSatisfiedBy($object): bool
    {
        if (!$object instanceof Publication) {
            return false;
        }

        $content = $object->getContent();
        if (!$content instanceof Article) {
            return false;
        }

        foreach (array_map(fn (Author $a) => $a->getPerson(), $content->getAuthors()) as $person) {
            if (strpos(
                $person->getFirstName() . ' ' . $person->getLastName() . ' ' . $person->getNickname(),
                $this->author
            )) {
                return true;
            }
        }

        return false;
    }
}
