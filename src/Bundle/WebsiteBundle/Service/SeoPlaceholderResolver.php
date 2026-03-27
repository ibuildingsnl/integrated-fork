<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Service;

final class SeoPlaceholderResolver
{
    /**
     * @return array<int, array{token: string, label: string}>
     */
    public function definitions(): array
    {
        return [
            ['token' => '%%title%%', 'label' => 'Titel'],
            ['token' => '%%site_title%%', 'label' => 'Sitetitel'],
            ['token' => '%%separator%%', 'label' => 'Scheidingsteken'],
            ['token' => '%%slug%%', 'label' => 'Slug'],
            ['token' => '%%channel%%', 'label' => 'Kanaal'],
        ];
    }

    public function resolve(?string $value, array $context = []): ?string
    {
        if ($value === null) {
            return null;
        }

        $resolved = strtr($value, [
            '%%title%%' => $this->normalizeText($context['title'] ?? null),
            '%%site_title%%' => $this->normalizeText($context['site_title'] ?? null),
            '%%separator%%' => $this->normalizeSeparator($context['separator'] ?? null),
            '%%slug%%' => $this->normalizeSlug($context['slug'] ?? null),
            '%%channel%%' => $this->normalizeText($context['channel'] ?? null),
        ]);

        $resolved = preg_replace('/[ \t]{2,}/', ' ', $resolved) ?? $resolved;
        $resolved = preg_replace('/\s+\|$/', '', $resolved) ?? $resolved;
        $resolved = preg_replace('/^\|\s+/', '', $resolved) ?? $resolved;

        return trim($resolved);
    }

    private function normalizeText(mixed $value): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        return trim((string) $value);
    }

    private function normalizeSeparator(mixed $value): string
    {
        $value = $this->normalizeText($value);

        return $value === '' ? '|' : $value;
    }

    private function normalizeSlug(mixed $value): string
    {
        $value = $this->normalizeText($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            $path = (string) parse_url($value, PHP_URL_PATH);
            $value = $path !== '' ? $path : $value;
        }

        return trim($value, '/');
    }
}
