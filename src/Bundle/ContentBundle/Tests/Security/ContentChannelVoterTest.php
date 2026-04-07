<?php

namespace Integrated\Bundle\ContentBundle\Tests\Security;

use Integrated\Bundle\ContentBundle\Document\Channel\Channel;
use Integrated\Bundle\ContentBundle\Security\ContentChannelVoter;
use Integrated\Bundle\ContentBundle\Tests\Fixtures\ObjectWithChannels;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Security\PermissionInterface;
use Integrated\Common\Security\Permissions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ContentChannelVoterTest extends TestCase
{
    public function testViewReusesCachedReadDecisionForSameChannelAcrossContents(): void
    {
        $decisionManager = $this->createMock(AccessDecisionManagerInterface::class);
        $decisionManager
            ->expects(self::once())
            ->method('decide')
            ->with(
                self::isInstanceOf(PreAuthenticatedToken::class),
                [PermissionInterface::READ],
                self::isInstanceOf(Channel::class),
            )
            ->willReturn(true);

        $voter = new ContentChannelVoter($decisionManager);
        $token = new PreAuthenticatedToken(new User(), 'main', ['foo']);

        $channel = new Channel();
        $channel->setId('news');

        $contentA = new ObjectWithChannels([$channel]);
        $contentB = new ObjectWithChannels([$channel]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $contentA, [Permissions::VIEW]));
        self::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $contentB, [Permissions::VIEW]));
    }
}
