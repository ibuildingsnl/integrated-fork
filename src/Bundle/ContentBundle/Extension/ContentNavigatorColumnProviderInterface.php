<?php

namespace Integrated\Bundle\ContentBundle\Extension;

use Symfony\Component\HttpFoundation\Request;

interface ContentNavigatorColumnProviderInterface
{
    /**
     * @return ContentNavigatorColumn[]
     */
    public function getColumns(): array;

    /**
     * @param array<int, mixed> $rows
     *
     * @return array<string, array<string, mixed>>
     */
    public function getRowValues(array $rows, Request $request): array;
}
