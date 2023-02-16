<?php

namespace Integrated\Bundle\NewsletterBundle\Form;

use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSelectionType extends AbstractType
{
    public function __construct(
        private readonly ResolverInterface $types,
        private readonly array $allowedDocumentTypes,
    ) {
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $choices = [];
        foreach ($this->types->getTypes() as $type) {
            if (in_array($type->getClass(), $this->allowedDocumentTypes)) {
                $choices[] = $type;
            }
        }
        $resolver->setDefaults([
            'choices' => $choices,
            'choice_value' => 'getId',
            'choice_label' => 'getName',
            'group_by' => fn (ContentTypeInterface $type) => substr(strrchr($type->getClass(), '\\'), 1),
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(
            fn (?string $id) => $id ? $this->types->getType($id) : null,
            fn (ContentTypeInterface $type) => $type->getId(),
        ));
    }
}
