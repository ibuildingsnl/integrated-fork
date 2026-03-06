<?php

namespace Integrated\Bundle\ContentBundle\Twig\Component;

use Integrated\Bundle\ContentBundle\Services\ArticleSearchServiceInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;

#[AsLiveComponent(
    'integrated_content_article_search',
    template: '@IntegratedContent/article_search/components/live_component.html.twig'
)]
class ArticleSearchLiveComponent
{
    /**
     * Default LiveComponent action.
     *
     * Kept explicit for compatibility with Symfony UX LiveComponent versions
     * where DefaultActionTrait is unavailable.
     */
    public function __invoke(): void
    {
    }

    /** @var array<int, array{key: string, label: string}> */
    #[LiveProp]
    public array $channels = [];

    /** @var array<int, array{key: string, label: string}> */
    #[LiveProp]
    public array $contentTypes = [];

    /** @var array<string, string> */
    #[LiveProp]
    public array $translations = [];

    #[LiveProp]
    public bool $existing = false;

    #[LiveProp(writable: true)]
    public string $channelId = '';

    #[LiveProp(writable: true)]
    public string $searchTerm = '';

    #[LiveProp(writable: true)]
    public string $linkText = '';

    #[LiveProp(writable: true)]
    public string $linkTitle = '';

    #[LiveProp(writable: true)]
    public bool $openInNewTab = false;

    #[LiveProp(writable: true)]
    public string $selectedId = '';

    /** @var array<int, string> */
    #[LiveProp(writable: true)]
    public array $contentTypeIds = [];

    public function __construct(
        private readonly ArticleSearchServiceInterface $articleSearchService,
    ) {
    }

    /**
     * @param array<int, array{key: string, label: string}> $channels
     * @param array<int, array{key: string, label: string}> $contentTypes
     * @param array<string, string>                         $translations
     * @param array<string, mixed>                          $searchData
     */
    public function mount(array $channels, array $contentTypes, array $translations, array $searchData = []): void
    {
        $this->channels = $channels;
        $this->contentTypes = $contentTypes;
        $this->translations = $translations;

        $this->linkText = (string) ($searchData['selectionText'] ?? '');
        $this->linkTitle = (string) ($searchData['title'] ?? '');
        $this->searchTerm = (string) ($searchData['url'] ?? '');
        $this->openInNewTab = (bool) ($searchData['openInNewTab'] ?? false);
        $this->existing = (bool) ($searchData['existing'] ?? false);

        if (\count($this->channels) === 1) {
            $this->channelId = (string) ($this->channels[0]['key'] ?? '');
        }
    }

    public function isSearchTermUrl(): bool
    {
        if ($this->searchTerm === '') {
            return false;
        }

        if (
            str_starts_with($this->searchTerm, '/')
            || str_starts_with($this->searchTerm, '#')
            || str_starts_with($this->searchTerm, 'mailto:')
            || str_starts_with($this->searchTerm, 'tel:')
            || str_starts_with($this->searchTerm, 'www')
        ) {
            return true;
        }

        return (bool) filter_var($this->searchTerm, \FILTER_VALIDATE_URL);
    }

    /**
     * @return array<int, array{id: string, title: string, subtitle: string, text: string, url: string}>
     */
    public function getResults(): array
    {
        if ($this->channelId === '' || $this->searchTerm === '' || $this->isSearchTermUrl()) {
            return [];
        }

        $channel = $this->articleSearchService->findChannel($this->channelId);

        if ($channel === null) {
            return [];
        }

        $allowedContentTypeIds = array_column($this->contentTypes, 'key');
        $contentTypeIds = array_values(array_filter(
            $this->contentTypeIds,
            static fn (string $contentTypeId): bool => \in_array($contentTypeId, $allowedContentTypeIds, true)
        ));

        return $this->articleSearchService->searchInChannel($channel, $this->searchTerm, $contentTypeIds);
    }
}
