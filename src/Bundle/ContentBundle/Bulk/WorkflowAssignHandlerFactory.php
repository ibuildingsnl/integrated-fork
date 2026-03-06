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
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Bulk\Action\HandlerFactoryInterface;
use Integrated\Common\ContentType\ResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowAssignHandlerFactory implements HandlerFactoryInterface
{
    private EntityManagerInterface $entityManager;
    private ResolverInterface $resolver;
    private UserManagerInterface $userManager;
    private OptionsResolver $optionsResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        ResolverInterface $resolver,
        UserManagerInterface $userManager,
    ) {
        $this->entityManager = $entityManager;
        $this->resolver = $resolver;
        $this->userManager = $userManager;
        $this->optionsResolver = new OptionsResolver();
        $this->optionsResolver
            ->setDefaults(['assigned' => null])
            ->setAllowedTypes('assigned', ['string', 'null']);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function createHandler(array $options)
    {
        $options = $this->optionsResolver->resolve($options);

        return new WorkflowAssignHandler(
            $this->entityManager,
            $this->resolver,
            $this->userManager,
            $options['assigned']
        );
    }
}
