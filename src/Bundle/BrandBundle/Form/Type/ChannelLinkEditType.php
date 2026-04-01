<?php

declare(strict_types=1);

namespace Integrated\Bundle\BrandBundle\Form\Type;

use Integrated\Bundle\BrandBundle\Document\ChannelLink;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ChannelLink>
 */
final class ChannelLinkEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('default', CheckboxType::class, [
            'required' => false,
            'label' => 'Enabled by default for %name% brand',
            'label_translation_parameters' => ['%name%' => $options['brand_name']],
            'attr' => [
                'align_with_widget' => true,
                'label_col' => 'w-full',
                'location' => 'editor',
                'style' => 'inline',
            ],
        ]);

        $builder->add('channel', ChannelType::class, [
            'data_class' => Channel::class,
            'can_change_type' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChannelLink::class,
            'brand_name' => 'this',
        ]);
    }
}
