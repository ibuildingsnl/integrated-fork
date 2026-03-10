<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:page_title', template: '@IntegratedContent/components/admin/page_title.html.twig')]
final class PageTitle
{
    public string $title = '';

    public ?string $titleHtml = null;

    public ?string $subtitle = null;

    public string $headingTag = 'h1';

    public ?string $extraClass = null;

    public ?string $actionsHtml = null;

    public ?string $contentHtml = null;

    public function wrapperClass(): string
    {
        $classes = ['page-title'];

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
