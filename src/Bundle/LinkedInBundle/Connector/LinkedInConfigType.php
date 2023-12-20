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
        $builder->add('page', TextType::class, ['attr' => ['readonly' => 'false']]);
        $builder->add('apiStatus', TextType::class, ['attr' => ['readonly' => 'false']]);
        $builder->add('test', RefreshableChoiceType::class, ['choices' => ['Test' => 'test', 'Test 2' => 'test2']]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_social_linkedin';
    }
}
