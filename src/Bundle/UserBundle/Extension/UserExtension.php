<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\UserBundle\Extension;

use Integrated\Bundle\UserBundle\Extension\Subscriber\ContentSubscriber;
use Integrated\Bundle\UserBundle\Extension\Subscriber\MetadataSubscriber;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Content\Extension\ExtensionInterface;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class UserExtension implements ExtensionInterface
{
    private UserManagerInterface $manager;

    public function __construct(UserManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function getSubscribers()
    {
        return [
            new ContentSubscriber($this, $this->manager),
            new MetadataSubscriber($this),
        ];
    }

    public function getName()
    {
        return 'integrated.extension.user';
    }
}
