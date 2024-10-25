<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Form\Type\BlockEditType;
use Integrated\Bundle\BlockBundle\Form\Type\BlockFilterType;
use Integrated\Bundle\BlockBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Block\BlockInterface;
use Integrated\Common\Content\Form\Event\BlockEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends AbstractController
{
    private MetadataFactoryInterface $metadataFactory;
    private DocumentManager $documentManager;
    private PaginatorInterface $paginator;
    private FilterQueryProvider $provider;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        DocumentManager $documentManager,
        PaginatorInterface $paginator,
        FilterQueryProvider $provider,
        EventDispatcherInterface $dispatcher,
    ) {
        $this->metadataFactory = $metadataFactory;
        $this->documentManager = $documentManager;
        $this->paginator = $paginator;
        $this->provider = $provider;
        $this->dispatcher = $dispatcher;
    }

    public function index(Request $request): Response
    {
        $user = null;
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
        }

        $data = $request->get('integrated_block_filter');

        $facetFilter = $this->createForm(BlockFilterType::class, null, [
            'blockIds' => $this->provider->getBlockIds($data, $user),
        ]);
        $facetFilter->handleRequest($request);

        $pagination = $this->paginator->paginate(
            $this->provider->getBlocksByChannelQueryBuilder($data, $user),
            $request->query->get('page', 1),
            $request->query->get('limit', 20),
            ['defaultSortFieldName' => 'title', 'defaultSortDirection' => 'asc', 'query_type' => 'block_overview']
        );

        return $this->render(sprintf('@IntegratedBlock/block/index.%s.twig', $request->getRequestFormat()), [
            'blocks' => $pagination,
            'factory' => $this->metadataFactory,
            'facetFilter' => $facetFilter,
        ]);
    }

    public function show(Request $request, Block $block): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $request->attributes->set('integrated_block_edit', true);

        return $this->render('@IntegratedBlock/block/show.json.twig', [
            'block' => $block,
        ]);
    }

    public function new(Request $request): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $class = $request->get('class');

        $block = class_exists($class) ? new $class() : null;

        if (!$block instanceof BlockInterface) {
            throw $this->createNotFoundException(sprintf('Invalid block "%s"', $class));
        }

        $form = $this->createForm(
            BlockEditType::class,
            $block,
            [
                'data_class' => $block::class,
                'type' => $block->getType(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_block_block_index');
            }
            if ($form->isValid()) {
                $this->documentManager->persist($block);
                $this->documentManager->flush();

                if ('iframe.html' === $request->getRequestFormat()) {
                    return $this->render('@IntegratedBlock/block/saved.iframe.html.twig', ['id' => $block->getId()]);
                }

                $this->addFlash('success', 'Block created');

                return $this->redirectToRoute('integrated_block_block_edit', ['id' => $block->getId()]);
            }
        }

        return $this->render(sprintf('@IntegratedBlock/block/new.%s.twig', $request->getRequestFormat()), [
            'form' => $form,
        ]);
    }

    public function edit(Request $request, Block $block): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof User || !$block->allowsGroupAccess($user->getGroups())) {
                throw $this->createAccessDeniedException();
            }
        }

        $form = $this->createForm(
            BlockEditType::class,
            $block,
            [
                'method' => 'POST',
                'data_class' => \get_class($block),
                'type' => $block->getType(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_block_block_index');
            }

            if ($form->isValid()) {
                if ($this->dispatcher->hasListeners(Events::BLOCK_VALIDATE)) {
                    $this->dispatcher->dispatch(new BlockEvent($block), Events::BLOCK_VALIDATE);
                }

                $this->documentManager->flush();

                if ('iframe.html' === $request->getRequestFormat()) {
                    return $this->render('@IntegratedBlock/block/saved.iframe.html.twig', [
                        'id' => $block->getId(),
                    ]);
                }

                $this->addFlash('success', 'Block updated');

                return $this->redirectToRoute('integrated_block_block_edit', ['id' => $block->getId()]);
            }
        }

        $metadata = $this->metadataFactory->getMetadata(\get_class($block));

        return $this->render(sprintf('@IntegratedBlock/block/edit.%s.twig', $request->getRequestFormat()), [
            'block' => $block,
            'form' => $form,
            'blockType' => $metadata->getType(),
        ]);
    }

    public function delete(Request $request, Block $block): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($block->isLocked()) {
            throw $this->createNotFoundException(sprintf('Block "%s" is locked.', $block->getId()));
        }

        /* check if current Block not used on some page */
        if ($this->container->has('integrated_page.form.type.page')) {
            if ($this->documentManager->getRepository(Block::class)->isUsed($block)) {
                throw $this->createNotFoundException(sprintf('Block "%s" is used.', $block->getId()));
            }
        }

        $form = $this->createDeleteForm($block->getId());
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_block_block_index');
            }
            if ($form->isValid()) {
                $this->documentManager->remove($block);
                $this->documentManager->flush();

                $this->addFlash('success', 'Block deleted');

                return $this->redirectToRoute('integrated_block_block_index');
            }
        }

        return $this->render('@IntegratedBlock/block/delete.html.twig', [
            'block' => $block,
            'form' => $form,
        ]);
    }

    private function createDeleteForm($id): FormInterface
    {
        $builder = $this->createFormBuilder();

        $builder->setAction($this->generateUrl('integrated_block_block_delete', ['id' => $id]));
        $builder->setMethod('DELETE');
        $builder->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $builder->getForm();
    }

    /**
     * @return Response
     */
    public function usedBy(Content $content, Request $request)
    {
        $query = $this->documentManager
            ->createQueryBuilder(Block::class)
            ->field('relations.references.$id')
            ->equals($content->getId())
            ->getQuery();

        /** @var \Knp\Component\Pager\Paginator $pagination */
        $pagination = $this->paginator->paginate(
            $query,
            $request->query->get('page', 1),
            $request->query->get('limit', 15)
        );

        return $this->render('@IntegratedBlock/block/used_by.'.$request->getRequestFormat().'.twig', [
            'content' => $content,
            'pagination' => $pagination,
        ]);
    }
}
