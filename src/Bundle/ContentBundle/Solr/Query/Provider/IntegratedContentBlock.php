<?php

namespace Integrated\Bundle\ContentBundle\Solr\Query\Provider;

use Integrated\Bundle\ContentBundle\Document\Block\ContentBlock;
use Integrated\Bundle\ContentBundle\Solr\Query\Converter\ContentBlockConverter;
use Integrated\Bundle\ContentBundle\Solr\Query\Type\IntegratedContentBlock as QueryType;
use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Solarium\Client;
use Symfony\Component\HttpFoundation\Request;

class IntegratedContentBlock
{
    private array $registry = [];

    public function __construct(private QueryFactoryInterface $factory, private Client $client, private PaginatorInterface $paginator)
    {
    }

    public function get(ContentBlock $block, Request $request, array $options = []): PaginationInterface
    {
        $converter = new ContentBlockConverter();

        $settings = $converter->convert($block, $request, $options);

        if (\count($this->registry)) {
            $settings['exclude_ids'] = $this->registry;
        }

        $query = $this->factory->createQuery(QueryType::class, $settings + ['published' => true]);

        $pageParam = $block->getId().'-page';
        $page = (int) $request->query->get($pageParam);

        $pagination = $this->paginator->paginate(
            [
                $this->client,
                $query->getQuery(),
            ],
            $page < 1 ? 1 : $page,
            $block->getItemsPerPage(),
            [
                'pageParameterName' => $pageParam,
                'maxItems' => $block->getMaxItems(),
                'sortFieldParameterName' => null,
            ]
        );

        if ($options['exclude'] ?? false) {
            foreach ($pagination as $document) {
                $this->registry[] = $document->offsetGet('type_id'); // exclude already shown items
            }
        }

        return $pagination;
    }
}
