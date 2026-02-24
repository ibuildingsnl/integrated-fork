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

use Integrated\Bundle\ContentBundle\Bulk\ContentTypeHandler;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\ContentTypeAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BulkActionContentTypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'contentType',
            ContentTypeChoice::class,
            [
                'label' => false,
                'multiple' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('data_class', ContentTypeAction::class)
            ->setDefault('empty_data', function (Options $options) {
                $action = new ContentTypeAction(ContentTypeHandler::class);

                return $action;
            });
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_bulk_action_content_type';
    }
}
