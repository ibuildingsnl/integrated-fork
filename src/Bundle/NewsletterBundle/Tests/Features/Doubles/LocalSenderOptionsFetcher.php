<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles;

use Integrated\Bundle\NewsletterBundle\Service\SenderOptionsFetcher;

final class LocalSenderOptionsFetcher implements SenderOptionsFetcher
{
    private array $list = [];

    public function retrieve(): array
    {
        return $this->list;
    }

    public function add(string $id, string $email): void
    {
        $this->list[$id] = $email;
    }
}
