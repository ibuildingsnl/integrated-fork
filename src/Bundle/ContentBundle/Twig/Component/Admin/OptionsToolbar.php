<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:options_toolbar', template: '@IntegratedContent/components/admin/options_toolbar.html.twig')]
final class OptionsToolbar
{
    public ?string $contentHtml = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $listItems = [];

    public string $menuClass = 'content-navigator-menu';

    public ?string $wrapperClass = null;

    public ?string $extraClass = null;

    public function classes(): string
    {
        $classes = ['options', 'options-toolbar'];

        if ($this->wrapperClass) {
            $classes[] = trim($this->wrapperClass);
        }

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
