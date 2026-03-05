<?php

namespace Integrated\Bundle\ApiBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

class ApiRouteLoader extends Loader
{
    public const TYPE_PUBLIC_V1 = 'integrated_api_public_v1';
    public const TYPE_ADMIN_V1 = 'integrated_api_admin_v1';

    /**
     * @var array<string, bool>
     */
    private array $loaded = [];

    public function __construct(private readonly RouteProviderRegistry $routeProviderRegistry)
    {
    }

    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        [$contract, $version] = match ($type) {
            self::TYPE_PUBLIC_V1 => ['public', 'v1'],
            self::TYPE_ADMIN_V1 => ['admin', 'v1'],
            default => throw new \InvalidArgumentException(sprintf('Unsupported API route loader type "%s".', (string) $type)),
        };

        $key = sprintf('%s:%s', $contract, $version);
        if (($this->loaded[$key] ?? false) === true) {
            throw new \RuntimeException(sprintf('API route loader already loaded for %s.', $key));
        }

        $collection = new RouteCollection();

        foreach ($this->routeProviderRegistry->getProviders($contract, $version) as $provider) {
            $collection->addCollection($provider->provideRoutes());
        }

        $this->loaded[$key] = true;

        return $collection;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return in_array($type, [self::TYPE_PUBLIC_V1, self::TYPE_ADMIN_V1], true);
    }
}
