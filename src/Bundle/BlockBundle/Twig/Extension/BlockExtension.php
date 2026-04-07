<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Twig\Extension;

use Integrated\Bundle\BlockBundle\Provider\BlockUsageProvider;
use Integrated\Bundle\BlockBundle\Service\RuntimeBlockUsageCollector;
use Integrated\Bundle\BlockBundle\Templating\BlockManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ThemeBundle\Exception\CircularFallbackException;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Common\Block\BlockInterface;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Form\Mapping\MetadataFactoryInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class BlockExtension extends AbstractExtension
{
    /**
     * @var BlockManager
     */
    private $blockManager;

    /**
     * @var ThemeManager
     */
    private $themeManager;

    /**
     * @var BlockUsageProvider
     */
    protected $blockUsageProvider;

    /**
     * @var MetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var array
     */
    protected $pages = [];

    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $environment;

    private RuntimeBlockUsageCollector $runtimeBlockUsageCollector;

    public function __construct(
        BlockManager $blockManager,
        ThemeManager $themeManager,
        BlockUsageProvider $blockUsageProvider,
        MetadataFactoryInterface $metadataFactory,
        ChannelContextInterface $channelContext,
        LoggerInterface $logger,
        string $environment,
        RuntimeBlockUsageCollector $runtimeBlockUsageCollector,
    ) {
        $this->blockManager = $blockManager;
        $this->themeManager = $themeManager;
        $this->blockUsageProvider = $blockUsageProvider;
        $this->metadataFactory = $metadataFactory;
        $this->channelContext = $channelContext;
        $this->logger = $logger;
        $this->environment = $environment;
        $this->runtimeBlockUsageCollector = $runtimeBlockUsageCollector;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'integrated_block',
                $this->renderBlock(...),
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction(
                'integrated_channel_block',
                $this->renderChannelBlock(...),
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
            new TwigFunction('integrated_block_css_class', $this->getBlockCssClass(...)),
            new TwigFunction('integrated_find_channels', $this->findChannels(...)),
            new TwigFunction('integrated_find_container_blocks', $this->findContainerBlocks(...)),
            new TwigFunction('integrated_find_template_usages', $this->findTemplateUsages(...)),
            new TwigFunction('integrated_find_pages', $this->findPages(...)),
            new TwigFunction('integrated_find_block_types', $this->findBlockTypes(...)),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('integrated_block_type', $this->getBlockTypeName(...)),
            new TwigFilter('integrated_sort_blocks', $this->sortByType(...)),
        ];
    }

    /**
     * @param BlockInterface|string $block
     *
     * @return string|null
     *
     * @throws \Exception
     */
    public function renderBlock(Environment $environment, $block, array $options = [])
    {
        if ($block instanceof BlockInterface) {
            $id = $block->getId();
        } else {
            $id = (string) $block;
            $block = $this->blockManager->getBlock($id);
        }

        $this->registerRuntimeUsage($id, $block instanceof BlockInterface ? $block : null);

        try {
            $options['data'] = $options['data'] ?? null;
            // fatal errors are not caught
            $html = $this->blockManager->render($block, $options);

            if (!$html) {
                return $environment->render($this->themeManager->locateTemplate('blocks/empty.html.twig'), [
                    'id' => $id,
                    'block' => $block,
                ]);
            }

            return $html;
        } catch (\Exception $e) {
            if ('prod' !== $this->environment) {
                throw $e;
            }
            $this->logger->error(\sprintf('Block "%s" contains an error', $id));

            return $environment->render($this->themeManager->locateTemplate('blocks/error.html.twig'), [
                'id' => $id,
                'block' => $block,
            ]);
        }
    }

    /**
     * @return string|null
     *
     * @throws CircularFallbackException
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function renderChannelBlock(Environment $environment, string $id, string $name, string $class, array $options = [])
    {
        // postfix with channel
        $id = $id.'_'.$this->channelContext->getChannel()->getId();
        $name = $name.' '.$this->channelContext->getChannel()->getName();

        $this->runtimeBlockUsageCollector->register($id, $name, $class);

        $block = $this->blockManager->getBlock($id);
        if ($block) {
            $this->registerRuntimeUsage($id, $block);

            return $environment->render($this->themeManager->locateTemplate('blocks/channel.html.twig'), [
                'id' => $id,
                'content' => $this->renderBlock($environment, $block, $options),
            ]);
        }

        return $environment->render($this->themeManager->locateTemplate('blocks/create.html.twig'), [
            'id' => $id,
            'name' => $name,
            'class' => $class,
        ]);
    }

    /**
     * @param BlockInterface|string|null $block
     */
    public function getBlockCssClass($block): string
    {
        try {
            if ($block instanceof BlockInterface) {
                return (string) $block->getCssClass();
            }

            if (!\is_string($block) || $block === '') {
                return '';
            }

            $resolved = $this->blockManager->getBlock($block);

            if (!$resolved instanceof BlockInterface) {
                return '';
            }

            return (string) $resolved->getCssClass();
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * @return ChannelInterface[]
     */
    public function findChannels(BlockInterface $block)
    {
        $channels = [];

        /* Get all pages which was associated with current Block document */
        $pages = $this->findPages($block);

        foreach ($pages as $page) {
            if (\array_key_exists('channel', $page)) {
                $channels[$page['channel']['$id']] = $this->blockUsageProvider->getChannel($page['channel']['$id']);
            }
        }

        return $channels;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function findPages(BlockInterface $block)
    {
        $pages = $this->blockUsageProvider->getPagesPerBlock($block->getId());

        return \is_array($pages) ? $pages : [];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function findContainerBlocks(BlockInterface $block)
    {
        $containers = $this->blockUsageProvider->getContainerBlocksPerBlock($block->getId());

        return \is_array($containers) ? $containers : [];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function findTemplateUsages(BlockInterface $block)
    {
        $templateUsages = $this->blockUsageProvider->getTemplateUsagesPerBlock($block->getId());

        if (!\is_array($templateUsages)) {
            return [];
        }

        $normalized = [];
        foreach ($templateUsages as $usageKey => $usage) {
            $normalized[$usageKey] = array_filter(
                $usage,
                static fn (mixed $value): bool => \is_string($value)
            );
        }

        return $normalized;
    }

    /**
     * @return string
     */
    public function getBlockTypeName(BlockInterface $block)
    {
        return $block->getType();
    }

    /**
     * @return array
     */
    public function findBlockTypes()
    {
        $blocks = $this->metadataFactory->getAllMetadata();

        usort($blocks, function ($a, $b) {
            if ($a->getType() === $b->getType()) {
                return 0;
            }

            return ($a->getType() < $b->getType()) ? -1 : 1;
        });

        ksort($blocks);

        return $blocks;
    }

    public function sortByType($array)
    {
        usort($array, function ($a, $b) {
            return strcmp($a->getType(), $b->getType());
        });

        return $array;
    }

    public function getName(): string
    {
        return 'integrated_block_block';
    }

    private function registerRuntimeUsage(string $id, ?BlockInterface $block = null): void
    {
        $this->runtimeBlockUsageCollector->register(
            $id,
            $block?->getTitle(),
            $block?->getType()
        );
    }
}
