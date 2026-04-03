<?php

declare(strict_types=1);

namespace Integrated\Bundle\WebsiteBundle\Tests\Service;

use Integrated\Bundle\BlockBundle\Templating\BlockManager;
use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Document\Content\Article;
use Integrated\Bundle\ContentBundle\Document\Content\Embedded\PublishTime;
use Integrated\Bundle\WebsiteBundle\EventListener\WebsiteToolbarListener;
use Integrated\Bundle\WebsiteBundle\Service\ContentService;
use Integrated\Common\Content\Channel\ChannelContextInterface;
use Integrated\Common\Security\Permissions;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

final class ContentServiceTest extends TestCase
{
    /** @var ChannelContextInterface&MockObject */
    private ChannelContextInterface $channelContext;
    /** @var BlockManager&MockObject */
    private BlockManager $blockManager;
    /** @var WebsiteToolbarListener&MockObject */
    private WebsiteToolbarListener $websiteToolbarListener;

    protected function setUp(): void
    {
        $this->channelContext = $this->createMock(ChannelContextInterface::class);
        $this->blockManager = $this->createMock(BlockManager::class);
        $this->websiteToolbarListener = $this->createMock(WebsiteToolbarListener::class);
    }

    public function testPrepareDoesNotExposeToolbarForScopeUserWithoutEditPermission(): void
    {
        $channel = new Channel();
        $content = $this->createPublishedArticle();
        $content->addChannel($channel);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($channel);

        $this->websiteToolbarListener
            ->expects($this->never())
            ->method('setContentItem');
        $this->websiteToolbarListener
            ->expects($this->never())
            ->method('setToolbarMessage');

        $this->blockManager
            ->expects($this->once())
            ->method('setDocument')
            ->with($content);

        $this->createService(static function (TokenInterface $token, array $attributes, mixed $subject = null) use ($content): bool {
            $attribute = $attributes[0] ?? null;

            if ($attribute === 'ROLE_SCOPE_INTEGRATED') {
                return true;
            }

            if ($attribute === Permissions::EDIT && $subject === $content) {
                return false;
            }

            return false;
        })->prepare($content);
    }

    public function testPrepareExposesToolbarForScopeUserWithEditPermission(): void
    {
        $channel = new Channel();
        $content = $this->createPublishedArticle();
        $content->addChannel($channel);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($channel);

        $this->websiteToolbarListener
            ->expects($this->once())
            ->method('setContentItem')
            ->with($content);
        $this->websiteToolbarListener
            ->expects($this->never())
            ->method('setToolbarMessage');

        $this->blockManager
            ->expects($this->once())
            ->method('setDocument')
            ->with($content);

        $this->createService(static function (TokenInterface $token, array $attributes, mixed $subject = null) use ($content): bool {
            $attribute = $attributes[0] ?? null;

            if ($attribute === 'ROLE_SCOPE_INTEGRATED') {
                return true;
            }

            if ($attribute === Permissions::EDIT && $subject === $content) {
                return true;
            }

            return false;
        })->prepare($content);
    }

    public function testPrepareRejectsContentOutsideCurrentChannel(): void
    {
        $currentChannel = new Channel();
        $otherChannel = new Channel();
        $content = $this->createPublishedArticle();
        $content->addChannel($otherChannel);

        $this->channelContext
            ->method('getChannel')
            ->willReturn($currentChannel);

        $this->blockManager
            ->expects($this->never())
            ->method('setDocument');
        $this->websiteToolbarListener
            ->expects($this->never())
            ->method('setContentItem');

        $this->expectException(NotFoundHttpException::class);

        $this->createService(static fn (TokenInterface $token, array $attributes, mixed $subject = null): bool => false)->prepare($content);
    }

    private function createService(callable $decisionCallback): ContentService
    {
        $tokenStorage = $this->createMock(TokenStorageInterface::class);
        $tokenStorage
            ->method('getToken')
            ->willReturn($this->createMock(TokenInterface::class));

        $accessDecisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $accessDecisionManager
            ->method('decide')
            ->willReturnCallback($decisionCallback);

        return new ContentService(
            $this->channelContext,
            new AuthorizationChecker($tokenStorage, $accessDecisionManager),
            $this->blockManager,
            $this->websiteToolbarListener,
        );
    }

    private function createPublishedArticle(): Article
    {
        $publishTime = (new PublishTime())
            ->setStartDate(new \DateTimeImmutable('-1 day'))
            ->setEndDate(new \DateTimeImmutable('+1 day'));

        $content = new Article();
        $content->setPublishTime($publishTime);

        return $content;
    }
}
