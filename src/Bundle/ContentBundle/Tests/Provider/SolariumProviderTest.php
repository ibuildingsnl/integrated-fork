<?php

namespace Integrated\Bundle\ContentBundle\Tests\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Document\SearchSelection\SearchSelection;
use Integrated\Bundle\ContentBundle\Provider\SolariumProvider;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\TestCase;
use Solarium\Client;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\HttpFoundation\Request;

class SolariumProviderTest extends TestCase
{
    public function testBlockSearchMatchesAllTermsByDefault(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('createSelect')->willReturn(new Query());

        $relations = $this->createMock(ObjectRepository::class);
        $relations->method('findAll')->willReturn([]);

        $manager = $this->createMock(DocumentManager::class);
        $manager->method('getRepository')->with(Relation::class)->willReturn($relations);

        $provider = new TestSolariumProvider(
            $client,
            $manager,
            $this->createMock(PaginatorInterface::class),
        );

        $block = new ContentBlock('search-results');
        $request = new Request(['search-results-search' => 'echte bakkers 2026']);
        $request->attributes->set('_channel', 'bakkersinbedrijf');

        $query = $provider->exposedGetQuery($block, $request);

        self::assertSame(Query::QUERY_OPERATOR_AND, $query->getQueryDefaultOperator());
        self::assertSame('echte bakkers 2026', $query->getQuery());
        self::assertArrayHasKey('bf', $query->getParams());
        self::assertStringContainsString('recip(ms(NOW,pub_time)', (string) $query->getParams()['bf']);
    }

    public function testSearchRelevanceSortUsesPublicationDateAsTieBreaker(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('createSelect')->willReturn(new Query());

        $relations = $this->createMock(ObjectRepository::class);
        $relations->method('findAll')->willReturn([]);

        $manager = $this->createMock(DocumentManager::class);
        $manager->method('getRepository')->with(Relation::class)->willReturn($relations);

        $provider = new TestSolariumProvider(
            $client,
            $manager,
            $this->createMock(PaginatorInterface::class),
        );

        $selection = (new SearchSelection())
            ->setId('search')
            ->setFilters(['sort' => 'rel']);

        $block = new ContentBlock('search-results');
        $block->setSearchSelection($selection);

        $request = new Request(['search-results-search' => 'Brioche']);
        $request->attributes->set('_channel', 'bakkersinbedrijf');

        $query = $provider->exposedGetQuery($block, $request);

        self::assertSame([
            'score' => 'desc',
            'pub_time' => 'desc',
        ], $query->getSorts());
    }
}

final class TestSolariumProvider extends SolariumProvider
{
    /**
     * @param array<string, mixed> $options
     */
    public function exposedGetQuery(ContentBlock $subject, Request $request, array $options = []): Query
    {
        return $this->getQuery($subject, $request, $options);
    }
}
