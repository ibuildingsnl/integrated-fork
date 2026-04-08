<?php

namespace Integrated\Bundle\BrandBundle\Controller;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Document\Block\BlockRepository;
use Integrated\Bundle\BrandBundle\Document\Brand;
use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\BrandBundle\EventListener\ConnectorDeletionRedirectListener;
use Integrated\Bundle\BrandBundle\Provider\ConnectorMissingThemeBlocksProvider;
use Integrated\Bundle\ChannelBundle\Event\FilterResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\FormConfigEvent;
use Integrated\Bundle\ChannelBundle\Event\GetResponseConfigEvent;
use Integrated\Bundle\ChannelBundle\Form\Type\ActionsType;
use Integrated\Bundle\ChannelBundle\Form\Type\ConfigFormType;
use Integrated\Bundle\ChannelBundle\IntegratedChannelEvents;
use Integrated\Bundle\ChannelBundle\Model\Config;
use Integrated\Common\Channel\Connector\Adapter\RegistryInterface;
use Integrated\Common\Channel\Connector\Config\ConfigManagerInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use MongoDB\BSON\Regex;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConnectorController extends AbstractController
{
    public function __construct(
        private readonly ConfigManagerInterface $configs,
        private readonly RegistryInterface $adapters,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly ConnectorMissingThemeBlocksProvider $missingThemeBlocksProvider,
        private readonly BlockRepository $blockRepository,
    ) {
    }

    public function configure(Request $request, Brand $brand, ChannelLink $link): Response
    {
        if (!$this->isGranted('ROLE_CHANNEL_MANAGER') && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$brand->hasChannelLink($link)) {
            throw $this->createNotFoundException('Channel link not found for this brand.');
        }

        $config = null;
        $new = true;
        foreach ($this->configs->findByChannel($link->channel) as $existingConfig) {
            if ($existingConfig instanceof Config && $existingConfig->getAdapter() === $link->type->connector) {
                $config = $existingConfig;
                $new = false;
                break;
            }
        }
        try {
            $adapter = $this->adapters->getAdapter($link->type->connector);
        } catch (\Throwable $exception) {
            $message = "It seems there is no {$link->type->connector} connector available. Please contact an administrator.";
            $this->addFlash('error', $message);
            throw $this->createNotFoundException($message, $exception);
        }
        if ($new || !$config instanceof Config) {
            $config = new Config();
            $config->setAdapter($adapter->getManifest()->getName());
            $config->setChannels([$link->channel]);
            $config->setName("{$brand->getName()} {$link->getName()} connector");
        } else {
            $request->getSession()->set(
                \sprintf(
                    ConnectorDeletionRedirectListener::SESSION_PATH,
                    $config->getAdapter(),
                    $config->getId()
                ),
                $this->generateUrl('integrated_content_brand_edit', ['id' => $brand->getId()])
            );
        }

        $event = new GetResponseConfigEvent($config, $request);
        $requestResponse = $this->dispatcher->dispatch($event, IntegratedChannelEvents::CONFIG_CREATE_REQUEST)?->getResponse();
        if ($requestResponse instanceof Response) {
            return $requestResponse;
        }

        $form = $this->createForm(ConfigFormType::class, $config, [
            'adapter' => $adapter,
            'method' => 'PUT',
        ]);
        $form->add('actions', ActionsType::class, ['buttons' => [
            $new ? 'create' : 'save',
            'cancel',
        ]]);
        $form->remove('channels');

        $form->handleRequest($request);

        if ($form->get('actions')->getData() == 'cancel') {
            return $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->configs->persist($config);

            if ($new) {
                $submitEvent = IntegratedChannelEvents::CONFIG_CREATE_SUBMITTED;
                $respondEvent = IntegratedChannelEvents::CONFIG_CREATE_RESPONSE;
                $message = 'The config %s is saved';
            } else {
                $submitEvent = IntegratedChannelEvents::CONFIG_EDIT_SUBMITTED;
                $respondEvent = IntegratedChannelEvents::CONFIG_EDIT_RESPONSE;
                $message = 'The changes to the config %s are saved';
            }

            $response = $this->dispatcher
                ->dispatch(new FormConfigEvent($config, $request, $form), $submitEvent)
                ->getResponse();

            if (!$response) {
                $this->addFlash('success', \sprintf($message, $config->getName()));
                $response = $this->redirectToRoute('integrated_content_brand_edit', ['id' => $brand->getId()]);
            }
            $response = $this->dispatcher
                ->dispatch(new FilterResponseConfigEvent($config, $request, $response), $respondEvent)
                ->getResponse();

            $this->configs->persist($config);

            $request->getSession()->set(
                'postReturnUri',
                $this->generateUrl('integrated_content_brand_manage_connector', [
                    'id' => $brand->getId(),
                    'link' => $link->getId(),
                ])
            );

            return $response;
        }

        $targetChannelId = $link->channel instanceof ChannelInterface
            ? trim((string) $link->channel->getId())
            : '';
        $missingConnectorBlocks = $this->missingThemeBlocksProvider->getMissingBlocksForChannel($link->channel);
        $missingConnectorBlocks = $this->enrichMissingBlocksWithDuplicateCandidates($missingConnectorBlocks, $targetChannelId);

        return $this->render('@IntegratedBrand/brand/config_manage.html.twig', [
            'link' => $link,
            'brand' => $brand,
            'new' => $new,
            'adapter' => $adapter,
            'config' => $config,
            'form' => $form,
            'missingConnectorBlocks' => $missingConnectorBlocks,
            'missingBlocksTargetChannelId' => $targetChannelId,
        ]);
    }

    /**
     * @param array<int, array{id: string, usages: array<int, array<string, string>>}> $missingBlocks
     *
     * @return array<int, array{
     *     id: string,
     *     usages: array<int, array<string, string>>,
     *     duplicateCandidates: array<int, array{id: string, title: string}>
     * }>
     */
    private function enrichMissingBlocksWithDuplicateCandidates(array $missingBlocks, string $targetChannelId): array
    {
        if ($targetChannelId === '' || $missingBlocks === []) {
            return $missingBlocks;
        }

        foreach ($missingBlocks as $index => $missingBlock) {
            $missingBlockId = trim((string) ($missingBlock['id'] ?? ''));
            $missingBlocks[$index]['duplicateCandidates'] = $this->findDuplicateCandidatesForMissingBlock(
                $missingBlockId,
                $targetChannelId
            );
        }

        return $missingBlocks;
    }

    /**
     * @return array<int, array{id: string, title: string}>
     */
    private function findDuplicateCandidatesForMissingBlock(string $missingBlockId, string $targetChannelId): array
    {
        $missingBlockId = trim($missingBlockId);
        $targetChannelId = trim($targetChannelId);

        if ($missingBlockId === '' || $targetChannelId === '') {
            return [];
        }

        $pattern = $this->extractChannelPattern($missingBlockId, $targetChannelId);
        if (!\is_array($pattern)) {
            return [];
        }

        $prefix = $pattern['prefix'];
        $suffix = $pattern['suffix'];

        $queryPattern = $this->buildCandidateQueryPattern($prefix, $suffix);
        if ($queryPattern === null) {
            return [];
        }

        $result = $this->blockRepository
            ->createQueryBuilder()
            ->field('id')->equals(new Regex($queryPattern))
            ->sort('id', 'asc')
            ->getQuery()
            ->execute();

        $candidates = [];

        foreach ($result as $candidateBlock) {
            if (!$candidateBlock instanceof Block) {
                continue;
            }

            $candidateId = trim((string) $candidateBlock->getId());
            if ($candidateId === '' || $candidateId === $missingBlockId) {
                continue;
            }

            if ($prefix !== '' && !str_starts_with($candidateId, $prefix)) {
                continue;
            }

            if ($suffix !== '' && !str_ends_with($candidateId, $suffix)) {
                continue;
            }

            $middleLength = \strlen($candidateId) - \strlen($prefix) - \strlen($suffix);
            if ($middleLength <= 0) {
                continue;
            }

            $middlePart = substr($candidateId, \strlen($prefix), $middleLength);
            if (!\is_string($middlePart) || trim($middlePart, '_') === '' || $middlePart === $targetChannelId) {
                continue;
            }

            $candidateTitle = trim((string) $candidateBlock->getTitle());
            $candidates[$candidateId] = [
                'id' => $candidateId,
                'title' => $candidateTitle !== '' ? $candidateTitle : $candidateId,
            ];

            if (\count($candidates) >= 5) {
                break;
            }
        }

        return array_values($candidates);
    }

    /**
     * @return array{prefix: string, suffix: string}|null
     */
    private function extractChannelPattern(string $blockId, string $channelId): ?array
    {
        if ($blockId === '' || $channelId === '') {
            return null;
        }

        $matches = [];
        $matched = preg_match(
            '/(^|_)'.preg_quote($channelId, '/').'(?=_|$)/',
            $blockId,
            $matches,
            \PREG_OFFSET_CAPTURE
        );

        if ($matched !== 1) {
            return null;
        }

        $fullMatch = (string) ($matches[0][0] ?? '');
        $matchOffset = (int) ($matches[0][1] ?? -1);
        $leftBoundary = (string) ($matches[1][0] ?? '');

        if ($fullMatch === '' || $matchOffset < 0) {
            return null;
        }

        $channelStart = $matchOffset + \strlen($leftBoundary);
        $channelEnd = $channelStart + \strlen($channelId);

        return [
            'prefix' => substr($blockId, 0, $channelStart),
            'suffix' => substr($blockId, $channelEnd),
        ];
    }

    private function buildCandidateQueryPattern(string $prefix, string $suffix): ?string
    {
        if ($prefix === '' && $suffix === '') {
            return null;
        }

        if ($prefix !== '' && $suffix !== '') {
            return '^'.preg_quote($prefix, '/').'(.+)'.preg_quote($suffix, '/').'$';
        }

        if ($prefix !== '') {
            return '^'.preg_quote($prefix, '/');
        }

        return preg_quote($suffix, '/').'$';
    }
}
