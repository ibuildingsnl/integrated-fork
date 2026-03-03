<?php

namespace Integrated\Bundle\UserBundle\Tests\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Integrated\Bundle\UserBundle\Doctrine\RoleManager;
use Integrated\Bundle\UserBundle\Event\ConfigureRolesEvent;
use Integrated\Bundle\UserBundle\Model\Role;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RoleManagerTest extends TestCase
{
    public function testGetRolesFromSourcesKeepsConfiguredRoleLabelsWhenRoleAlreadyExists(): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('getClassName')->willReturn(Role::class);
        $repository->method('findAll')->willReturn([
            new Role('ROLE_ADMIN', 'Database label'),
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(Role::class)->willReturn($repository);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->willReturnCallback(static fn (ConfigureRolesEvent $event): ConfigureRolesEvent => $event);

        $manager = new RoleManager($entityManager, $eventDispatcher, Role::class, [
            'ROLE_ADMIN' => 'Configured label',
        ]);

        $roles = $manager->getRolesFromSources();

        self::assertSame('Configured label', $roles['ROLE_ADMIN'] ?? null);
    }
}

