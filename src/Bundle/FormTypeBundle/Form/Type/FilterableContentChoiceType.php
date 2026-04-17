<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Services\WebsiteChannelResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterableContentChoiceType extends ContentChoiceType
{
    /** @var array<string, true> */
    private array $excludedContentTypeKeyLookup;

    /**
     * @param array<string, mixed>|null $params
     * @param list<string>              $excludedContentTypeKeys
     */
    public function __construct(
        DocumentManager $dm,
        string $repositoryClass,
        string $route,
        ?array $params,
        private readonly WebsiteChannelResolver $websiteChannelResolver,
        private readonly ContentTypeManager $contentTypeManager,
        private readonly array $excludedContentTypeKeys = [],
    ) {
        parent::__construct($dm, $repositoryClass, $route, $params);

        $this->excludedContentTypeKeyLookup = [];
        foreach ($this->excludedContentTypeKeys as $excludedContentTypeKey) {
            $normalizedKey = $this->normalizeContentTypeKey((string) $excludedContentTypeKey);
            if ($normalizedKey === '') {
                continue;
            }

            $this->excludedContentTypeKeyLookup[$normalizedKey] = true;
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $channels = [];
        foreach ($this->websiteChannelResolver->getWebsiteChannels() as $channel) {
            $channels[] = [
                'value' => $channel->getId(),
                'label' => $channel->getName(),
            ];
        }

        $contentTypes = [];
        foreach ($this->contentTypeManager->getAll() as $contentType) {
            if ($this->shouldExcludeContentType((string) $contentType->getId(), (string) $contentType->getName())) {
                continue;
            }

            $contentTypes[] = [
                'value' => $contentType->getId(),
                'label' => $contentType->getName(),
                'group' => is_a((string) $contentType->getClass(), Taxonomy::class, true) ? 'taxonomy' : 'content',
            ];
        }

        $view->vars['show_channel_filter'] = (bool) $options['show_channel_filter'];
        $view->vars['show_content_type_filter'] = (bool) $options['show_content_type_filter'];
        $view->vars['channel_choices'] = $channels;
        $view->vars['content_type_choices'] = $contentTypes;
        $view->vars['content_type_choice_groups'] = $this->buildContentTypeChoiceGroups($contentTypes);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults(
            [
                'show_channel_filter' => true,
                'show_content_type_filter' => true,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_filterable_content_choice';
    }

    /**
     * @param list<array{value:mixed,label:mixed,group:string}> $contentTypes
     *
     * @return array<string, array{label:string, choices:list<array{value:mixed,label:mixed}>}>
     */
    private function buildContentTypeChoiceGroups(array $contentTypes): array
    {
        $groups = [
            'content' => ['label' => 'Content', 'choices' => []],
            'taxonomy' => ['label' => 'Taxonomies', 'choices' => []],
        ];

        foreach ($contentTypes as $contentType) {
            $group = $contentType['group'] === 'taxonomy' ? 'taxonomy' : 'content';
            $groups[$group]['choices'][] = [
                'value' => $contentType['value'],
                'label' => $contentType['label'],
            ];
        }

        return array_filter($groups, static fn (array $group): bool => $group['choices'] !== []);
    }

    private function shouldExcludeContentType(string $id, string $name): bool
    {
        $normalizedId = $this->normalizeContentTypeKey($id);
        $normalizedName = $this->normalizeContentTypeKey($name);

        return isset($this->excludedContentTypeKeyLookup[$normalizedId])
               || isset($this->excludedContentTypeKeyLookup[$normalizedName]);
    }

    private function normalizeContentTypeKey(string $value): string
    {
        return strtolower(trim(str_replace('-', '_', $value)));
    }
}
