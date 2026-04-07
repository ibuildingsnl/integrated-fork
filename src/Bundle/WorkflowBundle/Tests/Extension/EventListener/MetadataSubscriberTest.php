<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Extension\EventListener;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\WorkflowBundle\Extension\EventListener\MetadataSubscriber;
use Integrated\Bundle\WorkflowBundle\Form\Type\DefinitionType;
use Integrated\Common\Content\Extension\Event\MetadataEvent;
use Integrated\Common\Content\Extension\ExtensionInterface;
use Integrated\Common\Form\Mapping\Metadata\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\NotBlank;

final class MetadataSubscriberTest extends TestCase
{
    public function testProcessAddsWorkflowOptionForRegularContentMetadata(): void
    {
        $subscriber = new MetadataSubscriber($this->createMock(ExtensionInterface::class));
        $metadata = new Document(WorkflowMetadataSubscriberTestContent::class);

        $subscriber->process(new MetadataEvent($metadata));

        self::assertTrue($metadata->hasOption('workflow'));
        self::assertSame(DefinitionType::class, $metadata->getOption('workflow')->getType());
    }

    public function testProcessSkipsWorkflowOptionForTaxonomyMetadata(): void
    {
        $subscriber = new MetadataSubscriber($this->createMock(ExtensionInterface::class));
        $metadata = new Document(Taxonomy::class);

        $subscriber->process(new MetadataEvent($metadata));

        self::assertFalse($metadata->hasOption('workflow'));
    }
}

final class WorkflowMetadataSubscriberTestContent
{
    #[NotBlank]
    public string $title = '';
}
