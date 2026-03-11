<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Support;

use Integrated\Bundle\ContentBundle\Twig\Component\Admin\AlertBox;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\DataTable;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\EmptyStateMessage;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\OptionsToolbar;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PageTitle;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\PaginationFooter;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\RowActions;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\SectionCard;
use Integrated\Bundle\ContentBundle\Twig\Component\Admin\StatusBadge;
use Twig\Environment;
use Twig\TwigFunction;

trait InteractsWithIntegratedAdminComponents
{
    protected function registerIntegratedAdminComponentFunction(Environment $twig): void
    {
        $twig->addFunction(new TwigFunction('component', fn (string $name, array $data = []): string => $this->renderIntegratedAdminComponent($name, $data), ['is_safe' => ['html']]));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminComponent(string $name, array $data = []): string
    {
        return match ($name) {
            'integrated_admin:alert_box' => $this->renderIntegratedAdminAlertBox($data),
            'integrated_admin:data_table' => $this->renderIntegratedAdminDataTable($data),
            'integrated_admin:empty_state_message' => $this->renderIntegratedAdminEmptyStateMessage($data),
            'integrated_admin:pagination_footer' => $this->renderIntegratedAdminPaginationFooter($data),
            'integrated_admin:row_actions' => $this->renderIntegratedAdminRowActions($data),
            'integrated_admin:section_card' => $this->renderIntegratedAdminSectionCard($data),
            'integrated_admin:status_badge' => $this->renderIntegratedAdminStatusBadge($data),
            'integrated_admin:options_toolbar' => $this->renderIntegratedAdminOptionsToolbar($data),
            'integrated_admin:page_title' => $this->renderIntegratedAdminPageTitle($data),
            default => throw new \InvalidArgumentException(sprintf('Unsupported component "%s".', $name)),
        };
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminEmptyStateMessage(array $data): string
    {
        $component = new EmptyStateMessage();
        $component->tag = isset($data['tag']) ? (string) $data['tag'] : $component->tag;
        $component->variant = isset($data['variant']) ? (string) $data['variant'] : $component->variant;
        $component->message = isset($data['message']) ? (string) $data['message'] : null;
        $component->elementAttributes = isset($data['elementAttributes']) ? (string) $data['elementAttributes'] : null;
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        return sprintf(
            '<%1$s class="%2$s"%4$s>%3$s</%1$s>',
            htmlspecialchars($component->tagName(), ENT_QUOTES),
            htmlspecialchars($component->classes(), ENT_QUOTES),
            htmlspecialchars((string) $component->message, ENT_QUOTES),
            $component->elementAttributes ? ' '.$component->elementAttributes : ''
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminStatusBadge(array $data): string
    {
        $component = new StatusBadge();
        $component->label = (string) ($data['label'] ?? '');
        $component->variant = (string) ($data['variant'] ?? $component->variant);
        $component->title = isset($data['title']) ? (string) $data['title'] : null;

        $attributes = [];
        if ($component->title) {
            $attributes[] = sprintf(' title="%s"', htmlspecialchars($component->title, ENT_QUOTES));
        }

        return sprintf(
            '<span class="%s status-badge"%s>%s</span>',
            $component->statusClass(),
            implode('', $attributes),
            htmlspecialchars($component->label, ENT_QUOTES)
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminAlertBox(array $data): string
    {
        $component = new AlertBox();
        $component->tag = (string) ($data['tag'] ?? $component->tag);
        $component->variant = (string) ($data['variant'] ?? $component->variant);
        $component->dismissible = (bool) ($data['dismissible'] ?? $component->dismissible);
        $component->bodyHtml = isset($data['bodyHtml']) ? (string) $data['bodyHtml'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        return sprintf(
            '<%1$s class="%2$s">%3$s%4$s</%1$s>',
            htmlspecialchars($component->tagName(), ENT_QUOTES),
            htmlspecialchars($component->classes(), ENT_QUOTES),
            $component->dismissible ? '<button type="button" class="close" data-dismiss="alert"></button>' : '',
            $component->bodyHtml ?? ''
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminOptionsToolbar(array $data): string
    {
        $component = new OptionsToolbar();
        $component->contentHtml = isset($data['contentHtml']) ? (string) $data['contentHtml'] : null;
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        return sprintf(
            '<div class="%s">%s</div>',
            htmlspecialchars($component->classes(), ENT_QUOTES),
            $component->contentHtml ?? ''
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminPageTitle(array $data): string
    {
        $component = new PageTitle();
        $component->title = (string) ($data['title'] ?? '');
        $component->subtitle = isset($data['subtitle']) ? (string) $data['subtitle'] : null;
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $component->actionsHtml = isset($data['actionsHtml']) ? (string) $data['actionsHtml'] : null;
        $component->contentHtml = isset($data['contentHtml']) ? (string) $data['contentHtml'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        $subtitle = $component->subtitle ? sprintf('<p class="mb-1 text-light">%s</p>', htmlspecialchars($component->subtitle, ENT_QUOTES)) : '';
        $actions = $component->actionsHtml ? sprintf('<div class="page-title-actions">%s</div>', $component->actionsHtml) : '';
        $content = $component->contentHtml ?? '';

        return sprintf(
            '<div class="%s"><div class="flex heading items-center w-full"><div><h1 class="heading">%s</h1>%s</div>%s</div>%s</div>',
            htmlspecialchars($component->classes(), ENT_QUOTES),
            htmlspecialchars($component->title, ENT_QUOTES),
            $subtitle,
            $actions,
            $content
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminSectionCard(array $data): string
    {
        $component = new SectionCard();
        $component->tag = isset($data['tag']) ? (string) $data['tag'] : $component->tag;
        $component->padding = (bool) ($data['padding'] ?? $component->padding);
        $component->title = isset($data['title']) ? (string) $data['title'] : null;
        $component->subtitle = isset($data['subtitle']) ? (string) $data['subtitle'] : null;
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;

        $tag = \in_array($component->tag, ['article', 'aside', 'div', 'section'], true) ? $component->tag : 'section';
        $header = '';
        if ($component->title || $component->subtitle) {
            $header = '<header class="p-4 pb-0">';
            if ($component->title) {
                $header .= sprintf('<h2 class="heading">%s</h2>', htmlspecialchars($component->title, ENT_QUOTES));
            }
            if ($component->subtitle) {
                $header .= sprintf('<p class="mb-0 text-light">%s</p>', htmlspecialchars($component->subtitle, ENT_QUOTES));
            }
            $header .= '</header>';
        }

        return sprintf(
            '<%1$s class="%2$s">%3$s%4$s</%1$s>',
            $tag,
            htmlspecialchars($component->surfaceClass(), ENT_QUOTES),
            $header,
            (string) ($data['contentHtml'] ?? '')
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminRowActions(array $data): string
    {
        $component = new RowActions();
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;
        $component->contentHtml = isset($data['contentHtml']) ? (string) $data['contentHtml'] : null;

        return sprintf(
            '<div class="%s">%s</div>',
            htmlspecialchars($component->classes(), ENT_QUOTES),
            $component->contentHtml ?? ''
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminPaginationFooter(array $data): string
    {
        $component = new PaginationFooter();
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $body = '<nav class="pagination-test"><a class="pagination-page-1">1</a><a class="pagination-page-2">2</a></nav>';

        if (!$component->hasWrapper()) {
            return $body;
        }

        return sprintf('<div class="%s">%s</div>', htmlspecialchars((string) $component->wrapperClass, ENT_QUOTES), $body);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderIntegratedAdminDataTable(array $data): string
    {
        $component = new DataTable();
        $component->wrapperClass = isset($data['wrapperClass']) ? (string) $data['wrapperClass'] : null;
        $component->tableClass = isset($data['tableClass']) ? (string) $data['tableClass'] : null;
        $component->extraClass = isset($data['extraClass']) ? (string) $data['extraClass'] : null;
        $component->headHtml = isset($data['headHtml']) ? (string) $data['headHtml'] : null;
        $component->bodyHtml = isset($data['bodyHtml']) ? (string) $data['bodyHtml'] : null;
        $component->tbodyAttributes = isset($data['tbodyAttributes']) ? (string) $data['tbodyAttributes'] : null;
        $component->emptyMessage = isset($data['emptyMessage']) ? (string) $data['emptyMessage'] : null;
        $component->colSpan = (int) ($data['colSpan'] ?? $component->colSpan);

        $table = sprintf('<table class="%s">', htmlspecialchars($component->tableClasses(), ENT_QUOTES));

        if ($component->headHtml) {
            $table .= sprintf('<thead>%s</thead>', $component->headHtml);
        }

        $tbodyAttributes = $component->tbodyAttributes ? ' '.$component->tbodyAttributes : '';
        if ($component->bodyHtml) {
            $table .= sprintf('<tbody%s>%s</tbody>', $tbodyAttributes, $component->bodyHtml);
        } else {
            $table .= sprintf('<tbody%s><tr><td colspan="%d">%s</td></tr></tbody>', $tbodyAttributes, $component->colSpan, htmlspecialchars((string) $component->emptyMessage, ENT_QUOTES));
        }

        $table .= '</table>';

        if (!$component->hasWrapper()) {
            return $table;
        }

        return sprintf('<div class="%s">%s</div>', htmlspecialchars((string) $component->wrapperClass, ENT_QUOTES), $table);
    }
}
