<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Form\Type;

use Integrated\Bundle\ChannelBundle\Form\Type\ChannelChoiceType;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Bundle\PageBundle\Form\EventListener\ContentTypePageListener;
use Integrated\Bundle\PageBundle\Resolver\ThemeResolver;
use Integrated\Bundle\PageBundle\Services\ContentTypeControllerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTypePageType extends AbstractType
{
    private ContentTypeControllerManager $manager;
    private ThemeResolver $resolver;

    public function __construct(ContentTypeControllerManager $manager, ThemeResolver $resolver)
    {
        $this->manager = $manager;
        $this->resolver = $resolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('channel', ChannelChoiceType::class, [
            'return_object' => true,
            'disabled' => true,
        ]);

        $builder->add('path', TextType::class, [
            'label' => 'URL',
        ]);

        $builder->add('layout', LayoutChoiceType::class);
        $builder->addEventSubscriber(new ContentTypePageListener($this->manager, $this->resolver));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContentTypePage::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_page_content_type_page';
    }
}
