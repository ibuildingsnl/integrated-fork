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
use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\BlockBundle\Form\Type\BlockEditType;
use Integrated\Bundle\BlockBundle\Form\Type\BlockFilterType;
use Integrated\Bundle\BlockBundle\Provider\FilterQueryProvider;
use Integrated\Bundle\BlockBundle\Security\AllowedBlockClassInstantiator;
use Integrated\Bundle\BlockBundle\Security\InvalidBlockClassException;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\IntegratedBundle\Controller\PaginationQueryTrait;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Content\Form\Event\BlockEvent;
use Integrated\Common\Content\Form\Events;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Integrated\Common\Security\Permissions;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends AbstractController
{
    use PaginationQueryTrait;

    public function __construct(
        private MetadataFactoryInterface $metadataFactory,
        private DocumentManager $documentManager,
        private PaginatorInterface $paginator,
        private FilterQueryProvider $provider,
        private EventDispatcherInterface $dispatcher,
        private BlockRepository $blockRepository,
        private AllowedBlockClassInstantiator $allowedBlockClassInstantiator,
    ) {
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
            $this->getPositiveIntQueryParameter($request, 'page', 1),
            $this->getPositiveIntQueryParameter($request, 'limit', 20),
            ['defaultSortFieldName' => 'title', 'defaultSortDirection' => 'asc', 'query_type' => 'block_overview']
        );

        return $this->render(\sprintf('@IntegratedBlock/block/index.%s.twig', $request->getRequestFormat()), [
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

        try {
            $block = $this->allowedBlockClassInstantiator->instantiate($class);
        } catch (InvalidBlockClassException $exception) {
            throw $this->createNotFoundException($exception->getMessage(), $exception);
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
                if ('iframe.html' === $request->getRequestFormat()) {
                    return $this->render('@IntegratedBlock/block/canceled.iframe.html.twig');
                }

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

        return $this->render(\sprintf('@IntegratedBlock/block/new.%s.twig', $request->getRequestFormat()), [
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
                'method' => 'PUT',
                'data_class' => $block::class,
                'type' => $block->getType(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                if ('iframe.html' === $request->getRequestFormat()) {
                    return $this->render('@IntegratedBlock/block/canceled.iframe.html.twig');
                }

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

        $metadata = $this->metadataFactory->getMetadata($block::class);

        return $this->render(\sprintf('@IntegratedBlock/block/edit.%s.twig', $request->getRequestFormat()), [
            'block' => $block,
            'form' => $form,
            'blockType' => $block->getType(),
        ]);
    }

    public function delete(Request $request, Block $block): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($block->isLocked()) {
            throw $this->createNotFoundException(\sprintf('Block "%s" is locked.', $block->getId()));
        }

        /* check if current Block not used on some page */
        if ($this->container->has('integrated_page.form.type.page')) {
            if ($this->blockRepository->isUsed($block)) {
                throw $this->createNotFoundException(\sprintf('Block "%s" is used.', $block->getId()));
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

    public function duplicate(Request $request, Block $sourceBlock): Response
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();
            if (!$user instanceof User || !$sourceBlock->allowsGroupAccess($user->getGroups())) {
                throw $this->createAccessDeniedException();
            }
        }

        $targetChannelId = trim((string) (
            $request->query->get('target_channel')
            ?? $request->request->get('target_channel')
            ?? $request->query->get('channel_id')
            ?? ''
        ));
        $targetId = trim((string) (
            $request->query->get('target_id')
            ?? $request->request->get('target_id')
            ?? ''
        ));
        $availableChannels = $this->getWebsiteChannelChoices();
        $sourceChannelId = $this->detectSourceChannelId((string) $sourceBlock->getId(), array_keys($availableChannels));
        if ($targetChannelId !== '' && !isset($availableChannels[$targetChannelId])) {
            $targetChannelId = '';
        }

        $block = $this->createDuplicateBlock($sourceBlock, $targetChannelId);
        if ($targetId !== '') {
            $block->setId($targetId);
        }

        $form = $this->createForm(
            BlockEditType::class,
            $block,
            [
                'method' => 'POST',
                'data_class' => $block::class,
                'type' => $block->getType(),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->get('actions')->getData() == 'cancel') {
                return $this->redirectToRoute('integrated_block_block_index');
            }

            $postedTargetChannelId = trim((string) ($request->request->get('target_channel') ?? ''));
            if ($postedTargetChannelId !== '' && isset($availableChannels[$postedTargetChannelId])) {
                $targetChannelId = $postedTargetChannelId;
            }

            $this->addDuplicateIdValidationError($form, $block);

            if ($form->isValid()) {
                if ($this->dispatcher->hasListeners(Events::BLOCK_VALIDATE)) {
                    $this->dispatcher->dispatch(new BlockEvent($block), Events::BLOCK_VALIDATE);
                }

                $this->documentManager->persist($block);
                $this->documentManager->flush();

                $this->addFlash('success', 'Block duplicated');

                return $this->redirectToRoute('integrated_block_block_edit', ['id' => $block->getId()]);
            }
        }

        return $this->render('@IntegratedBlock/block/duplicate.html.twig', [
            'sourceBlock' => $sourceBlock,
            'form' => $form,
            'sourceChannelId' => $sourceChannelId,
            'selectedTargetChannelId' => $targetChannelId,
            'availableChannels' => $availableChannels,
            'idAvailabilityUrl' => $this->generateUrl('integrated_block_block_id_availability'),
        ]);
    }

    private function createDeleteForm($id): FormInterface
    {
        $builder = $this->createFormBuilder();

        $builder->setAction($this->generateUrl('integrated_block_block_delete', ['id' => $id]));
        $builder->setMethod(Request::METHOD_DELETE);
        $builder->add('actions', ActionsType::class, ['buttons' => ['delete', 'cancel']]);

        return $builder->getForm();
    }

    /**
     * @return Response
     */
    public function usedBy(Content $content, Request $request)
    {
        if (
            !$this->isGranted('ROLE_WEBSITE_MANAGER')
            && !$this->isGranted('ROLE_ADMIN')
            && !$this->isGranted(Permissions::EDIT, $content)
        ) {
            throw $this->createAccessDeniedException();
        }

        $query = $this->documentManager
            ->createQueryBuilder(Block::class)
            ->field('relations.references.$id')
            ->equals($content->getId())
            ->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $this->getPositiveIntQueryParameter($request, 'page', 1),
            $this->getPositiveIntQueryParameter($request, 'limit', 15)
        );

        return $this->render('@IntegratedBlock/block/used_by.'.$request->getRequestFormat().'.twig', [
            'content' => $content,
            'pagination' => $pagination,
        ]);
    }

    public function idAvailability(Request $request): JsonResponse
    {
        if (!$this->isGranted('ROLE_WEBSITE_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $id = trim((string) $request->query->get('id', ''));
        if ($id === '') {
            return new JsonResponse([
                'id' => $id,
                'exists' => false,
                'valid' => false,
            ]);
        }

        $existing = $this->documentManager->getRepository(Block::class)->find($id);

        return new JsonResponse([
            'id' => $id,
            'exists' => $existing instanceof Block,
            'valid' => true,
        ]);
    }

    private function createDuplicateBlock(Block $sourceBlock, string $targetChannelId): Block
    {
        $copy = null;
        try {
            $unserialized = unserialize(serialize($sourceBlock), ['allowed_classes' => true]);
            $copy = $unserialized;
        } catch (\Throwable) {
            $copy = null;
        }
        $block = $copy instanceof Block ? $copy : clone $sourceBlock;

        $sourceId = trim((string) $sourceBlock->getId());
        $sourceTitle = trim((string) $sourceBlock->getTitle());

        $block->setId($this->buildDuplicateBlockId($sourceId, $targetChannelId));
        $block->setTitle($this->buildDuplicateBlockTitle($sourceTitle, $sourceId));
        $block->setCreatedAt(new \DateTime());
        $block->setUpdatedAt(new \DateTime());
        $block->setLocked(false);

        return $block;
    }

    private function addDuplicateIdValidationError(FormInterface $form, Block $block): void
    {
        $id = trim((string) $block->getId());

        if ($id === '') {
            $error = new FormError('Block id is required.');
            if ($form->has('id')) {
                $form->get('id')->addError($error);
            } else {
                $form->addError($error);
            }

            return;
        }

        $existing = $this->documentManager->getRepository(Block::class)->find($id);
        if ($existing instanceof Block) {
            $error = new FormError(\sprintf('Block id "%s" already exists.', $id));
            if ($form->has('id')) {
                $form->get('id')->addError($error);
            } else {
                $form->addError($error);
            }
        }
    }

    private function buildDuplicateBlockId(string $sourceId, string $targetChannelId): string
    {
        $sourceId = trim($sourceId);
        $targetChannelId = trim($targetChannelId);

        if ($sourceId === '') {
            return $targetChannelId !== '' ? $targetChannelId.'_copy' : 'block_copy';
        }

        if ($targetChannelId !== '') {
            $detectedSourceChannelId = $this->detectSourceChannelId($sourceId);
            if ($detectedSourceChannelId !== '' && $detectedSourceChannelId !== $targetChannelId) {
                $swappedId = $this->swapChannelInBlockId($sourceId, $detectedSourceChannelId, $targetChannelId);
                if ($swappedId !== '') {
                    return $swappedId;
                }
            }
        }

        return $sourceId.'_copy';
    }

    private function buildDuplicateBlockTitle(string $sourceTitle, string $sourceId): string
    {
        $baseTitle = $sourceTitle !== '' ? $sourceTitle : $sourceId;

        return $baseTitle !== '' ? $baseTitle.' (copy)' : 'Copy';
    }

    /**
     * @param list<string>|null $knownChannelIds
     */
    private function detectSourceChannelId(string $blockId, ?array $knownChannelIds = null): string
    {
        $blockId = trim($blockId);
        if ($blockId === '') {
            return '';
        }

        $channelIds = $knownChannelIds ?? array_keys($this->getWebsiteChannelChoices());
        $channelIds = array_values(array_filter($channelIds, static fn (string $channelId): bool => trim($channelId) !== ''));
        usort($channelIds, static fn (string $left, string $right): int => \strlen($right) <=> \strlen($left));

        foreach ($channelIds as $channelId) {
            if ($channelId === '') {
                continue;
            }

            if (preg_match('/(^|_)'.preg_quote($channelId, '/').'(?=_|$)/', $blockId) === 1) {
                return $channelId;
            }
        }

        $parts = explode('_', $blockId, 2);

        return trim((string) ($parts[0] ?? ''));
    }

    private function swapChannelInBlockId(string $blockId, string $sourceChannelId, string $targetChannelId): string
    {
        $blockId = trim($blockId);
        if ($blockId === '') {
            return $targetChannelId !== '' ? $targetChannelId.'_copy' : 'block_copy';
        }

        if ($sourceChannelId === '' || $targetChannelId === '' || $sourceChannelId === $targetChannelId) {
            return $blockId;
        }

        $swappedId = preg_replace_callback(
            '/(^|_)'.preg_quote($sourceChannelId, '/').'(?=_|$)/',
            static fn (array $match): string => (string) ($match[1] ?? '').$targetChannelId,
            $blockId,
            1
        );
        if (\is_string($swappedId) && $swappedId !== '' && $swappedId !== $blockId) {
            return $swappedId;
        }

        return $targetChannelId.'_'.$blockId;
    }

    /**
     * @return array<string, string>
     */
    private function getWebsiteChannelChoices(): array
    {
        $result = $this->documentManager
            ->createQueryBuilder(Channel::class)
            ->field('type.$id')->equals('website')
            ->sort('name', 'asc')
            ->getQuery()
            ->execute();

        $channels = [];
        foreach ($result as $channel) {
            if (!$channel instanceof Channel) {
                continue;
            }

            $channelId = trim((string) $channel->getId());
            if ($channelId === '') {
                continue;
            }

            $channels[$channelId] = trim((string) $channel->getName()) ?: $channelId;
        }

        return $channels;
    }
}
