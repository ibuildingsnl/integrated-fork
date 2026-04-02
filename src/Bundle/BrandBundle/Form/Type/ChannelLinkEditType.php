<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Channel>
 */
final class ChannelLinkEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('linkDefault', CheckboxType::class, [
            'required' => false,
            'label' => 'Enabled by default for %name% brand',
            'label_translation_parameters' => ['%name%' => $options['brand_name']],
            'mapped' => false,
            'data' => (bool) $options['link_default'],
            'attr' => [
                'align_with_widget' => true,
                'label_col' => 'w-full',
                'location' => 'editor',
                'style' => 'inline',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('brand_name', 'this');
        $resolver->setDefault('link_default', false);
    }

    public function getParent(): string
    {
        return ChannelType::class;
    }
}
