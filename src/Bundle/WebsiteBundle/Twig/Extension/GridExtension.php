<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WebsiteBundle\Twig\Extension;

use Integrated\Bundle\PageBundle\Document\Page\AbstractPage;
use Integrated\Bundle\PageBundle\Document\Page\Grid\Grid;
use Integrated\Bundle\ThemeBundle\Templating\ThemeManager;
use Integrated\Bundle\WebsiteBundle\PageBuilder\V2\Rendering\PageBuilderRenderer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class GridExtension extends AbstractExtension
{
    /**
     * @var OptionsResolver
     */
    protected $resolver;

    /**
     * @var RequestStack|null
     */
    protected $request;

    private PageBuilderRenderer $pageBuilderRenderer;

    public function __construct(RequestStack $requestStack, ThemeManager $themeManager, PageBuilderRenderer $pageBuilderRenderer)
    {
        $this->request = $requestStack->getMainRequest();
        $this->pageBuilderRenderer = $pageBuilderRenderer;

        $this->resolver = new OptionsResolver();
        $this->resolver->setDefaults([
            'template' => null,
        ]);
        $this->resolver->setAllowedTypes('template', ['null', 'string']);
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'integrated_grid',
                $this->renderGrid(...),
                ['is_safe' => ['html'], 'needs_environment' => true, 'needs_context' => true]
            ),
        ];
    }

    /**
     * @param array  $context
     * @param string $id
     *
     * @return string
     */
    public function renderGrid(Environment $environment, $context, $id, array $options = [])
    {
        $options = $this->resolver->resolve($options);
        $gridId = (string) $id;

        $page = isset($context['page']) ? $context['page'] : null;

        if ($page instanceof AbstractPage) {
            if ($page->getLayoutVersion() === 2) {
                if ($this->hasRenderableV2Payload($page->getLayoutPayload(), $gridId)) {
                    $rendered = $this->pageBuilderRenderer->render($environment, $page, $gridId);
                    if (trim($rendered) !== '') {
                        return $rendered;
                    }
                }
            }

            $grid = $page->getGrid($gridId);

            if (!$grid instanceof Grid) {
                $grid = new Grid($gridId);
            }

            return $environment->render($template, [
                'grid' => $grid,
            ]);
        }

        return '';
    }

    public function getName()
    {
        return 'integrated_website_grid';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function hasRenderableV2Payload(array $payload, string $gridId): bool
    {
        $root = $payload['root'] ?? null;
        if (!\is_array($root)) {
            return false;
        }

        $gridNode = $this->findNodeByGridId($root, $gridId);
        if (\is_array($gridNode)) {
            return $this->hasBlockReference($gridNode);
        }

        return $this->hasBlockReference($root);
    }

    /**
     * @param array<string, mixed> $node
     *
     * @return array<string, mixed>|null
     */
    private function findNodeByGridId(array $node, string $gridId): ?array
    {
        if (trim((string) ($node['props']['id'] ?? '')) === $gridId) {
            return $node;
        }

        foreach ((array) ($node['children'] ?? []) as $child) {
            if (!\is_array($child)) {
                continue;
            }

            $candidate = $this->findNodeByGridId($child, $gridId);
            if (\is_array($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function hasBlockReference(array $node): bool
    {
        if (($node['type'] ?? null) === 'block_ref' && trim((string) ($node['props']['blockId'] ?? '')) !== '') {
            return true;
        }

        foreach ((array) ($node['children'] ?? []) as $child) {
            if (\is_array($child) && $this->hasBlockReference($child)) {
                return true;
            }
        }

        return false;
    }
}
