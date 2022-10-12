<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Services;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Doctrine\ODM\MongoDB\DocumentManager;

/**
 * Class MediaGalleryMenu.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class MediaGalleryMenu
{
    private $dm;

    /**
     * SearchContentReferenced constructor.
     *
     * @param DocumentManager $dm
     */
    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    // CREATING THE MENU
    public function createMenu()
    {
        $menuItems = $this->getMenuItems();

        $menuResult = [];
        $this->makeParentChildRelations($menuItems, $menuResult);

        return $menuResult;
    }

    public function makeParentChildRelations(&$inArray, &$outArray, $currentParentId = 0)
    {
        if (!\is_array($inArray)) {
            return;
        }

        if (!\is_array($outArray)) {
            return;
        }

        foreach ($inArray as $key => $tuple) {
            if ($tuple['parent_id'] == $currentParentId) {
                $tuple['children'] = [];
                $this->makeParentChildRelations($inArray, $tuple['children'], $tuple['ID']);
                $outArray[] = $tuple;
            }
        }
    }

    public function getMenuItems()
    {
        // Alle MediaGalleryMenuTree items ophalen om de categorieen te tonen aan de linkerkant
        $menuItems = [];

        if ($mediaGalleryMenuResult = $this->dm->getRepository(Taxonomy::class)->findBy(['contentType' => 'media_taxonomy'])) {
            foreach ($mediaGalleryMenuResult as $menuItem) {
                $menuItems[] = [
                    'ID' => $menuItem->getId(),
                    'title' => $menuItem->getTitle(),
                    'parent_id' => $menuItem->getParentId(),
                ];
            }
        }

        return $menuItems;
    }

    // FIND SELECTED MENU TITLES
    public function findSelectedMenuTitles(array $menu, string $only_allowed_taxonomy_id): array
    {
        $allSelectedTaxonomys = $this->findCurrentlySelectedMenu($menu, $only_allowed_taxonomy_id);

        // TODO change this to ID!
        return $this->array_column_recursive($allSelectedTaxonomys, 'title');
    }

    public function findCurrentlySelectedMenu($inArray, $target)
    {
        foreach ($inArray as $key => $tuple) {
            if ($tuple['ID'] === $target) {
                return $tuple;
            }

            if (\count($tuple['children']) > 0) {
                $found = $this->findCurrentlySelectedMenu($tuple['children'], $target);

                if ($found !== null) {
                    return $found;
                }
            }
        }
    }

    public function array_column_recursive(array $haystack, $needle)
    {
        $found = [];
        array_walk_recursive($haystack, function ($value, $key) use (&$found, $needle) {
            if ($key == $needle) {
                $found[] = $value;
            }
        });

        return $found;
    }
}
