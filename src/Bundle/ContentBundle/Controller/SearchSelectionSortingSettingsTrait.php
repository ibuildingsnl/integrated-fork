<?php

namespace Integrated\Bundle\ContentBundle\Controller;

use Symfony\Component\Form\FormInterface;

trait SearchSelectionSortingSettingsTrait
{
    /**
     * @param FormInterface<mixed> $form
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function applySearchSelectionSortingSettings(FormInterface $form, array $filters): array
    {
        $sort = trim((string) $form->get('sort')->getData());
        $order = strtolower(trim((string) $form->get('order')->getData()));
        $customSort = $this->normalizeCustomSort((string) $form->get('customSort')->getData());
        $customSortEnabled = '__custom__' === $sort;

        if ($customSortEnabled && '' !== $customSort) {
            $filters['sort'] = 'custom:'.$customSort;
        } elseif ('' !== $sort && !$customSortEnabled) {
            $filters['sort'] = $sort;
        } else {
            unset($filters['sort']);
        }

        if (\in_array($order, ['asc', 'desc'], true)) {
            $filters['order'] = $order;
        } else {
            unset($filters['order']);
        }

        return $filters;
    }

    private function normalizeCustomSort(string $customSort): string
    {
        $parts = [];

        foreach ($this->parseCustomSortParts($customSort) as $part) {
            $parts[] = null !== $part['direction'] ? $part['field'].' '.$part['direction'] : $part['field'];
        }

        return implode(', ', $parts);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function applyCustomSorts(array $options): array
    {
        $sort = isset($options['sort']) && \is_string($options['sort']) ? trim($options['sort']) : '';
        if (!str_starts_with(strtolower($sort), 'custom:')) {
            return $options;
        }

        $sorts = $this->parseCustomSorts(substr($sort, 7));
        if ($sorts !== []) {
            $options['sorts'] = $sorts;
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function parseCustomSorts(string $customSort): array
    {
        $sorts = [];

        foreach ($this->parseCustomSortParts($customSort) as $part) {
            $sorts[$part['field']] = $part['direction'] ?? 'asc';
        }

        return $sorts;
    }

    /**
     * @return list<array{field: string, direction: string|null}>
     */
    private function parseCustomSortParts(string $customSort): array
    {
        $parts = [];

        foreach (explode(',', trim($customSort)) as $part) {
            $part = trim($part);
            if ('' === $part) {
                continue;
            }

            $tokens = preg_split('/\s+/', $part);
            if (false === $tokens) {
                continue;
            }

            $field = (string) array_shift($tokens);
            if (!preg_match('/^[A-Za-z0-9_.-]+$/', $field)) {
                continue;
            }

            $direction = strtolower((string) ($tokens[0] ?? ''));
            if ('' !== $direction && !\in_array($direction, ['asc', 'desc'], true)) {
                continue;
            }

            $parts[] = [
                'field' => $field,
                'direction' => '' !== $direction ? $direction : null,
            ];
        }

        return $parts;
    }
}
