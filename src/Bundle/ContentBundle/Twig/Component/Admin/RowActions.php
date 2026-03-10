<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:row_actions', template: '@IntegratedContent/components/admin/row_actions.html.twig')]
final class RowActions
{
    public ?string $contentHtml = null;

    public ?string $extraClass = null;

    public function classes(): string
    {
        $classes = ['row-options'];

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
