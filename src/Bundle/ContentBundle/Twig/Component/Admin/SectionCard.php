<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:section_card', template: '@IntegratedContent/components/admin/section_card.html.twig')]
final class SectionCard
{
    public string $tag = 'section';

    public bool $padding = false;

    public ?string $title = null;

    public ?string $subtitle = null;

    public ?string $extraClass = null;

    public function surfaceClass(): string
    {
        $classes = ['section-white', 'section-radius'];

        if ($this->padding) {
            $classes[] = 'p-4';
        }

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
