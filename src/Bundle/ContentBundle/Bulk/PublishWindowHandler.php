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
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Common\Bulk\Action\HandlerInterface;
use Integrated\Common\Content\ContentInterface;

class PublishWindowHandler implements HandlerInterface
{
    /**
     * @var \DateTimeInterface|null
     */
    private $startDate;

    /**
     * @var \DateTimeInterface|null
     */
    private $endDate;

    public function __construct(?\DateTimeInterface $startDate = null, ?\DateTimeInterface $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * @return void
     */
    public function execute(ContentInterface $content)
    {
        if (!$content instanceof Content) {
            return;
        }

        $publishTime = $content->getPublishTime();
        if (!$publishTime instanceof PublishTime) {
            $publishTime = new PublishTime();
            $content->setPublishTime($publishTime);
        }

        $publishTime->setStartDate($this->startDate);
        $publishTime->setEndDate($this->endDate);
    }
}
