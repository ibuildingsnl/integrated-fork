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

use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Bulk\BulkAction;
use Integrated\Bundle\ContentBundle\Provider\ContentProvider;
use Integrated\Common\Bulk\BulkHandlerInterface;
use Integrated\Common\Content\RankableInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param DocumentManager $dm
     * @param ContentProvider $contentProvider
     */
    public function __construct(
        DocumentManager $dm,
        ContentProvider $contentProvider,
        TranslatorInterface $translator
    ) {
        $this->dm = $dm;
        $this->contentProvider = $contentProvider;
        $this->translator = $translator;
    }

    /**
     * @param Request    $request
     * @param BulkAction $bulk
     *
     * @return RedirectResponse|Response
     */
    public function lookup(Request $request)
    {
        $limit = 1000;

//        $request->query->set('sort', 'rank');
        $request->query->set('hasFields', ['rank']);
        $request->query->set('contenttypes', 'Media_Taxonomy');
        $content = $this->contentProvider->getContentFromSolr($request, $limit);

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
