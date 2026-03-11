<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Twig\Component\Admin;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('integrated_admin:status_badge', template: '@IntegratedContent/components/admin/status_badge.html.twig')]
final class StatusBadge
{
    public string $label = '';

    public string $variant = 'inactive';

    public ?string $icon = null;

    public ?string $title = null;

    public ?string $extraClass = null;

    public function statusClass(): string
    {
        return match ($this->variant) {
            'sent' => 'status-sent',
            'active' => 'status-active',
            'info' => 'status-info',
            'sending' => 'status-sending',
            'retrying' => 'status-retrying',
            'failed' => 'status-failed',
            'dead-letter' => 'status-dead-letter',
            'queued' => 'status-queued',
            'inactive' => 'status-inactive',
            default => 'status-inactive',
        };
    }
}
