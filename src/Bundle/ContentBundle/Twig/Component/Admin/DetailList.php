<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:detail_list', template: '@IntegratedContent/components/admin/detail_list.html.twig')]
final class DetailList
{
    /**
     * @var array<int, array<string, mixed>>
     */
    public array $rows = [];

    public ?string $extraClass = null;

    public function classes(): string
    {
        $classes = ['dl-horizontal'];

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
