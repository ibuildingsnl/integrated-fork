<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:workflow_status', template: '@IntegratedContent/components/admin/workflow_status.html.twig')]
final class WorkflowStatus
{
    public string $color = '#6c7b89';

    public ?string $icon = null;

    public ?string $title = null;

    public ?string $wrapperClass = null;

    public ?string $extraClass = null;

    public function classes(): string
    {
        $classes = ['workflow-status'];

        if ($this->wrapperClass) {
            $classes[] = trim($this->wrapperClass);
        }

        if ($this->extraClass) {
            $classes[] = trim($this->extraClass);
        }

        return implode(' ', array_filter($classes));
    }
}
