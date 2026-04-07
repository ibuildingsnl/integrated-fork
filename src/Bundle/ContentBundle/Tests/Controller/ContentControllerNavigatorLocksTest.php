<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Controller;

use Integrated\Bundle\ContentBundle\Controller\ContentController;
use Integrated\Bundle\UserBundle\Model\UserManagerInterface;
use Integrated\Common\Locks\Filter;
use Integrated\Common\Locks\LockInterface;
use Integrated\Common\Locks\Provider\DBAL\Manager;
use Integrated\Common\Locks\Request;
use Integrated\Common\Locks\Resource;
use PHPUnit\Framework\TestCase;

final class ContentControllerNavigatorLocksTest extends TestCase
{
    public function testGetLocksAcceptsArrayAccessPaginatorItems(): void
    {
        $controller = (new \ReflectionClass(ContentController::class))->newInstanceWithoutConstructor();

        $lockManager = $this->createMock(Manager::class);
        $lockManager
            ->expects(self::once())
            ->method('findBy')
            ->with(self::callback(function (Filter $filter): bool {
                $resources = \is_array($filter->resources) ? $filter->resources : [$filter->resources];

                foreach ($resources as $resource) {
                    if ($resource instanceof Resource
                        && $resource->getType() === 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\News'
                        && $resource->getIdentifier() === 'news-1'
                    ) {
                        return true;
                    }
                }

                return false;
            }))
            ->willReturn([$this->createActiveLock('Integrated\\Bundle\\ContentBundle\\Document\\Content\\News', 'news-1')]);

        $userManager = $this->createMock(UserManagerInterface::class);

        $lockManagerProperty = new \ReflectionProperty(ContentController::class, 'lockManager');
        $lockManagerProperty->setAccessible(true);
        $lockManagerProperty->setValue($controller, $lockManager);

        $userManagerProperty = new \ReflectionProperty(ContentController::class, 'userManager');
        $userManagerProperty->setAccessible(true);
        $userManagerProperty->setValue($controller, $userManager);

        $method = new \ReflectionMethod(ContentController::class, 'getLocks');
        $method->setAccessible(true);

        $items = new \ArrayIterator([
            new \ArrayObject([
                'type_class' => 'Integrated\\Bundle\\ContentBundle\\Document\\Content\\News',
                'type_id' => 'news-1',
            ]),
        ]);

        /** @var array<string, array{lock: LockInterface|null, user: string}> $locks */
        $locks = $method->invoke($controller, $items);

        self::assertArrayHasKey('news-1', $locks);
        self::assertSame('', $locks['news-1']['user']);
    }

    private function createActiveLock(string $type, string $id): LockInterface
    {
        $request = new Request(new Resource($type, $id));

        $lock = $this->createMock(LockInterface::class);
        $lock->method('getRequest')->willReturn($request);
        $lock->method('getCreated')->willReturn(new \DateTime('-1 minute'));
        $lock->method('getExpires')->willReturn(new \DateTime('+5 minutes'));

        return $lock;
    }
}
