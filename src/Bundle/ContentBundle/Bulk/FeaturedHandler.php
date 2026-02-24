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

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Common\Bulk\Action\HandlerInterface;
use Integrated\Common\Content\ContentInterface;

class FeaturedHandler implements HandlerInterface
{
    /**
     * @var bool
     */
    private $featured;

    public function __construct(bool $featured)
    {
        $this->featured = $featured;
    }

    public function execute(ContentInterface $content)
    {
        if (!$content instanceof Content || !\method_exists($content, 'setFeatured')) {
            return;
        }

        $content->setFeatured($this->featured);
    }
}
