<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles;

use Integrated\Bundle\NewsletterBundle\Service\RecipientListOptionsFetcher;

final class LocalRecipientListOptionsFetcher implements RecipientListOptionsFetcher
{
    private array $list = [];

    public function retrieve(int $amount = 50, int $start = 0): array
    {
        return array_slice($this->list, $start, $amount, true);
    }

    public function add(string $id, string $name): void
    {
        $this->list[$id] = $name;
    }
}
