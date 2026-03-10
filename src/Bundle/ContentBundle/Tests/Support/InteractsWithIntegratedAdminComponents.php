<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Support;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\OptionsToolbar;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PageTitle;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\StatusBadge;
use Twig\Environment;
use Twig\TwigFunction;

trait InteractsWithIntegratedAdminComponents
{
    protected function registerIntegratedAdminComponentFunction(Environment $twig): void
    {
        $twig->addFunction(new TwigFunction('component', fn (string $name, array $data = []): string => $this->renderIntegratedAdminComponent($name, $data), ['is_safe' => ['html']]));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminComponent(string $name, array $data = []): string
    {
        return match ($name) {
            'integrated_admin:status_badge' => $this->renderIntegratedAdminStatusBadge($data),
            'integrated_admin:options_toolbar' => $this->renderIntegratedAdminOptionsToolbar($data),
            'integrated_admin:page_title' => $this->renderIntegratedAdminPageTitle($data),
            default => throw new \InvalidArgumentException(sprintf('Unsupported component "%s".', $name)),
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminStatusBadge(array $data): string
    {
        $component = new StatusBadge();
        $component->label = (string) ($data['label'] ?? '');
        $component->variant = (string) ($data['variant'] ?? $component->variant);
        $component->title = isset($data['title']) ? (string) $data['title'] : null;

        $attributes = [];
        if ($component->title) {
            $attributes[] = sprintf(' title="%s"', htmlspecialchars($component->title, ENT_QUOTES));
        }

        return sprintf(
            '<span class="%s status-badge"%s>%s</span>',
            $component->statusClass(),
            implode('', $attributes),
            htmlspecialchars($component->label, ENT_QUOTES)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminOptionsToolbar(array $data): string
    {
        $component = new OptionsToolbar();
        $component->contentHtml = isset($data['contentHtml']) ? (string) $data['contentHtml'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        return sprintf(
            '<div class="%s">%s</div>',
            htmlspecialchars($component->wrapperClass(), ENT_QUOTES),
            $component->contentHtml ?? ''
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminPageTitle(array $data): string
    {
        $component = new PageTitle();
        $component->title = (string) ($data['title'] ?? '');
        $component->subtitle = isset($data['subtitle']) ? (string) $data['subtitle'] : null;
        $component->actionsHtml = isset($data['actionsHtml']) ? (string) $data['actionsHtml'] : null;
        $component->contentHtml = isset($data['contentHtml']) ? (string) $data['contentHtml'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        $subtitle = $component->subtitle ? sprintf('<p class="mb-1 text-light">%s</p>', htmlspecialchars($component->subtitle, ENT_QUOTES)) : '';
        $actions = $component->actionsHtml ? sprintf('<div class="page-title-actions">%s</div>', $component->actionsHtml) : '';
        $content = $component->contentHtml ?? '';

        return sprintf(
            '<div class="%s"><div class="heading flex justify-between w-full"><div><h1 class="heading">%s</h1>%s</div>%s</div>%s</div>',
            htmlspecialchars($component->wrapperClass(), ENT_QUOTES),
            htmlspecialchars($component->title, ENT_QUOTES),
            $subtitle,
            $actions,
            $content
        );
    }
}
