<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Form\Extension;

use Integrated\Bundle\ContentBundle\StaticContent\StaticContentRepository;
use Symfony\Component\Form\AbstractTypeExtension as BaseAbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class AbstractTypeExtension extends BaseAbstractTypeExtension
{
    /**
     * @var StaticContentRepository
     */
    private $staticContentRepository;

    /**
     * AbstractTypeExtension constructor.
     *
     * @param StaticContentRepository $staticContentRepository
     */
    public function __construct(StaticContentRepository $staticContentRepository)
    {
        $this->staticContentRepository = $staticContentRepository;
    }

    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        // use FormType::class to modify (nearly) every field in the system
        return FormType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $object = $options['data'] ?? null;

        if (!\is_object($object)) {
            return;
        }

        if (!$data = $this->staticContentRepository->get($object)) {
            return;
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($data) {
            $form = $event->getForm();
            foreach ($data['fields'] as $key => $field) {
                if ($form->has($key)) {
                    $form->remove($key);
                }
            }
        });
    }
}
