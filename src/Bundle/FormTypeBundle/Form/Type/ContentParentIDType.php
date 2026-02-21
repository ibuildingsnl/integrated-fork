<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\FormTypeBundle\Form\Type;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentParentIDType extends AbstractType
{
    /**
     * @var DocumentManager
     */
    protected $dm;

    /**
     * @var string
     */
    protected $repositoryClass;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array|null
     */
    protected $params;
    private const VARNAMES = ['route', 'params', 'allow_clear'];

    /**
     * @param string $repositoryClass
     * @param string $route
     */
    public function __construct(DocumentManager $dm, $repositoryClass, $route, ?array $params = null)
    {
        $this->dm = $dm;
        $this->repositoryClass = $repositoryClass;
        $this->route = $route;
        $this->params = $params;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($this::VARNAMES as $varName) {
            $view->vars[$varName] = $options[$varName];
        }

        $view->vars['attr']['data-placeholder'] = $options['placeholder'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'repository_class' => $this->repositoryClass,
            'route' => $this->route,
            'params' => $this->params,
            'compound' => false,
            'required' => false,
            'placeholder' => null,
            'allow_clear' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'integrated_content_parent_id';
    }
}
