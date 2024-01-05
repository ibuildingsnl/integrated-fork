<?php

namespace Integrated\Bundle\LinkedInBundle\Connector;

use Integrated\Bundle\FormTypeBundle\Form\Type\RefreshableChoiceType;
use Integrated\Bundle\LinkedInBundle\Form\AddLinkedInPageFieldListener;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Cache\CacheInterface;

class LinkedInConfigType extends AbstractType
{
    public function __construct(
        private readonly LinkedInFactory $linkedin,
        private readonly CacheInterface $cache
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new AddLinkedInPageFieldListener($this->linkedin, $this->cache));
        $builder->add('token', TextType::class, ['attr' => ['readonly' => 'true']]);
        $builder->add('page', RefreshableChoiceType::class, ['select' => ['choices' => ['Please finish setting up connection to LinkedIn' => 'empty'], 'attr' => ['disabled' => 'true']]]);
        $builder->add('apiStatus', TextType::class, ['attr' => ['readonly' => 'false']]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_social_linkedin';
    }
}
