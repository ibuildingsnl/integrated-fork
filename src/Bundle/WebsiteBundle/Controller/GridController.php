<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\PageBundle\Grid\GridFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GridController extends AbstractController
{
    private DocumentManager $documentManager;
    private GridFactory $gridFactory;

    public function __construct(DocumentManager $documentManager, GridFactory $gridFactory)
    {
        $this->documentManager = $documentManager;
        $this->gridFactory = $gridFactory;
    }

    public function save(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return new JsonResponse([
            'success' => false,
            'error' => 'Legacy grid save disabled after pagebuilder v2 cutover',
        ], 410);
    }
}
