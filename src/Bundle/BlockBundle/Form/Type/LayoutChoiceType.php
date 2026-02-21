<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Form\Type;

use Integrated\Bundle\BlockBundle\Locator\LayoutLocator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class LayoutChoiceType extends AbstractType
{
    /**
     * @var LayoutLocator
     */
    protected $locator;

    public function __construct(LayoutLocator $locator)
    {
        $this->locator = $locator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choice_label' => function ($value, $key) {
                return $key;
            },
            'choices' => function (Options $options) {
                return $this->getChoiceList($options['type']);
            },
        ]);

        $resolver->setRequired([
            'type',
        ]);
    }

    /**
     * @return array
     */
    protected function getChoiceList($type)
    {
        $layouts = $this->locator->getLayouts($type);

        ksort($layouts);

        return $layouts;
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_block_layout_choice';
    }
}
