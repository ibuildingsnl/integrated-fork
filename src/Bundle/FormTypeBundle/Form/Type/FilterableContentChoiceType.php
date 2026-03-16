<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Common\Content\Channel\ChannelManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterableContentChoiceType extends ContentChoiceType
{
    public function __construct(
        DocumentManager $dm,
        string $repositoryClass,
        string $route,
        ?array $params,
        private readonly ChannelManagerInterface $channelManager,
        private readonly ContentTypeManager $contentTypeManager,
    ) {
        parent::__construct($dm, $repositoryClass, $route, $params);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $channels = [];
        foreach ($this->channelManager->findAll() as $channel) {
            if (!$channel->getType() || $channel->getType()->getName() !== 'Website') {
                continue;
            }

            $channels[] = [
                'value' => $channel->getId(),
                'label' => $channel->getName(),
            ];
        }

        $contentTypes = [];
        foreach ($this->contentTypeManager->getAll() as $contentType) {
            $contentTypes[] = [
                'value' => $contentType->getId(),
                'label' => $contentType->getName(),
            ];
        }

        usort($channels, static fn (array $left, array $right): int => strcmp($left['label'], $right['label']));
        usort($contentTypes, static fn (array $left, array $right): int => strcmp($left['label'], $right['label']));

        $view->vars['show_channel_filter'] = (bool) $options['show_channel_filter'];
        $view->vars['show_content_type_filter'] = (bool) $options['show_content_type_filter'];
        $view->vars['channel_choices'] = $channels;
        $view->vars['content_type_choices'] = $contentTypes;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'show_channel_filter' => true,
            'show_content_type_filter' => true,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_filterable_content_choice';
    }
}
