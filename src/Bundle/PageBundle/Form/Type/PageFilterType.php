<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $pageTypeCounts = $options['page_type_counts'];
        $statusCounts = $options['status_counts'];
        $channelChoices = $options['channel_choices'];

        $builder->add('q', TextType::class, [
            'label' => 'Search query',
            'required' => false,
        ]);

        $builder->add('pagetype', ChoiceType::class, [
            'label' => 'Page type',
            'choices' => [
                $this->formatRawCountLabel('Static pages', (int) ($pageTypeCounts['page'] ?? 0)) => 'page',
                $this->formatRawCountLabel('Content type pages', (int) ($pageTypeCounts['contenttype'] ?? 0)) => 'contenttype',
            ],
            'choice_translation_domain' => false,
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);

        $builder->add('channel', ChoiceType::class, [
            'label' => 'Channel',
            'required' => false,
            'multiple' => true,
            'expanded' => true,
            'choices' => $channelChoices,
            'choice_translation_domain' => false,
        ]);

        $builder->add('status', ChoiceType::class, [
            'label' => 'Status',
            'choices' => [
                $this->formatRawCountLabel('Published', (int) ($statusCounts['published'] ?? 0)) => 'published',
                $this->formatRawCountLabel('Draft', (int) ($statusCounts['draft'] ?? 0)) => 'draft',
            ],
            'choice_translation_domain' => false,
            'multiple' => true,
            'expanded' => true,
            'required' => false,
        ]);

        // Keep GET form "submitted" even when all visible filters are empty.
        // This avoids stale session filters when users clear all checkboxes.
        $builder->add('_applied', HiddenType::class, [
            'mapped' => false,
            'data' => '1',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'page_type_counts' => [],
            'status_counts' => [],
            'channel_choices' => [],
        ]);
    }

    private function formatRawCountLabel(string $label, int $count): string
    {
        return \sprintf('%s %d', $label, $count);
    }
}
