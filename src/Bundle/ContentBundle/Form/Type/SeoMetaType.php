<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Content\Embedded\SeoMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeoMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('focusKeyphrase', TextType::class, [
            'priority' => 999,
        ]);

        $builder->add('seoScore', TextType::class, [
            'priority' => 980,
        ]);

        $builder->add('readabilityScore', TextType::class, [
            'priority' => 980,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options): void {
            $data = $event->getData();

            self::configureSeoTextFields($event->getForm(), $data instanceof SeoMeta ? $data : null, $options);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => SeoMeta::class,
                'meta_title_fallback' => '%%title%% %%separator%% %%channel%%',
                'meta_description_fallback' => null,
            ]
        );

        $resolver->setAllowedTypes('meta_title_fallback', ['null', 'string']);
        $resolver->setAllowedTypes('meta_description_fallback', ['null', 'string']);
        $resolver->setNormalizer('meta_title_fallback', static function (Options $options, mixed $value): ?string {
            if (!\is_string($value)) {
                return null;
            }

            $value = trim($value);

            return $value === '' ? null : $value;
        });
        $resolver->setNormalizer('meta_description_fallback', static function (Options $options, mixed $value): ?string {
            if (!\is_string($value)) {
                return null;
            }

            $value = trim($value);

            return $value === '' ? null : $value;
        });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_seo_meta';
    }

    /**
     * @param FormInterface<mixed> $form
     * @param array<string, mixed> $options
     */
    private static function configureSeoTextFields(FormInterface $form, ?SeoMeta $seoMeta, array $options): void
    {
        $metaTitle = $seoMeta instanceof SeoMeta ? trim((string) $seoMeta->getMetatitle()) : '';
        $metaDescription = $seoMeta instanceof SeoMeta ? trim((string) $seoMeta->getMetadescription()) : '';

        $form->add('metaTitle', TextType::class, [
            'priority' => 990,
            'empty_data' => $metaTitle !== '' ? $metaTitle : $options['meta_title_fallback'],
            'data' => $metaTitle !== '' ? $metaTitle : $options['meta_title_fallback'],
        ]);

        $form->add('metaDescription', TextareaType::class, [
            'priority' => 970,
            'empty_data' => $metaDescription !== '' ? $metaDescription : $options['meta_description_fallback'],
            'data' => $metaDescription !== '' ? $metaDescription : $options['meta_description_fallback'],
        ]);
    }
}
