<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use Integrated\Common\Bulk\Action\HandlerFactoryInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowStateHandlerFactory implements HandlerFactoryInterface
{
    private EntityManagerInterface $entityManager;
    private ResolverInterface $resolver;
    private OptionsResolver $optionsResolver;

    public function __construct(EntityManagerInterface $entityManager, ResolverInterface $resolver)
    {
        $this->entityManager = $entityManager;
        $this->resolver = $resolver;
        $this->optionsResolver = new OptionsResolver();
        $this->optionsResolver
            ->setRequired(['state'])
            ->setAllowedTypes('state', 'string');
    }

    public function createHandler(array $options)
    {
        $options = $this->optionsResolver->resolve($options);

        return new WorkflowStateHandler(
            $this->entityManager,
            $this->resolver,
            $options['state']
        );
    }
}

