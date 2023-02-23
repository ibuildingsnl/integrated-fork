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
    #[Type\Field(options: ['attr' => ['style' => 'editor', 'state' => 'show']], location: 'editor')]
    public string $title;

    #[Type\Field(options: ['attr' => ['style' => 'editor', 'state' => 'show']], location: 'editor')]
    public string $headline;

    /** @var string[] */
    #[Type\Field(type: SocialsType::class, options: [
        'attr' => [
            'style' => 'editor',
            'state' => 'show',
        ],
    ], location: 'editor')]
    public array $socials;

    #[Type\Field(type: IntegerType::class, options: [
        'label' => 'Generation time',
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'clock-outline',
            'show_headings' => 'false',
            'help_text' => 'Email is generated the amount of time given before sending.',
        ],
    ], location: 'sidebar')]
    public int $hoursBefore;

    /** @var string[] */
    #[Type\Field(type: ContentSelectionsType::class, options: [
        'attr' => [
            'style' => 'editor',
            'show_headings' => 'false',
            'state' => 'show',
        ],
    ], location: 'editor')]
    public array $contentSelection = [];

    #[Type\Field(type: TestEmailAddressesType::class, options: [
        'attr' => [
            'style' => 'sidebar',
            'icon' => 'group',
            'show_headings' => 'false',
        ],
    ], location: 'sidebar')]
    public array $testAddresses = [];

    #[Type\Field(type: RecurringScheduleEntryType::class, options: [
        'label' => 'Sending Schedule',
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

    public function isInGenerationWindow(\DateTimeImmutable $now): bool
    {
        $nextSend = $this->schedule->firstAfter($now);
        $prepare = $nextSend->modify(sprintf('-%d hours', $this->hoursBefore));
        return $now >= $prepare;
    }

    public function __toString()
    {
        return $this->title;
    }
}
