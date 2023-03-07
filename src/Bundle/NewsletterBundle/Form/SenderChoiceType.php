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
        $senders = $this->senders->retrieve();
        $resolver->setDefaults([
            'choices' => isset($this->senders) ?
                array_combine($senders, $senders) :
                ['No newsletter platform connected' => ''],
            'placeholder' => false,
        ]);
    }
}
