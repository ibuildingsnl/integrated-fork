<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\StorageBundle\Form\Type;

use Integrated\Bundle\ImageBundle\Validator\Constraints\OnTheFlyFormatConverterConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Johnny Borg <johnny@e-active.nl>
 */
class ImageType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [new OnTheFlyFormatConverterConstraint()],
        ]);
    }

    public function getParent(): ?string
    {
        return FileType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_image';
    }
}
