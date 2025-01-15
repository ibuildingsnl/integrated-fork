<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content\Embedded;

use Integrated\Common\Content\PublishTimeInterface;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
class PublishTime implements PublishTimeInterface
{
    /**
     * @var \DateTimeInterface
     */
    protected $startDate;

    /**
     * @var \DateTimeInterface
     */
    protected $endDate;

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function isPublished(?\DateTimeInterface $date = null): bool
    {
        if (null === $date) {
            $date = new \DateTime();
        }

        return $this->startDate <= $date && $this->endDate >= $date;
    }
}
