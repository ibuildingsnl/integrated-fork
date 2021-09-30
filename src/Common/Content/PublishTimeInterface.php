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

use DateTimeImmutable;
use DateTimeInterface;
use DateTime;

interface PublishTimeInterface
{
    /**
     * @var string
     */
    public const DATE_MAX = '9999-12-31 00:00:00';
     // @todo find a better way (INTEGRATED-429)
    /**
     * @return DateTime|DateTimeImmutable|null
     */
    public function getStartDate(): ?DateTimeInterface;

    /**
     * @param DateTime $startDate
     *
     * @return $this
     */
    public function setStartDate(DateTimeInterface $startDate = null);

    /**
     * @return DateTime|DateTimeImmutable|null
     */
    public function getEndDate(): ?DateTimeInterface;

    /**
     * @param DateTime $endDate
     *
     * @return $this
     */
    public function setEndDate(DateTimeInterface $endDate = null);

    /**
     * @param DateTime $date
     *
     * @return bool
     */
    public function isPublished(DateTimeInterface $date = null): bool;
}
