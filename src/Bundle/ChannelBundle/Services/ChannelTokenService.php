<?php

namespace Integrated\Bundle\ChannelBundle\Services;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\LockException;
use Doctrine\ODM\MongoDB\Mapping\MappingException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Integrated\Bundle\ChannelBundle\Document\ChannelToken;

class ChannelTokenService
{
    public function __construct(
        private readonly DocumentManager $dm,
    ) {
    }

    /**
     * @throws MongoDBException
     */
    public function createOrUpdateFor(string $channelId, string $token, \DateTimeImmutable $expiresAt): ChannelToken
    {
        $channelToken = new ChannelToken();
        $channelToken->setId($channelId);
        $channelToken->setToken($token);
        $channelToken->setExpiresAt($expiresAt);

        $this->dm->persist($channelToken);
        $this->dm->flush();

        return $channelToken;
    }

    /**
     * @throws MappingException
     * @throws LockException
     */
    public function getChannelTokenFor(string $channelId): ?ChannelToken
    {
        $repo = $this->dm->getRepository(ChannelToken::class);

        return $repo->find($channelId);
    }

    /**
     * @throws MappingException
     * @throws LockException
     * @throws MongoDBException
     */
    public function deleteTokenFor(string $channelTokenId): void
    {
        $repo = $this->dm->getRepository(ChannelToken::class);

        $channelToken = $repo->find($channelTokenId);

        if ($channelToken) {
            $this->dm->remove($channelToken);
            $this->dm->flush();
        }
    }
}
