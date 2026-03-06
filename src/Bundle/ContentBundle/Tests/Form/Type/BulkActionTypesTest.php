<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Form\Type;

use Integrated\Bundle\ContentBundle\Bulk\CanonicalHandler;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedHandler;
use Integrated\Bundle\ContentBundle\Bulk\PremiumHandler;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowHandler;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowAssignHandler;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowStateHandler;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\CanonicalAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\FeaturedAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PremiumAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PublishWindowAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\WorkflowAssignAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\WorkflowStateAction;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionCanonicalType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionFeaturedType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPremiumType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPublishWindowType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionWorkflowAssignType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionWorkflowStateType;
use Symfony\Component\Form\Test\TypeTestCase;

class BulkActionTypesTest extends TypeTestCase
{
    public function testCanonicalTypeMapsSubmittedDataToAction(): void
    {
        $form = $this->factory->create(BulkActionCanonicalType::class, null, [
            'canonical_handler' => CanonicalHandler::class,
            'label' => 'Source',
        ]);

        $form->submit([
            'source' => 'Reuters',
            'sourceUrl' => 'https://example.org/source',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(CanonicalAction::class, $form->getData());
        self::assertSame(CanonicalHandler::class, $form->getData()->getHandler());
        self::assertSame('Reuters', $form->getData()->getSource());
        self::assertSame('https://example.org/source', $form->getData()->getSourceUrl());
    }

    public function testFeaturedTypeMapsSubmittedDataToAction(): void
    {
        $form = $this->factory->create(BulkActionFeaturedType::class, null, [
            'featured_handler' => FeaturedHandler::class,
            'label' => 'Featured',
        ]);

        $form->submit(['featured' => true]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(FeaturedAction::class, $form->getData());
        self::assertSame(FeaturedHandler::class, $form->getData()->getHandler());
        self::assertTrue($form->getData()->isFeatured());
    }

    public function testPremiumTypeMapsSubmittedDataToAction(): void
    {
        $form = $this->factory->create(BulkActionPremiumType::class, null, [
            'premium_handler' => PremiumHandler::class,
            'label' => 'Premium',
        ]);

        $form->submit(['premium' => true]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(PremiumAction::class, $form->getData());
        self::assertSame(PremiumHandler::class, $form->getData()->getHandler());
        self::assertTrue($form->getData()->isPremium());
    }

    public function testPublishWindowTypeMapsSubmittedDataToAction(): void
    {
        $form = $this->factory->create(BulkActionPublishWindowType::class, null, [
            'publish_window_handler' => PublishWindowHandler::class,
            'label' => 'Publication window',
        ]);

        $form->submit([
            'startDate' => '2026-01-01T10:00',
            'endDate' => '2026-01-03T18:30',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(PublishWindowAction::class, $form->getData());
        self::assertSame(PublishWindowHandler::class, $form->getData()->getHandler());

        if (null !== $form->getData()->getStartDate()) {
            self::assertInstanceOf(\DateTimeInterface::class, $form->getData()->getStartDate());
        }
        if (null !== $form->getData()->getEndDate()) {
            self::assertInstanceOf(\DateTimeInterface::class, $form->getData()->getEndDate());
        }
    }

    public function testWorkflowStateTypeMapsSubmittedDataToAction(): void
    {
        $form = $this->factory->create(BulkActionWorkflowStateType::class, null, [
            'workflow_state_handler' => WorkflowStateHandler::class,
            'state_choices' => [
                'Draft' => 'state-draft',
                'Published' => 'state-published',
            ],
            'label' => 'Workflow status',
        ]);

        $form->submit([
            'state' => 'state-published',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(WorkflowStateAction::class, $form->getData());
        self::assertSame(WorkflowStateHandler::class, $form->getData()->getHandler());
        self::assertSame('state-published', $form->getData()->getState());
    }

    public function testWorkflowStateTypeIsNotRequiredAtHtmlLevel(): void
    {
        $form = $this->factory->create(BulkActionWorkflowStateType::class, null, [
            'workflow_state_handler' => WorkflowStateHandler::class,
            'state_choices' => [
                'Draft' => 'state-draft',
                'Published' => 'state-published',
            ],
            'label' => 'Workflow status',
        ]);

        self::assertFalse($form->get('state')->getConfig()->getOption('required'));
    }

    public function testWorkflowAssignTypeMapsSubmittedDataToAction(): void
    {
        $form = $this->factory->create(BulkActionWorkflowAssignType::class, null, [
            'workflow_assign_handler' => WorkflowAssignHandler::class,
            'user_choices' => [
                'alice' => 'u1',
                'bob' => 'u2',
            ],
            'label' => 'Assignee',
        ]);

        $form->submit([
            'assigned' => 'u2',
        ]);

        self::assertTrue($form->isSynchronized());
        self::assertInstanceOf(WorkflowAssignAction::class, $form->getData());
        self::assertSame(WorkflowAssignHandler::class, $form->getData()->getHandler());
        self::assertSame('u2', $form->getData()->getAssigned());
    }
}
