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

use Doctrine\Persistence\ManagerRegistry;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Form\DataTransformer\ContentTypeTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentSelectType extends AbstractType
{
    /**
     * @var ManagerRegistry
     */
    private $mr;

    /**
     * @var ContentTypeManager
     */
    private $contentTypeManager;

    public function __construct(ManagerRegistry $mr, ContentTypeManager $contentTypeManager)
    {
        $this->mr = $mr;
        $this->contentTypeManager = $contentTypeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ContentTypeTransformer($this->mr, $options['class']);

        $builder->addModelTransformer($transformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $contentTypes = [];
        $class = $form->getConfig()->getOption('class');

        foreach ($this->contentTypeManager->filterInstanceOf($class, true) as $contentType) {
            $contentTypes[] = $contentType->getId();
        }

        $view->vars['contentTypes'] = $contentTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'multiple' => true,
                'class' => Article::class,
            ]
        );
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
        return 'integrated_content_select';
    }
}
