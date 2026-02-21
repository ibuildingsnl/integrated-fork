<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\PageBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\PageBundle\Document\Page\ContentTypePage;
use Integrated\Common\Content\Channel\ChannelInterface;

/**
 * @author Johan Liefers <johan@e-active.nl>
 */
class ContentTypePageService
{
    public function __construct(
        protected readonly ContentTypeControllerManager $controllerManager,
        protected readonly DocumentManager $dm,
    ) {
    }

    public function addContentType(ContentType $contentType, ChannelInterface $channel)
    {
        $controller = $this->controllerManager->getController($contentType->getClass());

        if (!$controller || !($type = $channel->getType()) || $type->getName() != 'Website') {
            return;
        }

        $contentTypePage = new ContentTypePage($contentType, $channel);

        $contentTypePage->setControllerService($controller['serviceId']);
        $contentTypePage->setControllerAction($controller['actions'][0]);

        $this->dm->persist($contentTypePage);
        $this->dm->flush();
    }
}
