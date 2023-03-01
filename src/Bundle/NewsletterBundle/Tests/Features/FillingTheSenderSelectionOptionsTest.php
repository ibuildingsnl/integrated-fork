<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Form\SenderChoiceType;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\LocalSenderOptionsFetcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\ResolvedFormTypeFactory;

final class FillingTheSenderSelectionOptionsTest extends TestCase
{
    private FormBuilderInterface $builder;
    private FormBuilderInterface $builder2;
    private ?LocalSenderOptionsFetcher $senders;

    protected function setUp(): void
    {
        $this->senders = new LocalSenderOptionsFetcher();
        $this->builder = new FormBuilder(
            'form',
            Newsletter::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([new PreloadedExtension([
                SenderChoiceType::class => new SenderChoiceType($this->senders),
            ], [])], new ResolvedFormTypeFactory()))
        );
        $this->builder->add('sender', SenderChoiceType::class);
        $this->builder2 = new FormBuilder(
            'form',
            Newsletter::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()))
        );
        $this->builder2->add('sender', SenderChoiceType::class);
    }

    public function testRecipientSelectionIsEmptyByDefault()
    {
        self::assertEquals([], $this->recipientListOptions());
    }

    public function testShowingOneAvailableRecipientList()
    {
        $this->addSender('123', 'test@foo.bar');

        self::assertEquals(['test@foo.bar' => '123'], $this->recipientListOptions());
    }

    public function testShowingThreeAvailableRecipientLists()
    {
        $this->addSender('123', 'test1@foo.bar');
        $this->addSender('456', 'test2@foo.bar');
        $this->addSender('789', 'test3@foo.bar');

        self::assertEquals([
            'test1@foo.bar' => '123',
            'test2@foo.bar' => '456',
            'test3@foo.bar' => '789',
        ], $this->recipientListOptions());
    }

    public function testShowingWarningWhenNotConfigured()
    {
        self::assertEquals(
            ['No newsletter platform connected' => ''],
            $this->builder2->get('sender')->getOption('choices', [])
        );
    }

    private function recipientListOptions(): array
    {
        return $this->builder->get('sender')->getOption('choices', []);
    }

    private function addSender(string $id, string $email): void
    {
        $this->senders->add($id, $email);
    }
}
