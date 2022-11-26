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
use Integrated\Common\Security\PermissionInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

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
    public function __construct(DocumentManager $dm, private AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->dm = $dm;
    }

    public function createMenu(): array
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

    public function getMenuItemsFromDB(): array
    {
        return $this->dm->getRepository(Taxonomy::class)->findBy(['contentType' => 'media_taxonomy']);
    }

    public function isGranted(Taxonomy $menuItem): bool
    {
        return $this->authorizationChecker->isGranted(PermissionInterface::READ, $menuItem);
    }

    public function getMenuItems(): array
    {
        // Get MediaGalleryMenuItems to show in the menu on the left side.
        $menuItems = [];

        if ($mediaGalleryMenuResult = $this->getMenuItemsFromDB()) {
            foreach ($mediaGalleryMenuResult as $menuItem) {
                // Does the user have the right rights?
                if (false === $this->isGranted($menuItem)) {
                    continue;
                }

                $menuItems[] = [
                    'ID' => $menuItem->getId(),
                    'title' => $menuItem->getTitle(),
                    'parent_id' => $menuItem->getParentId(),
                ];
            }
        }

        return $menuItems;
    }

    public function findSelectedMenuTitles(array $menu, string $only_allowed_taxonomy_id): array
    {
        $allSelectedTaxonomys = $this->findCurrentlySelectedMenu($menu, $only_allowed_taxonomy_id);

        // TODO change this to ID!
        return $this->arrayColumnRecursive($allSelectedTaxonomys, 'title');
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

    public function arrayColumnRecursive(array $haystack, $needle)
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
