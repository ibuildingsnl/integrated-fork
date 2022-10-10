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
use Integrated\Bundle\IntegratedBundle\Controller\AbstractController;

/**
 * Class MediaGalleryMenu.
 *
 * @author Wouter Koppers <wouter@twindigital.com>
 */
class MediaGalleryMenu
{
    public function getChannels() {
        return ["Alea Publischers", "Bakkers in Bedrijf", "Hardcoded channel"];
    }

    public function getTaxonomyItems() {
        return ["Custom TaxItem 1", "Custom TaxItem 2", "Custom TaxItem 3"];
    }

    public function getSimulation() {
        return $this->simulateTreeStructure();
    }

    public function get() {
//        return $this->simulateTreeStructure();
        return array_merge($this->getChannels(), $this->getTaxonomyItems());
    }
}
