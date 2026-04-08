<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\BlockBundle\Form\Type;

use Integrated\Bundle\BlockBundle\Document\Block\Block;
use Integrated\Bundle\BlockBundle\Form\DataTransformer\GroupTransformer;
use Integrated\Bundle\BlockBundle\Locator\LayoutLocator;
use Integrated\Bundle\ContentBundle\Form\Type\ActionsType;
use Integrated\Bundle\UserBundle\Form\Type\GroupType;
use Integrated\Bundle\UserBundle\Model\GroupManagerInterface;
use Integrated\Common\Form\Type\MetadataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BlockEditType extends AbstractType
{
    /**
     * @var LayoutLocator
     */
    private $layoutLocator;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var GroupManagerInterface
     */
    private $groupManager;

    public function __construct(
        LayoutLocator $layoutLocator,
        AuthorizationCheckerInterface $authorizationChecker,
        GroupManagerInterface $groupManager,
    ) {
        $this->layoutLocator = $layoutLocator;
        $this->authorizationChecker = $authorizationChecker;
        $this->groupManager = $groupManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            $block = $event->getForm()->getData();

            if (!\is_array($data) || !$block instanceof Block) {
                return;
            }

            // The id field is rendered disabled in edit forms and is therefore not posted.
            // Keep the existing id so the mapper does not try to write null.
            if ((!isset($data['id']) || $data['id'] === '') && $block->getId()) {
                $data['id'] = $block->getId();
                $event->setData($data);
            }
        });

        $layouts = $this->layoutLocator->getLayouts($options['type']);

        if (\count($layouts) === 1) {
            $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($layouts): void {
                $data = $event->getData();
                if ($data instanceof Block) {
                    $data->setLayout(current($layouts));
                }
            });
        } else {
            $builder->add('layout', LayoutChoiceType::class, [
                'type' => $options['type'],
            ]);
        }

        if ($this->authorizationChecker->isGranted('ROLE_WEBSITE_MANAGER') || $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $builder->add('groups', GroupType::class, [
                'required' => false,
                'multiple' => true,
                'label' => 'User group access',
                'attr' => [
                    'class' => 'select2',
                    'data-placeholder' => 'Block managers only',
                    'location' => 'sidebar',
                    'style' => 'sidebar',
                    'icon' => 'key-plus',
                    'state' => 'show',
                ],
            ]);

            $builder->get('groups')->addModelTransformer(new GroupTransformer($this->groupManager));
        }

        if ($options['method'] == 'PUT') {
            $builder->add('actions', ActionsType::class, ['buttons' => ['save', 'cancel']]);
        } else {
            $builder->add('actions', ActionsType::class, ['buttons' => ['create', 'cancel']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['type']);
    }

    public function getParent(): ?string
    {
        return MetadataType::class;
    }
}
