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

use Integrated\Common\Bulk\Action\HandlerFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeaturedHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @var OptionsResolver
     */
    private $resolver;

    public function __construct()
    {
        $this->resolver = new OptionsResolver();
        $this->resolver
            ->setRequired(['featured'])
            ->addAllowedTypes('featured', 'bool');
    }

    public function createHandler(array $options)
    {
        $options = $this->resolver->resolve($options);

        return new FeaturedHandler($options['featured']);
    }
}

