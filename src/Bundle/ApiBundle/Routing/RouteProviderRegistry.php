<?php

namespace Integrated\Bundle\ApiBundle\Routing;

class RouteProviderRegistry
{
    /**
     * @var array<string, RouteProviderInterface[]>
     */
    private array $providers = [];

    public function addProvider(RouteProviderInterface $provider, string $contract, string $version): void
    {
        $this->providers[$this->key($contract, $version)][] = $provider;
    }

    /**
     * @return RouteProviderInterface[]
     */
    public function getProviders(string $contract, string $version): array
    {
        return $this->providers[$this->key($contract, $version)] ?? [];
    }

    private function key(string $contract, string $version): string
    {
        return sprintf('%s:%s', $contract, $version);
    }
}
