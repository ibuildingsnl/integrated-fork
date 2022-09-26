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

    public function simulateTreeStructure() {
        return [
            "1" => [
                "name" => "Alea Publishers NIEUW",
                "title" => "Alea Publishers NIEUW",
                "children" => [
                    "1" => [
                        "name" => "Alea Publishers 1",
                        "title" => "Alea Publishers 1",
                        "children" => []
                    ],
                    "2" => [
                        "name" => "Alea Publishers 2",
                        "title" => "Alea Publishers 2",
                        "children" => [
                            "1" => [
                                "name" => "Alea Publishers 2 - 2",
                                "title" => "Alea Publishers 2 - 2",
                            ],
                            "2" => [
                                "name" => "Alea Publishers 2 - 2",
                                "title" => "Alea Publishers 2 - 2",
                            ],
                        ]
                    ],
                    "3" => [
                        "name" => "Alea Publishers 3",
                        "title" => "Alea Publishers 3",
                        "children" => []
                    ],
                ]
            ],
            "2" => [
                "name" => "Bakkers in Bedrijf NIEUW",
                "title" => "Bakkers in Bedrijf NIEUW",
                "children" => []
            ],
            "3" => [
                "name" => "Hardcoded channel NIEUW",
                "title" => "Hardcoded channel NIEUW",
                "children" => [
                    "1" => [
                        "name" => "Hardcoded channel 1",
                        "title" => "Hardcoded channel 1",
                        "children" => []
                    ],
                    "2" => [
                        "name" => "Hardcoded channel 2",
                        "title" => "Hardcoded channel 2",
                        "children" => []
                    ],
                ]
            ],
        ];
    }

    public function getSimulation() {
        return $this->simulateTreeStructure();
    }

    public function get() {
//        return $this->simulateTreeStructure();
        return array_merge($this->getChannels(), $this->getTaxonomyItems());
    }
}
