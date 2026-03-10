<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:filter_search_input', template: '@IntegratedContent/components/admin/filter_search_input.html.twig')]
final class FilterSearchInput
{
    public ?string $inputHtml = null;

    public ?string $buttonHtml = null;

    public ?string $holderClass = null;

    public ?string $groupClass = null;

    public function holderClasses(): string
    {
        $classes = ['aside-item-holder'];

        if ($this->holderClass) {
            $classes[] = trim($this->holderClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function groupClasses(): string
    {
        $classes = ['input-group', 'input-group-search'];

        if ($this->groupClass) {
            $classes[] = trim($this->groupClass);
        }

        return implode(' ', array_filter($classes));
    }
}
