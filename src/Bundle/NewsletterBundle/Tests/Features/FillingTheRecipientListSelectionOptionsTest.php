<?php

namespace Integrated\Bundle\NewsletterBundle\Tests\Features;

use Integrated\Bundle\NewsletterBundle\Document\Newsletter;
use Integrated\Bundle\NewsletterBundle\Form\RecipientListChoiceType;
use Integrated\Bundle\NewsletterBundle\Tests\Features\Doubles\LocalRecipientListOptionsFetcher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\ResolvedFormTypeFactory;

final class FillingTheRecipientListSelectionOptionsTest extends TestCase
{
    private FormBuilderInterface $builder;
    private FormBuilderInterface $builder2;
    private ?LocalRecipientListOptionsFetcher $recipients;

    protected function setUp(): void
    {
        $this->recipients = new LocalRecipientListOptionsFetcher();
        $this->builder = new FormBuilder(
            'form',
            Newsletter::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([new PreloadedExtension([
                RecipientListChoiceType::class => new RecipientListChoiceType($this->recipients),
            ], [])], new ResolvedFormTypeFactory()))
        );
        $this->builder->add('recipientList', RecipientListChoiceType::class);
        $this->builder2 = new FormBuilder(
            'form',
            Newsletter::class,
            new EventDispatcher(),
            new FormFactory(new FormRegistry([], new ResolvedFormTypeFactory()))
        );
        $this->builder2->add('recipientList', RecipientListChoiceType::class);
    }

    public function testRecipientSelectionIsEmptyByDefault()
    {
        self::assertEquals([], $this->recipientListOptions());
    }

    public function testShowingOneAvailableRecipientList()
    {
        $this->addRecipientList('123', 'Test List');

        self::assertEquals(['Test List' => '123'], $this->recipientListOptions());
    }

    public function testShowingThreeAvailableRecipientLists()
    {
        $this->addRecipientList('123', 'List 1');
        $this->addRecipientList('456', 'List 2');
        $this->addRecipientList('789', 'List 3');

        self::assertEquals([
            'List 1' => '123',
            'List 2' => '456',
            'List 3' => '789',
        ], $this->recipientListOptions());
    }

    public function testShowingWarningWhenNotConfigured()
    {
        self::assertEquals(
            ['No newsletter platform connected' => ''],
            $this->builder2->get('recipientList')->getOption('choices', [])
        );
    }

    private function recipientListOptions(): array
    {
        return $this->builder->get('recipientList')->getOption('choices', []);
    }

    private function addRecipientList(string $id, string $name): void
    {
        $this->recipients->add($id, $name);
    }
}
