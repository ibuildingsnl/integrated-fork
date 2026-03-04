<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Extension;

use Integrated\Bundle\ContentBundle\Extension\ContentNavigatorColumn;
use Integrated\Bundle\ContentBundle\Extension\ContentNavigatorColumnProviderInterface;
use Integrated\Bundle\ContentBundle\Extension\ContentNavigatorColumnRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ContentNavigatorColumnRegistryTest extends TestCase
{
    public function testGetColumnsSortsByPriorityAndDeduplicatesByKey(): void
    {
        $highPriority = new class implements ContentNavigatorColumnProviderInterface {
            public function getColumns(): array
            {
                return [
                    new ContentNavigatorColumn('impressions', 'Impressions', 20),
                    new ContentNavigatorColumn('clicks', 'Clicks', 10),
                ];
            }

            public function getRowValues(array $rows, Request $request): array
            {
                return [];
            }
        };

        $lowerDuplicate = new class implements ContentNavigatorColumnProviderInterface {
            public function getColumns(): array
            {
                return [
                    new ContentNavigatorColumn('impressions', 'Lower duplicate', 1),
                    new ContentNavigatorColumn('position', 'Position', 5),
                ];
            }

            public function getRowValues(array $rows, Request $request): array
            {
                return [];
            }
        };

        $registry = new ContentNavigatorColumnRegistry([$lowerDuplicate, $highPriority]);
        $columns = $registry->getColumns();

        $this->assertCount(3, $columns);
        $this->assertSame('impressions', $columns[0]->getKey());
        $this->assertSame('clicks', $columns[1]->getKey());
        $this->assertSame('position', $columns[2]->getKey());
        $this->assertSame('Impressions', $columns[0]->getLabel());
    }

    public function testGetRowValuesMergesProviderOutputByContentId(): void
    {
        $providerA = new class implements ContentNavigatorColumnProviderInterface {
            public function getColumns(): array
            {
                return [];
            }

            public function getRowValues(array $rows, Request $request): array
            {
                return [
                    'a1' => ['impressions' => 100],
                    'a2' => ['impressions' => 50],
                ];
            }
        };

        $providerB = new class implements ContentNavigatorColumnProviderInterface {
            public function getColumns(): array
            {
                return [];
            }

            public function getRowValues(array $rows, Request $request): array
            {
                return [
                    'a1' => ['clicks' => 8],
                ];
            }
        };

        $registry = new ContentNavigatorColumnRegistry([$providerA, $providerB]);
        $values = $registry->getRowValues([], new Request());

        $this->assertSame(['impressions' => 100, 'clicks' => 8], $values['a1']);
        $this->assertSame(['impressions' => 50], $values['a2']);
    }
}
