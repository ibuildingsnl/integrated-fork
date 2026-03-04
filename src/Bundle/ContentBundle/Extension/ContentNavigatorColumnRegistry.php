<?php

namespace Integrated\Bundle\ContentBundle\Extension;

use Symfony\Component\HttpFoundation\Request;

class ContentNavigatorColumnRegistry
{
    /** @var iterable<ContentNavigatorColumnProviderInterface> */
    private iterable $providers;

    /**
     * @param iterable<ContentNavigatorColumnProviderInterface> $providers
     */
    public function __construct(iterable $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * @return ContentNavigatorColumn[]
     */
    public function getColumns(): array
    {
        $columns = [];

        foreach ($this->providers as $provider) {
            foreach ($provider->getColumns() as $column) {
                if (!$column instanceof ContentNavigatorColumn) {
                    continue;
                }

                $columns[] = $column;
            }
        }

        usort($columns, static function (ContentNavigatorColumn $left, ContentNavigatorColumn $right): int {
            return $right->getPriority() <=> $left->getPriority();
        });

        $deduped = [];
        foreach ($columns as $column) {
            $key = $column->getKey();
            if (isset($deduped[$key])) {
                continue;
            }
            $deduped[$key] = $column;
        }

        return array_values($deduped);
    }

    /**
     * @param array<int, mixed> $rows
     *
     * @return array<string, array<string, mixed>>
     */
    public function getRowValues(array $rows, Request $request): array
    {
        $values = [];

        foreach ($this->providers as $provider) {
            $providerValues = $provider->getRowValues($rows, $request);
            if (!\is_array($providerValues)) {
                continue;
            }

            foreach ($providerValues as $contentId => $contentValues) {
                if (!\is_array($contentValues)) {
                    continue;
                }

                $rowKey = trim((string) $contentId);
                if ('' === $rowKey) {
                    continue;
                }

                if (!isset($values[$rowKey])) {
                    $values[$rowKey] = [];
                }

                foreach ($contentValues as $columnKey => $columnValue) {
                    $columnKey = trim((string) $columnKey);
                    if ('' === $columnKey) {
                        continue;
                    }

                    $values[$rowKey][$columnKey] = $columnValue;
                }
            }
        }

        return $values;
    }
}
