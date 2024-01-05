<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Type\Job;

use Integrated\Bundle\FormTypeBundle\Form\Type\TailwindCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class ContactPersonsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => CompanyJobType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'label' => 'Jobs',
            'delete_button_text' => 'x',
            'add_button_text' => 'Add Job',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): ?string
    {
        return TailwindCollectionType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_contact_persons';
    }
}
