<?php

namespace Integrated\Bundle\ContentBundle\Tests\EventListener;

use Doctrine\ODM\MongoDB\DocumentManager;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\ContentBundle\Document\Content\Publication;
use Integrated\Bundle\ContentBundle\Document\Content\PublicationRepositoryInterface;
use Integrated\Bundle\ContentBundle\EventListener\ContentPublicationIntegrationListener;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class ContentPublicationIntegrationListenerTest extends TestCase
{
    private ContentPublicationIntegrationListener $listener;

    protected function setUp(): void
    {
        $this->listener = new ContentPublicationIntegrationListener(
            $this->createMock(PublicationRepositoryInterface::class),
            $this->createMock(DocumentManager::class),
        );
    }

    public function testIsPublicationChangedIgnoresEquivalentSettingsWithDifferentKeyOrder(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');
        $time = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00'));
        $publication = new Publication($content, $channel, $time, [
            'caption' => 'A short intro',
            'meta' => [
                'title' => 'Demo',
                'description' => 'Same values, different order',
            ],
        ]);

        $changed = $this->invokeIsPublicationChanged($publication, [
            'time' => (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00')),
            'settings' => [
                'meta' => [
                    'description' => 'Same values, different order',
                    'title' => 'Demo',
                ],
                'caption' => 'A short intro',
            ],
        ]);

        self::assertFalse($changed);
    }

    public function testIsPublicationChangedDetectsNestedSettingsDifferences(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');
        $time = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00'));
        $publication = new Publication($content, $channel, $time, [
            'meta' => [
                'title' => 'Original title',
            ],
        ]);

        $changed = $this->invokeIsPublicationChanged($publication, [
            'time' => (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00')),
            'settings' => [
                'meta' => [
                    'title' => 'Updated title',
                ],
            ],
        ]);

        self::assertTrue($changed);
    }

    public function testIsPublicationChangedIgnoresEquivalentPublishTimeAcrossTimeZones(): void
    {
        $content = new Article();
        $channel = $this->createChannel('vismagazine');
        $utcTime = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27T10:00:00+00:00'));
        $publication = new Publication($content, $channel, $utcTime, []);

        $cetTime = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27T11:00:00+01:00'));
        $changed = $this->invokeIsPublicationChanged($publication, [
            'time' => $cetTime,
            'settings' => [],
        ]);

        self::assertFalse($changed);
    }

    public function testBuildFormDoesNotQueryPublicationRepositoryForUnsavedContent(): void
    {
        $repository = $this->createMock(PublicationRepositoryInterface::class);
        $repository
            ->expects(self::never())
            ->method('forContentByChannel');
        $repository
            ->expects(self::never())
            ->method('forContent');
        $repository
            ->expects(self::never())
            ->method('forContentOnChannel');

        $listener = new ContentPublicationIntegrationListener(
            $repository,
            $this->createMock(DocumentManager::class),
        );

        $content = new Article();
        $channel = $this->createChannel('vismagazine');

        $channelsBuilder = $this->createMock(FormBuilderInterface::class);
        $channelsBuilder
            ->method('getOption')
            ->with('choices')
            ->willReturn([$channel]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->method('getData')
            ->willReturn($content);
        $builder
            ->method('has')
            ->with('channels')
            ->willReturn(true);
        $builder
            ->method('get')
            ->with('channels')
            ->willReturn($channelsBuilder);
        $builder
            ->method('add')
            ->willReturnSelf();

        $postSubmitListener = null;
        $builder
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $listener) use (&$postSubmitListener, $builder): FormBuilderInterface {
                $postSubmitListener = $listener;

                return $builder;
            });

        $event = new BuilderEvent(
            $this->createMock(ContentTypeInterface::class),
            $this->createMock(MetadataInterface::class),
            $builder,
            [],
        );

        $listener->buildForm($event);

        self::assertIsCallable($postSubmitListener);

        $form = $this->createMock(FormInterface::class);
        $publicationsForm = $this->createMock(FormInterface::class);
        $form
            ->method('get')
            ->with('publications')
            ->willReturn($publicationsForm);

        $postSubmitListener(new FormEvent($form, $content));

        self::addToAssertionCount(1);
    }

    public function testPostSubmitRemovesAllExistingPublicationsForUnpublishedContent(): void
    {
        $content = (new Article())
            ->setId('content-id')
            ->setDisabled(true);

        $channel = $this->createChannel('vismagazine');
        $time = (new PublishTime())->setStartDate(new \DateTimeImmutable('2026-02-27 10:00:00'));

        $successPublication = new Publication($content, $channel, $time, []);
        $successPublication->setStatus(Publication::STATUS_SUCCESS);

        $failedPublication = new Publication($content, $channel, $time, []);
        $failedPublication->setStatus(Publication::STATUS_FAILED);

        $repository = $this->createMock(PublicationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('forContentByChannel')
            ->with($content)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('forContent')
            ->with($content)
            ->willReturn([$successPublication, $failedPublication]);
        $repository
            ->expects(self::exactly(2))
            ->method('remove')
            ->with(self::isInstanceOf(Publication::class));
        $repository
            ->expects(self::never())
            ->method('forContentOnChannel');
        $repository
            ->expects(self::never())
            ->method('add');

        $listener = new ContentPublicationIntegrationListener(
            $repository,
            $this->createMock(DocumentManager::class),
        );

        $channelsBuilder = $this->createMock(FormBuilderInterface::class);
        $channelsBuilder
            ->method('getOption')
            ->with('choices')
            ->willReturn([$channel]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->method('getData')
            ->willReturn($content);
        $builder
            ->method('has')
            ->with('channels')
            ->willReturn(true);
        $builder
            ->method('get')
            ->with('channels')
            ->willReturn($channelsBuilder);
        $builder
            ->method('add')
            ->willReturnSelf();

        $postSubmitListener = null;
        $builder
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $listener) use (&$postSubmitListener, $builder): FormBuilderInterface {
                $postSubmitListener = $listener;

                return $builder;
            });

        $event = new BuilderEvent(
            $this->createMock(ContentTypeInterface::class),
            $this->createMock(MetadataInterface::class),
            $builder,
            [],
        );

        $listener->buildForm($event);
        self::assertIsCallable($postSubmitListener);

        $form = $this->createMock(FormInterface::class);
        $postSubmitListener(new FormEvent($form, $content));
    }

    public function testPostSubmitAllowsPublicationCreationWhenSubmittedWorkflowStateIsPublishable(): void
    {
        $content = (new Article())
            ->setId('content-id')
            ->setDisabled(true);

        $channel = $this->createChannel('vismagazine');
        $content->addChannel($channel);

        $repository = $this->createMock(PublicationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('forContentByChannel')
            ->with($content)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('forContent')
            ->with($content)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('forContentOnChannel')
            ->with($content, $channel)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('add')
            ->with(self::isInstanceOf(Publication::class));
        $repository
            ->expects(self::never())
            ->method('remove');

        $listener = new ContentPublicationIntegrationListener(
            $repository,
            $this->createMock(DocumentManager::class),
        );

        $channelsBuilder = $this->createMock(FormBuilderInterface::class);
        $channelsBuilder
            ->method('getOption')
            ->with('choices')
            ->willReturn([$channel]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->method('getData')
            ->willReturn($content);
        $builder
            ->method('has')
            ->with('channels')
            ->willReturn(true);
        $builder
            ->method('get')
            ->with('channels')
            ->willReturn($channelsBuilder);
        $builder
            ->method('add')
            ->willReturnSelf();

        $postSubmitListener = null;
        $builder
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $listener) use (&$postSubmitListener, $builder): FormBuilderInterface {
                $postSubmitListener = $listener;

                return $builder;
            });

        $event = new BuilderEvent(
            $this->createMock(ContentTypeInterface::class),
            $this->createMock(MetadataInterface::class),
            $builder,
            [],
        );

        $listener->buildForm($event);
        self::assertIsCallable($postSubmitListener);

        $workflowState = new class {
            public function isPublishable(): bool
            {
                return true;
            }
        };

        $settingsForm = $this->createMock(FormInterface::class);
        $settingsForm
            ->method('getData')
            ->willReturn([]);

        $channelForm = $this->createMock(FormInterface::class);
        $channelForm
            ->method('get')
            ->with('settings')
            ->willReturn($settingsForm);

        $publicationsForm = $this->createMock(FormInterface::class);
        $publicationsForm
            ->method('get')
            ->with('vismagazine')
            ->willReturn($channelForm);

        $extensionWorkflowForm = $this->createMock(FormInterface::class);
        $extensionWorkflowForm
            ->method('getData')
            ->willReturn(['state' => $workflowState]);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('has')
            ->with('extension_workflow')
            ->willReturn(true);
        $form
            ->method('get')
            ->willReturnMap([
                ['extension_workflow', $extensionWorkflowForm],
                ['publications', $publicationsForm],
            ]);

        $postSubmitListener(new FormEvent($form, $content));
    }

    public function testPostSubmitUsesWorkflowStateFieldWhenWorkflowDataIsNotArray(): void
    {
        $content = (new Article())
            ->setId('content-id')
            ->setDisabled(true);

        $channel = $this->createChannel('vismagazine');
        $content->addChannel($channel);

        $repository = $this->createMock(PublicationRepositoryInterface::class);
        $repository
            ->expects(self::once())
            ->method('forContentByChannel')
            ->with($content)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('forContent')
            ->with($content)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('forContentOnChannel')
            ->with($content, $channel)
            ->willReturn([]);
        $repository
            ->expects(self::once())
            ->method('add')
            ->with(self::isInstanceOf(Publication::class));
        $repository
            ->expects(self::never())
            ->method('remove');

        $listener = new ContentPublicationIntegrationListener(
            $repository,
            $this->createMock(DocumentManager::class),
        );

        $channelsBuilder = $this->createMock(FormBuilderInterface::class);
        $channelsBuilder
            ->method('getOption')
            ->with('choices')
            ->willReturn([$channel]);

        $builder = $this->createMock(FormBuilderInterface::class);
        $builder
            ->method('getData')
            ->willReturn($content);
        $builder
            ->method('has')
            ->with('channels')
            ->willReturn(true);
        $builder
            ->method('get')
            ->with('channels')
            ->willReturn($channelsBuilder);
        $builder
            ->method('add')
            ->willReturnSelf();

        $postSubmitListener = null;
        $builder
            ->method('addEventListener')
            ->willReturnCallback(function (string $eventName, callable $listener) use (&$postSubmitListener, $builder): FormBuilderInterface {
                $postSubmitListener = $listener;

                return $builder;
            });

        $event = new BuilderEvent(
            $this->createMock(ContentTypeInterface::class),
            $this->createMock(MetadataInterface::class),
            $builder,
            [],
        );

        $listener->buildForm($event);
        self::assertIsCallable($postSubmitListener);

        $workflowState = new class {
            public function isPublishable(): bool
            {
                return true;
            }
        };

        $workflowStateField = $this->createMock(FormInterface::class);
        $workflowStateField
            ->method('getData')
            ->willReturn($workflowState);

        $extensionWorkflowForm = $this->createMock(FormInterface::class);
        $extensionWorkflowForm
            ->method('has')
            ->with('state')
            ->willReturn(true);
        $extensionWorkflowForm
            ->method('get')
            ->with('state')
            ->willReturn($workflowStateField);
        $extensionWorkflowForm
            ->method('getData')
            ->willReturn(new \stdClass());

        $settingsForm = $this->createMock(FormInterface::class);
        $settingsForm
            ->method('getData')
            ->willReturn([]);

        $channelForm = $this->createMock(FormInterface::class);
        $channelForm
            ->method('get')
            ->with('settings')
            ->willReturn($settingsForm);

        $publicationsForm = $this->createMock(FormInterface::class);
        $publicationsForm
            ->method('get')
            ->with('vismagazine')
            ->willReturn($channelForm);

        $form = $this->createMock(FormInterface::class);
        $form
            ->method('has')
            ->with('extension_workflow')
            ->willReturn(true);
        $form
            ->method('get')
            ->willReturnMap([
                ['extension_workflow', $extensionWorkflowForm],
                ['publications', $publicationsForm],
            ]);

        $postSubmitListener(new FormEvent($form, $content));
    }

    /**
     * @param array{time?: mixed, settings?: mixed} $data
     */
    private function invokeIsPublicationChanged(Publication $publication, array $data): bool
    {
        $method = new \ReflectionMethod(ContentPublicationIntegrationListener::class, 'isPublicationChanged');
        $method->setAccessible(true);

        return $method->invoke($this->listener, $publication, $data);
    }

    private function createChannel(?string $id): ChannelInterface
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel
            ->method('getId')
            ->willReturn($id);

        return $channel;
    }
}
