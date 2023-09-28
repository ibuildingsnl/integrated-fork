<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Content;

interface PublishTimeInterface
{
    /**
     * @var string
     */
    public const DATE_MAX = '9999-12-31 00:00:00Z'; // @todo find a better way (INTEGRATED-429)

    public function getStartDate(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface $startDate
     *
     * @return $this
     */
    public function setStartDate(\DateTimeInterface $startDate = null);

    public function getEndDate(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface $endDate
     *
     * @return $this
     */
    public function setEndDate(\DateTimeInterface $endDate = null);

    /**
     * @param \DateTimeInterface $date
     */
    public function isPublished(\DateTimeInterface $date = null): bool;
}
