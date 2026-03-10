<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:aside_panel', template: '@IntegratedContent/components/admin/aside_panel.html.twig')]
final class AsidePanel
{
    public string $title = '';

    public ?string $titleHtml = null;

    public ?string $icon = null;

    public bool $expanded = false;

    public ?string $wrapperClass = null;

    public ?string $listClass = null;

    public ?string $containerClass = null;

    public ?string $contentHtml = null;

    public string $indicatorIcon = 'nav-arrow-down';

    public function wrapperClasses(): string
    {
        $classes = ['aside-item-wrapper'];

        if ($this->expanded) {
            $classes[] = 'show';
        }

        if ($this->wrapperClass) {
            $classes[] = trim($this->wrapperClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function listClasses(): string
    {
        $classes = ['aside-item-list'];

        if ($this->listClass) {
            $classes[] = trim($this->listClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function containerClasses(): string
    {
        $classes = ['aside-item-list-container'];

        if ($this->containerClass) {
            $classes[] = trim($this->containerClass);
        }

        return implode(' ', array_filter($classes));
    }
}
