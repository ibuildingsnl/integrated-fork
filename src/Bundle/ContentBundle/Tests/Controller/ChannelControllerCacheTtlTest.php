<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\ChannelController;
use Integrated\Bundle\ContentBundle\Services\SearchContentReferenced;
use Integrated\Bundle\UserBundle\Model\UserInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class ChannelControllerCacheTtlTest extends TestCase
{
    public function testChannelsFragmentCacheTtlMatchesSignedBridgeWindow(): void
    {
        $reflection = new \ReflectionClass(ChannelController::class);

        self::assertSame(300, $reflection->getConstant('CHANNELS_CACHE_TTL_SECONDS'));
    }

    public function testChannelsFragmentCacheKeyIncludesCurrentSessionId(): void
    {
        $request = Request::create('/admin/brand/example/edit');
        $request->setSession($this->createSessionStub('session-abc'));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $controller = new ChannelController(
            $this->createMock(\Doctrine\ODM\MongoDB\DocumentManager::class),
            $this->createMock(SearchContentReferenced::class),
            $this->createMock(EventDispatcherInterface::class),
            $requestStack,
        );

        $method = new \ReflectionMethod($controller, 'buildChannelsCacheKey');
        $method->setAccessible(true);
        $cacheKey = $method->invoke($controller, $this->createUserStub('user-123'));

        self::assertSame('channels_'.md5('user-123:session-abc'), $cacheKey);
    }

    private function createUserStub(string $id): UserInterface
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getId')->willReturn($id);

        return $user;
    }

    private function createSessionStub(string $id): \Symfony\Component\HttpFoundation\Session\SessionInterface
    {
        return new class($id) implements \Symfony\Component\HttpFoundation\Session\SessionInterface {
            public function __construct(private readonly string $id)
            {
            }

            public function start(): bool { return true; }
            public function isStarted(): bool { return true; }
            public function has(string $name): bool { return false; }
            public function get(string $name, mixed $default = null): mixed { return $default; }
            public function set(string $name, mixed $value): void {}
            public function all(): array { return []; }
            public function replace(array $attributes): void {}
            public function remove(string $name): mixed { return null; }
            public function clear(): void {}
            public function invalidate(?int $lifetime = null): bool { return true; }
            public function migrate(bool $destroy = false, ?int $lifetime = null): bool { return true; }
            public function save(): void {}
            public function getId(): string { return $this->id; }
            public function setId(string $id): void {}
            public function getName(): string { return 'PHPSESSID'; }
            public function setName(string $name): void {}
            public function getMetadataBag(): \Symfony\Component\HttpFoundation\Session\Storage\MetadataBag { throw new \BadMethodCallException(); }
            public function registerBag(\Symfony\Component\HttpFoundation\Session\SessionBagInterface $bag): void {}
            public function getBag(string $name): \Symfony\Component\HttpFoundation\Session\SessionBagInterface { throw new \BadMethodCallException(); }
        };
    }
}
