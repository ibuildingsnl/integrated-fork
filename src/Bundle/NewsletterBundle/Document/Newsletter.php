<?php

namespace Integrated\Bundle\NewsletterBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Form\Type\SocialsType;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\RecurringScheduleEntry;
use Integrated\Bundle\NewsletterBundle\Form\ContentSelectionsType;
use Integrated\Bundle\NewsletterBundle\Form\RecurringScheduleEntryType;
use Integrated\Bundle\NewsletterBundle\Form\TestEmailAddressesType;
use Integrated\Bundle\StorageBundle\Form\Type\ImageDropzoneType;
use Integrated\Common\Content\Document\Storage\Embedded\StorageInterface;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

#[Type\Document('Newsletter')]
class Newsletter extends Content
{
    #[Type\Field]
    public string $title = 'untitled';

    /** @var string[] */
    #[Type\Field(type: TestEmailAddressesType::class)]
    public array $testAddresses = [];

    #[Type\Field(type: IntegerType::class)]
    public int $hoursBefore;

    #[Type\Field(type: RecurringScheduleEntryType::class)]
    public RecurringScheduleEntry $schedule;

    #[Type\Field(type: ImageDropzoneType::class)]
    public ?StorageInterface $logo = null;

    /** @var string[] */
    #[Type\Field(type: ContentSelectionsType::class)]
    public array $contentSelection = [];

    /** @var string[] */
    #[Type\Field(type: SocialsType::class)]
    public array $socials;

    public function __toString()
    {
        return $this->title;
    }
}
