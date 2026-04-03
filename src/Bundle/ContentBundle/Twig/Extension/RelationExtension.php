<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Twig\Extension;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Relation\Relation;
use Integrated\Common\Content\Relation\RelationInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class RelationExtension extends AbstractExtension
{
    private DocumentManager $manager;

    public function __construct(DocumentManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('integrated_relation', $this->getRelation(...)),
        ];
    }

    public function getRelation(string $id): ?RelationInterface
    {
        return $this->manager->getRepository(Relation::class)->find($id);
    }
}
