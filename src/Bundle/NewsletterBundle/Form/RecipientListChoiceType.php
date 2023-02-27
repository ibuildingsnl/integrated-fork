<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Bundle\NewsletterBundle\Service\RecipientListOptionsFetcher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipientListChoiceType extends AbstractType
{
    public function __construct(
        private readonly ?RecipientListOptionsFetcher $recipients = null
    ) {
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => isset($this->recipients) ?
                array_flip($this->recipients->retrieve()) :
                ['No newsletter platform connected' => ''],
        ]);
    }
}
