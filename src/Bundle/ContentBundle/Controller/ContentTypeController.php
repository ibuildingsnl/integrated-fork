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
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ChannelBundle\Form\Type\DeleteFormType;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Event\ContentDeletedEvent;
use Integrated\Bundle\ContentBundle\Form\Type\ContentTypeFormType;
use Integrated\Common\Content\Form\Events as ContentEvents;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\Event\ContentTypeEvent;
use Integrated\Common\ContentType\Events;
use Integrated\Common\Form\Mapping\MetadataFactory;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentTypeController extends AbstractController
{
    private MetadataFactoryInterface $metadata;
    private ContentTypeManager $contentTypeManager;
    private EventDispatcherInterface $eventDispatcher;
    private DocumentManager $documentManager;

    public function __construct(
        ContentTypeManager $contentTypeManager,
        EventDispatcherInterface $eventDispatcher,
        MetadataFactory $metadataFactory,
        DocumentManager $documentManager,
    ) {
        $this->contentTypeManager = $contentTypeManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->metadata = $metadataFactory;
        $this->documentManager = $documentManager;
    }

    public function index(): Response
    {
        $this->denyAdminAccessUnlessGranted();

        $documents = $this->contentTypeManager->getAll();
        $documentTypes = $this->metadata->getAllMetadata();

        return $this->render('@IntegratedContent/content_type/index.html.twig', [
            'documents' => $documents,
            'documentTypes' => $documentTypes,
        ]);
    }

    public function select(): Response
    {
        $documentTypes = $this->metadata->getAllMetadata();

        return $this->render('@IntegratedContent/content_type/select.html.twig', [
            'documentTypes' => $documentTypes,
        ]);
    }

    public function show(string $id): Response
    {
        $this->denyAdminAccessUnlessGranted();

        $contentType = $this->getContentType($id);
        $form = $this->createDeleteForm($contentType, \count($this->getRelatedContent($contentType)) === 0);

        return $this->render('@IntegratedContent/content_type/show.html.twig', [
            'form' => $form,
            'contentType' => $contentType,
        ]);
    }

    public function new(Request $request): Response
    {
        $this->denyAdminAccessUnlessGranted();

        $class = trim((string) $request->query->get('class', ''));

        if ('' === $class) {
            return $this->redirectToRoute('integrated_content_content_type_select');
        }

        $metadata = $this->metadata->getMetadata($class);

        if (!$metadata) {
            return $this->redirectToRoute('integrated_content_content_type_select');
        }

        $contentType = new ContentType();
        $contentType->setClass($metadata->getClass());

        $form = $this->createNewForm($contentType, $metadata);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_content_content_type_index');
            }

            if ($form->isValid()) {
                $this->documentManager->persist($contentType);
                $this->documentManager->flush();

                $this->addFlash('success', 'Item created');

                $this->eventDispatcher->dispatch(new ContentTypeEvent($contentType), Events::CONTENT_TYPE_CREATED);

                return $this->redirectToRoute('integrated_content_content_type_edit', ['id' => $contentType->getId()]);
            }
        }

        return $this->render('@IntegratedContent/content_type/new.html.twig', [
            'form' => $form,
        ]);
    }

    public function edit(Request $request, string $id): Response
    {
        $this->denyAdminAccessUnlessGranted();

        $contentType = $this->getContentType($id);
        $metadata = $this->getRequiredMetadata($contentType->getClass());

        $form = $this->createEditForm($contentType, $metadata);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_content_content_type_index');
            }

            if ($form->isValid()) {
                if (!$this->documentManager->contains($contentType)) {
                    // Needed for content types from XML files
                    $this->documentManager->persist($contentType);
                }

                $this->documentManager->flush();

                $this->addFlash('success', 'Item updated');

                $this->eventDispatcher->dispatch(new ContentTypeEvent($contentType), Events::CONTENT_TYPE_UPDATED);

                return $this->redirectToRoute('integrated_content_content_type_edit', ['id' => $contentType->getId()]);
            }
        }

        return $this->render('@IntegratedContent/content_type/edit.html.twig', [
            'form' => $form,
            'contentType' => $contentType,
        ]);
    }

    public function delete(Request $request, string $id): Response
    {
        $this->denyAdminAccessUnlessGranted();

        $contentType = $this->getContentType($id);

        if ($contentType->isLocked()) {
            throw new AccessDeniedHttpException(\sprintf('Content type with id "%s" is locked.', $id));
        }

        $relatedContent = $this->getRelatedContent($contentType);
        $form = $this->createDeleteForm($contentType, \count($relatedContent) === 0);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_content_content_type_index');
            }

            if ($form->isValid()) {
                $relatedContent = $this->getRelatedContent($contentType);
                $deleteRelatedContent = $form->has('delete_related_content') && (bool) $form->get('delete_related_content')->getData();

                if (\count($relatedContent) > 0 && !$deleteRelatedContent) {
                    $this->addFlash('warning', 'This content type still contains content. Select "Delete related content" to proceed.');

                    return $this->redirectToRoute('integrated_content_content_type_delete', ['id' => $contentType->getId()]);
                }

                if ($deleteRelatedContent) {
                    $this->removeRelatedContent($relatedContent);
                }

                $this->documentManager->remove($contentType);
                $this->documentManager->flush();

                $this->eventDispatcher->dispatch(new ContentTypeEvent($contentType), Events::CONTENT_TYPE_DELETED);

                // Set flash message
                $this->addFlash('success', 'Item deleted');

                return $this->redirectToRoute('integrated_content_content_type_index');
            }
        }

        return $this->render('@IntegratedContent/content_type/delete.html.twig', [
            'contentType' => $contentType,
            'form' => $form,
            'hasRelatedContent' => \count($relatedContent) > 0,
        ]);
    }

    private function getContentType(string $id): ContentTypeInterface
    {
        try {
            return $this->contentTypeManager->getType($id);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundHttpException(\sprintf('Content type with id "%s" not found.', $id));
        }
    }

    private function denyAdminAccessUnlessGranted(): void
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }
    }

    private function createNewForm(ContentType $type, MetadataInterface $metadata): FormInterface
    {
        $form = $this->createForm(ContentTypeFormType::class, $type, [
            'action' => $this->generateUrl('integrated_content_content_type_new', ['class' => $type->getClass()]),
            'metadata' => $metadata,
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);

        return $form;
    }

    private function createEditForm(ContentType $type, MetadataInterface $metadata): FormInterface
    {
        $form = $this->createForm(ContentTypeFormType::class, $type, [
            'action' => $this->generateUrl('integrated_content_content_type_edit', ['id' => $type->getId()]),
            'metadata' => $metadata,
        ]);

        $form->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);

        return $form;
    }

    private function createDeleteForm(ContentType $type, bool $deleteAllowed): FormInterface
    {
        $form = $this->createForm(DeleteFormType::class, $type, [
            'action' => $this->generateUrl('integrated_content_content_type_delete', ['id' => $type->getId()]),
        ]);

        if (!$deleteAllowed) {
            $form->add('delete_related_content', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Delete related content',
                'label_attr' => ['class' => 'control-label'],
                'attr' => ['class' => 'form-control'],
            ]);
        }

        $form->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $form;
    }

    private function getRequiredMetadata(string $class): MetadataInterface
    {
        $metadata = $this->metadata->getMetadata($class);
        if (!$metadata instanceof MetadataInterface) {
            throw new NotFoundHttpException(\sprintf('No metadata found for class "%s".', $class));
        }

        return $metadata;
    }

    /**
     * @return array<int, mixed>
     */
    private function getRelatedContent(ContentTypeInterface $contentType): array
    {
        return $this->documentManager->getRepository($contentType->getClass())->findBy(
            ['contentType' => $contentType->getId()]
        );
    }

    /**
     * @param array<int, mixed> $relatedContent
     */
    private function removeRelatedContent(array $relatedContent): void
    {
        foreach ($relatedContent as $content) {
            if (!$content instanceof Content) {
                continue;
            }

            if ($this->eventDispatcher->hasListeners(ContentEvents::CONTENT_DELETED)) {
                $this->eventDispatcher->dispatch(
                    new ContentDeletedEvent($content),
                    ContentEvents::CONTENT_DELETED
                );
            }

            $this->documentManager->remove($content);
        }
    }
}
