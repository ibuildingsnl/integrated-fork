<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:taxonomy_category_picker', template: '@IntegratedContent/components/admin/taxonomy_category_picker.html.twig')]
final class TaxonomyCategoryPicker
{
    public string $rootId = '';

    public ?string $rootClass = null;

    public string $relationTitle = '';

    /** @var iterable<mixed> */
    public iterable $categories = [];

    public string $checkboxIdPrefix = '';

    public bool $allTabActive = false;

    public ?string $hiddenContentHtml = null;

    public function rootClasses(): string
    {
        $classes = ['taxonomy_category'];

        if ($this->rootClass) {
            $classes[] = trim($this->rootClass);
        }

        return implode(' ', array_filter($classes));
    }
}
