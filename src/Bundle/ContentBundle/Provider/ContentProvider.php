<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Provider;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use Integrated\Bundle\WorkflowBundle\Solr\Extension\WorkflowExtension;
use Solarium\Client;
use Solarium\Component\Result\Facet\Field;
use Solarium\QueryType\Select\Query\FilterQuery;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

/**
 * @author Patrick Mestebeld <patrick@e-active.nl>
 */
class ContentProvider
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var WorkflowExtension
     */
    private $workflowExtension;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    /**
     * ContentProvider constructor.
     *
     * @param bool $workflowExtension
     */
    public function __construct(
        Client $client,
        DocumentManager $dm,
        TokenStorageInterface $tokenStorage,
        AuthorizationChecker $authorizationChecker,
        $workflowExtension = false,
    ) {
        $this->client = $client;
        $this->dm = $dm;
        $this->tokenStorage = $tokenStorage;
        $this->workflowExtension = $workflowExtension;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getFilterOptionsFromSolr(Request $request, $dateFilterGap, $contentTypeSelectOptions): array
    {
        $query = $this->client->createSelect();

        $helper = $query->getHelper();
        $filter = function ($param) use ($helper) {
            return $helper->escapePhrase($param);
        };

        // Filter on ContentType
        $contentType = $request->query->all('contenttypes');
        if (!\count($contentType)) {
            $contentType = [];
            foreach ($contentTypeSelectOptions as $contentTypeSelectOption) {
                $contentType[] = $contentTypeSelectOption->getId();
            }
        }

        if (\count($contentType)) {
            $contentTypesQuery = $query->createFilterQuery('contenttypes');
            $this->setContentTypes($contentType, $contentTypesQuery, $filter, $request);
        }

        // Filter on Category
        if ($selectedCategory = $request->query->get('MediaTaxonomy')) {
            $relation = $this->dm->getRepository(Relation::class)->find('media_taxonomy');

            // No Results;
            if (null === $relation) {
                return [];
            }

            $name = preg_replace('/[^a-zA-Z]/', '', $relation->getName());

            $query
                ->createFilterQuery($name)
                ->addTag($name)
                ->setQuery('facet_'.$relation->getId().': ((%1%))', [implode(') OR (', $selectedCategory)]);
        }

        // Filter on dates
        $facetSet = $query->getFacetSet();
        $facet = $facetSet->createFacetRange('pub_created');
        $facet->setField('pub_created');
        $facet->setStart('2022-01-01T00:00:00Z'); // TODO Can we fill this dynamically with Lowest?
        $facet->setGap($dateFilterGap);
        $facet->setEnd(date('Y-m-d').'T'.date('H:i:s').'Z');

        $resultSet = $this->client->select($query);

        /** @var Field $facet */
        $facet = $resultSet->getFacetSet()->getFacet('pub_created');

        $facetValues = $facet->getValues();

        $result = [];
        foreach ($facetValues as $key => $val) {
            $yyyy_mm_dd = substr($key, 0, 10);

            if ($val !== 0) {
                $result[$yyyy_mm_dd] = $val;
            }
        }

        return $result;
    }

    public function getContentFromSolr(Request $request, $limit, $offset = 0, $countResultset = false): array|int
    {
        $query = $this->client->createSelect();

        // If the request query contains a relation parameter we need to fetch all the targets of the relation in order
        // to filter on these targets.
        $relations = $request->query->get('relation');
        if (null !== $relations) {
            $contentType = [];
            /* @var Relation $relation */
            foreach ($relations as $key => $value) {
                if ($relation = $this->dm->getRepository(Relation::class)->find($key)) {
                    foreach ($relation->getSources() as $source) {
                        $contentType[] = $source->getId();
                    }
                }
            }
        } else {
            $contentType = $request->query->all('contenttypes');
        }

        $helper = $query->getHelper();
        $filter = function ($param) use ($helper) {
            return $helper->escapePhrase($param);
        };

        // If the request query contains a properties parameter we need to fetch all the targets of the relation in order
        // to filter on these targets.
        $propertiesfilter = $request->query->get('properties');
        if (\is_array($propertiesfilter)) {
            $query
                ->createFilterQuery('properties')
                ->addTag('properties')
                ->setQuery('facet_properties: ((%1%))', [implode(') OR (', array_map($filter, $propertiesfilter))]);
        }

        /* @var Relation $relation */
        foreach ($request->query->get('relation') as $relationId => $value) {
            $relation = $this->dm->getRepository(Relation::class)->find($relationId);
            $relationfilter = $value;

            if (\is_array($relationfilter)) {
                $query
                    ->createFilterQuery($relationId)
                    ->addTag($relationId)
                    ->setQuery('facet_'.$relation->getId().': ((%1%))', [implode(') OR (', array_map($filter, $relationfilter))]);
            }
        }

        if ($contentType) {
            $contentTypesQuery = $query->createFilterQuery('contenttypes')->addTag('contenttypes');
            $this->setContentTypes($contentType, $contentTypesQuery, $filter, $request);
        }

        // If the workflow bundle is loaded then only display the results that the
        // user has read rights to
        if ($this->workflowExtension) {
            $this->addWorkflowFilter($query);
        }

        $activeBrands = $request->query->get('brands');
        if (\is_array($activeBrands)) {
            if (\count($activeBrands)) {
                $query
                    ->createFilterQuery('brands')
                    ->addTag('brands')
                    ->setQuery('facet_brands: ((%1%))', [implode(') OR (', array_map($filter, $activeBrands))]);
            }
        }

        $activeChannels = $request->query->get('channels');
        if (\is_array($activeChannels)) {
            if (\count($activeChannels)) {
                $query
                    ->createFilterQuery('channels')
                    ->addTag('channels')
                    ->setQuery('facet_channels: ((%1%))', [implode(') OR (', array_map($filter, $activeChannels))]);
            }
        }

        $activeStates = $request->query->get('workflow_state');
        if (\is_array($activeStates)) {
            if (\count($activeStates)) {
                $query
                    ->createFilterQuery('workflow_state')
                    ->addTag('workflow_state')
                    ->setQuery('facet_workflow_state: ((%1%))', [implode(') OR (', array_map($filter, $activeStates))]);
            }
        }

        $activeAssigned = $request->query->get('workflow_assigned');
        if (\is_array($activeAssigned)) {
            if (\count($activeAssigned)) {
                $query
                    ->createFilterQuery('workflow_assigned')
                    ->addTag('workflow_assigned')
                    ->setQuery('facet_workflow_assigned: ((%1%))', [implode(') OR (', array_map($filter, $activeAssigned))]);
            }
        }

        $activeAuthors = $request->query->get('authors');
        if (\is_array($activeAuthors)) {
            if (\count($activeAuthors)) {
                $query
                    ->createFilterQuery('authors')
                    ->addTag('authors')
                    ->setQuery('facet_authors: ((%1%))', [implode(') OR (', array_map($filter, $activeAuthors))]);
            }
        }

        $hasFields = $request->query->get('hasFields');
        if (\is_array($hasFields)) {
            foreach ($hasFields as $field) {
                $query
                    ->createFilterQuery('hasField_'.$field)
                    ->setQuery($field.':[* TO *]');
            }
        }

        // sorting
        $sort_default = 'time';
        $sort_options = [
            'rel' => ['name' => 'rel', 'field' => 'score', 'label' => 'relevance', 'order' => 'desc'],
            'time' => ['name' => 'time', 'field' => 'pub_time', 'label' => 'publication date', 'order' => 'desc'],
            'changed' => ['name' => 'changed', 'field' => 'pub_edited', 'label' => 'date modified', 'order' => 'desc'],
            'created' => ['name' => 'created', 'field' => 'pub_created', 'label' => 'date created', 'order' => 'desc'],
            'title' => ['name' => 'title', 'field' => 'title_sort', 'label' => 'title', 'order' => 'asc'],
            'random' => ['name' => 'random', 'field' => 'random_'.mt_rand(), 'label' => 'random', 'order' => 'desc'],
            'rank' => ['name' => 'rank', 'field' => 'rank', 'label' => 'rank', 'order' => 'asc'],
        ];
        $order_options = [
            'asc' => 'asc',
            'desc' => 'desc',
        ];

        if ($ids = $request->get('ids')) {
            $ids = array_filter(explode(',', $ids), function ($value) {
                return preg_match('/[a-z0-9]{32}/', $value);
            });
            if (\count($ids)) {
                $query->createFilterQuery('ids')->setQuery('type_id: ("'.implode('" OR "', $ids).'")');
            }
        }

        if ($q = $request->get('q')) {
            $edismax = $query->getEDisMax();
            $edismax->setQueryFields('title content');
            $edismax->setMinimumMatch('75%');

            $query->setQuery($q);

            $sort_default = 'rel';
        } else {
            // relevance only available when sorting on specific query
            unset($sort_options['rel']);
        }

        $sort = $request->query->get('sort', $sort_default);
        $sort = trim(strtolower($sort));
        $sort = \array_key_exists($sort, $sort_options) ? $sort : $sort_default;

        $query->addSort($sort_options[$sort]['field'], \in_array($request->query->get('order'), $order_options) ? $request->query->get('order') : $sort_options[$sort]['order']);

        $query->setStart($offset);

        $query->setRows($limit);

        if ($countResultset) {
            return $this->getNumFound($query);
        } else {
            return $this->getContents($query);
        }
    }

    public function getNumFound($query)
    {
        return $this->client->select($query)->getNumFound();
    }

    private function getContents($query)
    {
        $iterator = $this->client->select($query)->getIterator();
        $contents = [];

        while ($iterator->valid()) {
            $content = $iterator->current();
            if (isset($content['type_id']) && $content = $this->dm->getRepository(Content::class)->find($content['type_id'])) {
                $contents[$content->getId()] = $content;
            }
            $iterator->next();
        }

        return $contents;
    }

    protected function addWorkflowFilter(Query $query)
    {
        $filterWorkflow = [];

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            // admin is always allowed to do everything
            return;
        }

        // allow content without workflow
        $fq = $query->createFilterQuery('workflow')
                    ->addTag('workflow')
                    ->addTag('security')
                    ->setQuery('(*:* -security_workflow_read:[* TO *])');

        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        foreach ($user->getGroups() as $group) {
            $filterWorkflow[] = $group->getId();
        }

        // allow content with group access
        if ($filterWorkflow) {
            $fq->setQuery($fq->getQuery().' OR (security_workflow_read: ((%1%)) AND security_workflow_write: ((%1%)))', [implode(') OR (', $filterWorkflow)]);
        }

        // always allow access to assinged content
        $fq->setQuery($fq->getQuery().' OR facet_workflow_assigned_id: %1%', [$user->getId()]);

        if ($user instanceof User) {
            if ($person = $user->getRelation()) {
                $fq->setQuery($fq->getQuery().' OR author: %1%*', [$person->getId()]);
            }
        }
    }

    // If there is ONE contenttype selected, we only want to show files with this contenttype
    // Else ,if available_contenttypes is filled, we only want to show those file.
    // Else, we are showing all the contenttypes
    // $contentType is what the user has in its selection,
    // $available_contenttypes is what the user can choose from
    private function setContentTypes(?array $contentType, FilterQuery $contentTypesQuery, \Closure $filter, Request $request): void
    {
        if (\is_array($contentType) && \count($contentType) === 1) {
            $contentTypesQuery->setQuery('type_name: ((%1%))', [implode(') OR (', array_map($filter, $contentType))]);
        } else {
            $availableContenttypes = $request->query->all('available_contenttypes');
            if (\is_array($availableContenttypes) && \count($availableContenttypes)) {
                $contentTypesQuery->setQuery('type_name: ((%1%))', [implode(') OR (', array_map($filter, $availableContenttypes))]);
            } elseif (\is_array($contentType) && \count($contentType)) {
                $contentTypesQuery->setQuery('type_name: ((%1%))', [implode(') OR (', array_map($filter, $contentType))]);
            }
        }
    }
}
