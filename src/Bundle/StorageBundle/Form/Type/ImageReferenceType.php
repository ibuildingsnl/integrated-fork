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

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\StorageBundle\Form\Mapper\ImageReferenceMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageReferenceType extends AbstractType
{
    public function __construct(
        private readonly DocumentManager $manager,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('image', ImageType::class);

        $builder->setDataMapper(new ImageReferenceMapper($this->manager, $options['channels']));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('channels', []);

        $resolver->addAllowedTypes('channels', ['array']);
        $resolver->setAllowedValues('channels', function ($channels) {
            foreach ($channels as $channel) {
                if (!$channel instanceof Channel) {
                    return false;
                }
            }

            return true;
        });
        parent::configureOptions($resolver);
    }

    public function getBlockPrefix()
    {
        return 'integrated_image_reference';
    }
}
