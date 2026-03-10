<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:filter_group', template: '@IntegratedContent/components/admin/filter_group.html.twig')]
final class FilterGroup
{
    public string $title = '';

    public ?string $titleHtml = null;

    public ?string $wrapperClass = null;

    public ?string $listClass = null;

    public ?string $containerClass = null;

    public ?string $listStyle = null;

    public ?string $contentHtml = null;

    public function wrapperClasses(): string
    {
        $classes = ['aside-item-container'];

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
