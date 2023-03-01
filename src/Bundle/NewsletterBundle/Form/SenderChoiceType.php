<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\NewsletterBundle\Service\SenderOptionsFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SenderChoiceType extends AbstractType
{
    public function __construct(
        private readonly ?SenderOptionsFetcher $senders = null
    ) {
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => isset($this->senders) ?
                array_flip($this->senders->retrieve()) :
                ['No newsletter platform connected' => ''],
            'placeholder' => false,
        ]);
    }
}
