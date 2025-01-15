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

use Integrated\Bundle\PageBundle\Locator\LayoutLocator;
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
    private $locator;

    public function __construct(LayoutLocator $locator)
    {
        $this->locator = $locator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'theme' => 'default',
                'directory' => null,
                'choice_label' => function ($value, $key) {
                    return $key;
                },
                'choices' => function (Options $options) {
                    return array_unique($this->locator->getLayouts($options['theme'], $options['directory']));
                },
            ]
        );
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_page_layout_choice';
    }
}
