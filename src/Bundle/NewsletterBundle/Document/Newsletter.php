<?php

namespace Integrated\Bundle\NewsletterBundle\Document;

use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Document\Content\Image;
use Integrated\Bundle\ContentBundle\Form\Type\MediaGalleryImageType;
use Integrated\Bundle\ContentBundle\Form\Type\SocialsType;
use Integrated\Bundle\NewsletterBundle\Document\Schedule\RecurringScheduleEntry;
use Integrated\Bundle\NewsletterBundle\Form\ContentSelectionsType;
use Integrated\Bundle\NewsletterBundle\Form\RecurringScheduleEntryType;
use Integrated\Bundle\NewsletterBundle\Form\TestEmailAddressesType;
use Integrated\Common\Form\Mapping\Attributes as Type;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

#[Type\Document('Newsletter')]
class Newsletter extends Content
{
    #[Type\Field(options: ['priority' => 990, 'attr' => ['style' => 'editor', 'state' => 'show']], location: 'editor')]
    public string $title;

    #[Type\Field(options: ['priority' => 980, 'attr' => ['style' => 'editor', 'state' => 'show']], location: 'editor')]
    public string $headline;

    #[Type\Field(type: TestEmailAddressesType::class, options: [
        'priority' => 490,
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'group',
            'show_headings' => 'false',
        ],
    ], location: 'sidebar')]
    public array $testAddresses = [];

    #[Type\Field(type: IntegerType::class, options: [
        'label' => 'Generation time',
        'priority' => 480,
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'clock-outline',
            'show_headings' => 'false',
            'help_text' => 'Email is generated the amount of time given before sending.',
        ],
    ], location: 'sidebar')]
    public int $hoursBefore;

    #[Type\Field(type: RecurringScheduleEntryType::class, options: [
        'label' => 'Sending Schedule',
        'priority' => 490,
        'attr' => [
            'style' => 'editor',
            'show_headings' => 'false',
            'state' => 'show',
        ],
    ], location: 'editor')]
    public RecurringScheduleEntry $schedule;

    #[Type\Field(type: MediaGalleryImageType::class, options: [
        'label' => 'Logo',
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'media-image',
            'data-types' => '[{"type":"image","name":"Image"}]',
            'data-emptytext' => 'Select logo',
            'data-multiple' => false,
        ],
    ], location: 'sidebar')]
    public ?Image $logo = null;

    /** @var string[] */
    #[Type\Field(type: ContentSelectionsType::class, options: [
        'label' => 'Content selection',
        'priority' => 480,
        'attr' => [
            'style' => 'editor',
            'show_headings' => 'false',
            'state' => 'show',
        ],
    ], location: 'editor')]
    public array $contentSelection = [];

    /** @var string[] */
    #[Type\Field(type: SocialsType::class, options: [
        'label' => 'Socials',
        'priority' => 470,
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    public array $socials;

    public function __toString()
    {
        return $this->title;
    }
}
