<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:data_table', template: '@IntegratedContent/components/admin/data_table.html.twig')]
final class DataTable
{
    public ?string $wrapperClass = null;

    public ?string $headHtml = null;

    public ?string $bodyHtml = null;

    public ?string $emptyMessage = null;

    public int $colSpan = 1;

    public bool $hover = true;

    public ?string $extraClass = null;

    public function tableClass(): string
    {
        $classes = ['table'];

        if ($this->hover) {
            $classes[] = 'table-hover';
        }

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }

    public function hasWrapper(): bool
    {
        return null !== $this->wrapperClass && '' !== trim($this->wrapperClass);
    }
}
