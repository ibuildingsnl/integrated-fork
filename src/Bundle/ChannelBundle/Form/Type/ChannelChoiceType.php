<?php

namespace Integrated\Bundle\ChannelBundle\Form\Type;

use Doctrine\Bundle\MongoDBBundle\Form\Type\DocumentType;
use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ChannelBundle\Form\DataTransformer\ChannelTransformer;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChannelChoiceType extends AbstractType
{
    public function __construct(
        private readonly ObjectRepository $repository
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['return_object']) {
            $builder->addModelTransformer(new ChannelTransformer($this->repository, $options['multiple']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('class', Channel::class);
        $resolver->setDefault('choice_label', 'name');
        $resolver->setDefault('placeholder', 'Select a channel');
        $resolver->setDefault('return_object', false);
        $resolver->setDefault('filter', []);

        $resolver->setNormalizer('choices', function (Options $options, mixed $value) {
            return $this->repository->findBy($options['filter']);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return DocumentType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_channel_choice';
    }
}
