<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Bulk;

use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\Persistence\ManagerRegistry;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionRelationType;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\Bulk\Form\ConfigProviderInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class RelationFormProvider implements ConfigProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    private $manager;

    /**
     * @var TaxonomyOverview
     */
    private $taxonomyOverview;

    public function __construct(ManagerRegistry $manager, TaxonomyOverview $taxonomyOverview)
    {
        $this->manager = $manager;
        $this->taxonomyOverview = $taxonomyOverview;
    }

    public function getConfig(array $content)
    {
        $types = [];

        foreach ($content as $item) {
            $types[$item->getContentType()] = $item->getContentType();
        }

        /** @var Builder $builder */
        $builder = $this->manager->getRepository(Relation::class)->createQueryBuilder('r');
        $builder->field('sources.$id')->in($types);

        $config = [];

        foreach ($builder->getQuery()->getIterator() as $relation) {
            $taxonomyCategories = $this->getTaxonomyCategories($relation);

            $config[] = new Config(
                RelationAddHandler::class,
                \sprintf('add_%s', $relation->getId()),
                BulkActionRelationType::class,
                [
                    'relation' => $relation,
                    'taxonomy_categories' => $taxonomyCategories,
                    'relation_handler' => RelationAddHandler::class,
                    'label' => \sprintf('Add %s', $relation->getName()),
                ],
                new RelationFormActionMatcher(RelationAddHandler::class, $relation->getId())
            );

            $config[] = new Config(
                RelationRemoveHandler::class,
                \sprintf('remove_%s', $relation->getId()),
                BulkActionRelationType::class,
                [
                    'relation' => $relation,
                    'taxonomy_categories' => $taxonomyCategories,
                    'relation_handler' => RelationRemoveHandler::class,
                    'label' => \sprintf('Remove %s', $relation->getName()),
                ],
                new RelationFormActionMatcher(RelationRemoveHandler::class, $relation->getId())
            );
        }

        return $config;
    }

    /** @return array<mixed> */
    private function getTaxonomyCategories(Relation $relation): array
    {
        if ($relation->getType() !== 'taxonomy_category') {
            return [];
        }

        foreach ($relation->getTargets() as $target) {
            return $this->taxonomyOverview->overviewFor($target->getId());
        }

        return [];
    }
}
