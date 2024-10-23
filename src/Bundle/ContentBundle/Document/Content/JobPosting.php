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
#[Type\Document('JobPosting')]
class JobPosting extends Article
{
    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $jobTitle;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'euro']], location: 'sidebar')]
    protected $salary;

    /**
     * @var string
     */
    #[Type\Field(options: ['attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $applyUrl;

    /**
     * @var string
     */
    #[Type\Field(options: ['label' => 'Working Hours', 'attr' => ['style' => 'sidebar', 'icon' => 'link']], location: 'sidebar')]
    protected $workingHours;

    /**
     * @var \DateTime
     */
    #[Type\Field(type: 'Symfony\Component\Form\Extension\Core\Type\DateTimeType', options: [
        'priority' => 495,
        'label' => 'Apply before',
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'calendar',
            'class' => 'datetime',
            'data-set-date-text' => 'Set end date',
        ],
        'html5' => true,
        'date_widget' => 'single_text',
        'time_widget' => 'single_text',
    ], location: 'sidebar')]
    protected $applyBefore;

    /**
     * @var Relation\Company
     */
    protected $company;

    /**
     * @var Relation\Person
     */
    protected $contact;

    /**
     * @return string
     */
    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    /**
     * @param string $jobTitle
     *
     * @return $this
     */
    public function setJobTitle($jobTitle)
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalary()
    {
        return $this->salary;
    }

    /**
     * @param string $salary
     *
     * @return $this
     */
    public function setSalary($salary)
    {
        $this->salary = $salary;

        return $this;
    }

    /**
     * @return string
     */
    public function getApplyUrl()
    {
        return $this->applyUrl;
    }

    /**
     * @param string $applyUrl
     *
     * @return $this
     */
    public function setApplyUrl($applyUrl)
    {
        $this->applyUrl = $applyUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getWorkingHours()
    {
        return $this->workingHours;
    }

    /**
     * @param string $workingHours
     *
     * @return $this
     */
    public function setWorkingHours($workingHours)
    {
        $this->workingHours = $workingHours;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getApplyBefore()
    {
        if ($this->applyBefore === null) {
            return new \DateTime();
        }

        return $this->applyBefore;
    }

    /**
     * @param \DateTime $applyBefore
     *
     * @return $this
     */
    public function setApplyBefore(\DateTime $applyBefore = null)
    {
        $this->applyBefore = $applyBefore;

        return $this;
    }

    /**
     * @return Relation\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param Relation\Company $company
     *
     * @return $this
     */
    public function setCompany(Relation\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Relation\Person
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param Relation\Person $contact
     *
     * @return $this
     */
    public function setContact(Relation\Person $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }
}
