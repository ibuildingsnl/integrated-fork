<?php

namespace Integrated\Bundle\ApiBundle\Registry;

use Integrated\Bundle\ApiBundle\Endpoint\EndpointHandlerInterface;

class EndpointRegistry
{
    /**
     * @var array<string, EndpointDefinition>
     */
    private array $definitions = [];

    /**
     * @param string[] $scopes
     */
    public function register(
        string $contract,
        string $version,
        string $resource,
        string $operation,
        EndpointHandlerInterface $handler,
        array $scopes = []
    ): void {
        $key = $this->buildKey($contract, $version, $resource, $operation);
        $this->definitions[$key] = new EndpointDefinition($contract, $version, $resource, $operation, $handler, $scopes);
    }

    public function get(string $contract, string $version, string $resource, string $operation): ?EndpointDefinition
    {
        $key = $this->buildKey($contract, $version, $resource, $operation);

        return $this->definitions[$key] ?? null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(string $contract, string $version): array
    {
        $result = [];

        foreach ($this->definitions as $definition) {
            if ($definition->getContract() !== $contract || $definition->getVersion() !== $version) {
                continue;
            }

            $result[] = [
                'resource' => $definition->getResource(),
                'operation' => $definition->getOperation(),
                'scopes' => $definition->getScopes(),
            ];
        }

        usort($result, static function (array $left, array $right): int {
            return [$left['resource'], $left['operation']] <=> [$right['resource'], $right['operation']];
        });

        return $result;
    }

    private function buildKey(string $contract, string $version, string $resource, string $operation): string
    {
        return sprintf('%s:%s:%s:%s', $contract, $version, $resource, $operation);
    }
}
