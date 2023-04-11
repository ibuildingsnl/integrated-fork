<?php

namespace Integrated\Bundle\TaxonomyBundle\Controller;

use Integrated\Common\Solr\Search\QueryFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ListController extends AbstractController
{
    public function __construct(
        private readonly QueryFactoryInterface $queryFactory,
    ) {
    }
}
