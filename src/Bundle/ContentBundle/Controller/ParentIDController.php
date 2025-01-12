<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Common\Bulk\BulkHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParentIDController extends AbstractController
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var ContentProvider
     */
    protected $contentProvider;

    /**
     * @var BulkHandlerInterface
     */
    protected $bulkHandler;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        DocumentManager $dm,
        ContentProvider $contentProvider,
        TranslatorInterface $translator,
    ) {
        $this->dm = $dm;
        $this->contentProvider = $contentProvider;
        $this->translator = $translator;
    }

    public function lookup(Request $request): Response
    {
        $request->query->set('contenttypes', [$request->get('contentType')]);
        $content = $this->contentProvider->getContentFromSolr($request, 1000);

        $result = [];
        foreach ($content as $content_item) {
            $result[$content_item->getID()] = $content_item->getTitle();
        }

        return $this->render('@IntegratedContent/parent_id/lookup.json.twig', [
            'result' => $result,
            'relations' => [],
        ]);
    }
}
