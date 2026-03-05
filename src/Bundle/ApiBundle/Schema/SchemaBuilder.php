<?php

namespace Integrated\Bundle\ApiBundle\Schema;

use Integrated\Bundle\ApiBundle\Registry\EndpointRegistry;

class SchemaBuilder
{
    public function __construct(private readonly EndpointRegistry $endpointRegistry)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(string $contract, string $version): array
    {
        return [
            'contract' => $contract,
            'version' => $version,
            'resources' => $this->endpointRegistry->all($contract, $version),
        ];
    }
}
