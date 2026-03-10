<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:folder_menu_panel', template: '@IntegratedContent/components/admin/folder_menu_panel.html.twig')]
final class FolderMenuPanel
{
    public string $title = '';

    public string $target = 'aside-filters';

    public ?string $holderClass = null;

    public ?string $wrapperClass = null;

    public ?string $rootLinkHtml = null;

    public ?string $searchHtml = null;

    public ?string $contentHtml = null;

    public function holderClasses(): string
    {
        $classes = ['aside-holder'];

        if ($this->holderClass) {
            $classes[] = trim($this->holderClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function wrapperClasses(): string
    {
        $classes = ['aside-item-wrapper'];

        if ($this->wrapperClass) {
            $classes[] = trim($this->wrapperClass);
        }

        return implode(' ', array_filter($classes));
    }
}
