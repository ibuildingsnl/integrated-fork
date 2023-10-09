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

use Integrated\Bundle\ContentBundle\Document\Channel\ChannelRepository;
use Integrated\Common\Bulk\Action\HandlerFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class ChannelHandlerFactory implements HandlerFactoryInterface
{
    /**
     * @var OptionsResolver
     */
    private OptionsResolver $resolver;

    public function __construct(
        private readonly string $class,
        private readonly ChannelRepository $channelRepository,
        private readonly AuthorizationChecker $authorizationChecker
    ) {
        $this->resolver = (new OptionsResolver())->setRequired(['channel'])->addAllowedTypes('channel', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function createHandler(array $options)
    {
        $options = $this->resolver->resolve($options);
        $class = $this->class;

        return new $class($options['channel'], $this->channelRepository, $this->authorizationChecker);
    }
}
