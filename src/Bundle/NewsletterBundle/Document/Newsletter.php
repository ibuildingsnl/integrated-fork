<?php

namespace Integrated\Bundle\NewsletterBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\NewsletterBundle\Form\RecurringScheduleEntryType;
use Integrated\Bundle\NewsletterBundle\Form\TestEmailAddressesType;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\RecurringScheduleEntry;
use Integrated\Common\Form\Mapping\Attributes as Type;

#[Type\Document('Newsletter')]
class Newsletter extends Content
{
    #[Type\Field]
    public string $title = 'untitled';
    /** @var string[] */
    #[Type\Field(type: TestEmailAddressesType::class)]
    public array $testAddresses = [];
    #[Type\Field(type: RecurringScheduleEntryType::class)]
    public RecurringScheduleEntry $schedule;

    public function __toString()
    {
        return $this->title;
    }
}
