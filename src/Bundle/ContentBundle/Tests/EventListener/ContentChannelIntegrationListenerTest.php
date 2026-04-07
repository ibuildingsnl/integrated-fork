<?php

namespace Integrated\Bundle\ContentBundle\Tests\EventListener;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\EventListener\ContentChannelIntegrationListener;
use Integrated\Common\Content\Channel\ChannelInterface;
use Integrated\Common\Content\Form\Event\BuilderEvent;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\Form\Mapping\MetadataInterface;
use Integrated\Common\Security\PermissionInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContentChannelIntegrationListenerTest extends TestCase
{
    public function testGetChannelsLoadsAllChannelsWhenIdsAreOmitted(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $expected = [$channel];

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expected);
        $repository
            ->expects($this->never())
            ->method('findBy');

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame($expected, $listener->exposedGetChannels());
    }

    public function testGetChannelsReturnsEmptyListWithoutQueryForEmptyIds(): void
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->never())
            ->method('findAll');
        $repository
            ->expects($this->never())
            ->method('findBy');

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame([], $listener->exposedGetChannels([]));
    }

    public function testGetChannelsBuildsOrCriteriaForSpecificIds(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $expected = [$channel];

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->never())
            ->method('findAll');
        $repository
            ->expects($this->once())
            ->method('findBy')
            ->with(['$or' => [['id' => 'a'], ['id' => 'b']]])
            ->willReturn($expected);

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame($expected, $listener->exposedGetChannels(['a', 'b']));
    }

    public function testBuildFormReusesPrecomputedChoiceAttributesWithoutExtraAuthorizationChecks(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $channel->method('getId')->willReturn('main');
        $channel->method('getName')->willReturn('Main');

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->expects(self::exactly(2))
            ->method('isGranted')
            ->willReturnCallback(static function (mixed $attribute, mixed $subject) use ($channel): bool {
                self::assertSame($channel, $subject);
                self::assertContains($attribute, [PermissionInterface::READ, PermissionInterface::WRITE]);

                return true;
            });

        $listener = new TestableContentChannelIntegrationListener(
            $this->createMock(ObjectRepository::class),
            $authorizationChecker,
            [$channel],
        );

        $capturedOptions = null;
        $builder = $this->createMock(FormBuilderInterface::class);
        $builder->method('addEventSubscriber')->willReturnSelf();
        $builder
            ->expects(self::exactly(2))
            ->method('add')
            ->willReturnCallback(function (string $name, string $type, array $options = []) use (&$capturedOptions, $builder): FormBuilderInterface {
                if ($name === 'channels') {
                    self::assertSame(ChoiceType::class, $type);
                    $capturedOptions = $options;
                }

                return $builder;
            });

        $contentType = $this->createMock(ContentTypeInterface::class);
        $contentType->method('getOption')->with('channels')->willReturn([
            'disabled' => 0,
            'defaults' => [],
        ]);

        $listener->buildForm(new BuilderEvent(
            $contentType,
            $this->createMock(MetadataInterface::class),
            $builder,
            [],
        ));

        self::assertIsArray($capturedOptions);
        self::assertSame([
            'data-channel-selector' => 'main',
            'data-channel-name' => 'Main',
        ], $capturedOptions['choice_attr']($channel));
    }

    public function testGetChannelsCachesRepositoryResultsWithinListenerInstance(): void
    {
        $channel = $this->createMock(ChannelInterface::class);
        $expected = [$channel];

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects(self::once())
            ->method('findAll')
            ->willReturn($expected);

        $listener = new TestableContentChannelIntegrationListener(
            $repository,
            $this->createMock(AuthorizationCheckerInterface::class)
        );

        self::assertSame($expected, $listener->exposedGetChannels());
        self::assertSame($expected, $listener->exposedGetChannels());
    }
}

final class TestableContentChannelIntegrationListener extends ContentChannelIntegrationListener
{
    /**
     * @param array<int, ChannelInterface>|null $channels
     */
    public function __construct(ObjectRepository $repository, AuthorizationCheckerInterface $authorizationChecker, private readonly ?array $channels = null)
    {
        parent::__construct($repository, $authorizationChecker);
    }

    /**
     * @param array<int, string>|null $ids
     *
     * @return array<int, ChannelInterface>
     */
    public function exposedGetChannels(?array $ids = null): array
    {
        return $this->getChannels($ids);
    }

    protected function getChannels(?array $ids = null): array
    {
        if ($ids === null && $this->channels !== null) {
            return $this->channels;
        }

        return parent::getChannels($ids);
    }
}
