<?php

namespace Integrated\Bundle\ContentBundle\Form\Type;

use Doctrine\Persistence\ManagerRegistry;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Relation\Person;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\AuthorTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuthorType extends AbstractType
{
    public function __construct(
        private readonly ManagerRegistry $mr,
        private readonly ContentTypeManager $contentTypeManager,
        private readonly array $authorContentTypes
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new AuthorTransformer($this->mr);

        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $contentTypes = [];

        if (count($this->authorContentTypes) > 0) {
            foreach ($contentTypes as $contentType) {
                $contentTypes[$contentType] = $this->contentTypeManager->getType($contentType)->getName();
            }
        } else {
            foreach ($this->contentTypeManager->filterInstanceOf(Person::class) as $contentType) {
                if ($contentType instanceof ContentType) {
                    $contentTypes[$contentType->getId()] = $contentType->getName();
                }
            }
        }

        $view->vars['contentTypes'] = $contentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'multiple' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'integrated_author';
    }
}
