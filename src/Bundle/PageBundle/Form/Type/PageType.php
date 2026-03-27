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

use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryType;
use Integrated\Bundle\ContentBundle\Form\Type\SeoMetaType;
use Integrated\Bundle\ContentBundle\Form\Type\CheckboxSwitcherType;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Content\Channel\ChannelInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class PageType extends AbstractType
{
    /**
     * @var ChannelContextInterface
     */
    private $channelContext;

    /**
     * @var ThemeResolver
     */
    private $themeResolver;

    /**
     * @var bool
     */
    private $defaultPaginatedNoindex;

    public function __construct(
        ChannelContextInterface $channelContext,
        ThemeResolver $themeResolver,
        bool $defaultPaginatedNoindex = true
    )
    {
        $this->channelContext = $channelContext;
        $this->themeResolver = $themeResolver;
        $this->defaultPaginatedNoindex = $defaultPaginatedNoindex;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $channel = $this->channelContext->getChannel();

        if ($page = $builder->getData()) {
            if ($page->getChannel()) {
                $channel = $page->getChannel();
            }
        }

        $builder->add('channel', ChannelChoiceType::class, [
            'return_object' => true,
            'disabled' => $page && $page->getChannel(),
            'data' => $channel,
            'filter' => ['type.$id' => 'website'],
        ]);

        $builder->add('title', TextType::class);

        if (!$options['short']) {
            $builder->add('description', TextareaType::class, [
                'required' => false,
            ]);

            $builder->add('seoMetadata', SeoMetaType::class, [
                'label' => false,
                'required' => false,
                'meta_title_fallback' => $page ? (string) $page->getTitle() : null,
                'meta_description_fallback' => $page ? (string) $page->getDescription() : null,
            ]);

            $builder->add('canonicalUrl', TextType::class, [
                'label' => 'Canonical URL',
                'required' => false,
            ]);

            $builder->add('featuredImage', MediaGalleryType::class, [
                'label' => 'Featured image',
                'required' => false,
                'attr' => [
                    'style' => 'sidebar',
                    'icon' => 'media-image',
                    'data-types' => '[{"type":"image","name":"Image"}]',
                    'data-emptytext' => 'Select featured image',
                    'data-multiple' => false,
                ],
            ]);

            $builder->add('robotsDirective', ChoiceType::class, [
                'label' => 'Robots',
                'required' => false,
                'choices' => [
                    'Default' => 'default',
                    'index,follow' => 'index,follow',
                    'noindex,follow' => 'noindex,follow',
                    'index,nofollow' => 'index,nofollow',
                    'noindex,nofollow' => 'noindex,nofollow',
                ],
                'data' => null === $page || null === $page->getRobotsDirective()
                    ? 'default'
                    : $page->getRobotsDirective(),
            ]);

            $builder->add('twitterCard', ChoiceType::class, [
                'label' => 'Twitter card',
                'required' => false,
                'choices' => [
                    'Default' => 'default',
                    'summary' => 'summary',
                    'summary_large_image' => 'summary_large_image',
                ],
                'data' => null === $page || null === $page->getTwitterCard()
                    ? 'default'
                    : $page->getTwitterCard(),
            ]);
        }

        $builder->add('path', TextType::class, [
            'label' => 'URL',
            'required' => false,
            'constraints' => [
                new NotBlank(),
                new Regex('/^\/$|(\/[a-zA-Z_0-9-\.\/]+)+$/'),
            ],
        ]);

        if (!$options['short']) {
            $builder->add('disabled', CheckboxSwitcherType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ]);

            $builder->add('paginationNoindexEnabled', CheckboxSwitcherType::class, [
                'label' => 'Noindex paginated pages',
                'required' => false,
                'empty_data' => '0',
                'data' => null === $page || null === $page->isPaginationNoindexEnabled()
                    ? $this->defaultPaginatedNoindex
                    : $page->isPaginationNoindexEnabled(),
                'attr' => [
                    'align_with_widget' => true,
                ],
            ]);
        }

        $formModifier = function (FormInterface $form, ?ChannelInterface $channel = null): void {
            $theme = null === $channel ? 'default' : $this->themeResolver->getTheme($channel);

            $form->add('layout', LayoutChoiceType::class, [
                'theme' => $theme,
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier): void {
                $data = $event->getData();

                $channel = $this->channelContext->getChannel();
                if ($data && $data->getChannel()) {
                    $channel = $data->getChannel();
                }

                $formModifier($event->getForm(), $channel);
            }
        );

        $builder->get('channel')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier): void {
                $formModifier($event->getForm()->getParent(), $event->getForm()->getData());
            }
        );

        $builder->get('path')->addModelTransformer(new CallbackTransformer(
            function ($path) {
                return ltrim($path, '/');
            },
            function ($path) {
                return '/'.$path;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('short', false);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_page_page';
    }
}
