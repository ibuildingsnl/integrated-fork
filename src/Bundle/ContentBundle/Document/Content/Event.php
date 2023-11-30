<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Document\Content;

use Integrated\Common\Form\Mapping\Attributes as Type;

/**
 * @author Ger Jan van den Bosch <gerjan@e-active.nl>
 */
#[Type\Document('Event')]
class Event extends Article
{
    /**
     * @var \DateTime
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\DateTimeType', options: [
        'priority' => 495,
        'label' => 'Event start',
        'attr' => ['style' => 'sidebar', 'icon' => 'calendar', 'class' => 'datetime', 'data-set-date-text' => 'Set start date'],
    ], location: 'sidebar')]
    protected $startDate;

    /**
     * @var \DateTime
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\DateTimeType', options: [
        'priority' => 494,
        'label' => 'Event end',
        'attr' => ['style' => 'sidebar', 'icon' => 'calendar', 'class' => 'datetime', 'data-set-date-text' => 'Set end date'],
    ], location: 'sidebar')]
    protected $endDate;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'www']], location: 'sidebar')]
    protected $website;

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        if ($this->startDate === null) {
            return new \DateTime();
        }
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     *
     * @return $this
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     *
     * @return $this
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param string $website
     *
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }
}
