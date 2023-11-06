<?php

namespace Integrated\Bundle\IQLBundle\Infrastructure;

use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Stratadox\Sorting\ElementSorter;

final class PublicationSorter extends ElementSorter
{
    private \Closure $getter;

    public function __construct(?\Closure $getter = null)
    {
        $this->getter = $getter ?: fn (string $field) => $this->$field ?? null;
    }

    protected function valueFor($element, string $field)
    {
        if (!$element instanceof Publication) {
            return null;
        }
        // Fetch the value from content or publication
        return $this->getter->call($element->getContent(), $field) ?: $this->getter->call($element, $field);
    }
}
