<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Integrated\Bundle\ContentBundle\Form\DataTransformer\CsvArray as Transformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type which can handle comma separated values and returns an array.
 *
 * @author Jeroen van Leeuwen <jeroen@e-active.nl>
 */
class CsvArray extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new Transformer());
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_csv_array';
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
