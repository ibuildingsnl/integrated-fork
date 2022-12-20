<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Security\ContentTypeVoter;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Integrated\Bundle\TaxonomyBundle\EventListener\ConfigureMenuSubscriber;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Security\Permission;
use Integrated\Common\Security\PermissionInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Strategy\UnanimousStrategy;

final class MenuTest extends TestCase
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private TokenStorageInterface $tokenStorage;
    /** @var ObjectRepository&MockObject */
    private ObjectRepository $repository;
    private ItemInterface $menu;
    private ConfigureMenuSubscriber $menuSubscriber;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ObjectRepository::class);
        $this->tokenStorage = new TokenStorage();
        $this->authorizationChecker = new AuthorizationChecker(
            $this->tokenStorage,
            new AccessDecisionManager(
                [new ContentTypeVoter($this->repository)],
                new UnanimousStrategy(),
            ),
        );
        $this->menuSubscriber = new ConfigureMenuSubscriber($this->authorizationChecker, $this->repository);
        $this->menu = new MenuItem('integrated_menu', new MenuFactory());
    }

    public function testShowingTaxonomyOptionToAdmins()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('no-access', 'ROLE_ADMIN'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        $section = $this->menu->getChild('Taxonomy');
        $item = $section->getChild('Taxonomies');

        self::assertInstanceOf(MenuItem::class, $section);
        self::assertInstanceOf(MenuItem::class, $item);
    }

    public function testHidingTaxonomyOptionWhenThereIsNoSuchContentType()
    {
        $this->tokenStorage->setToken($this->user('no-access', 'ROLE_ADMIN'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        self::assertNull($this->menu->getChild('Taxonomy'));
        self::assertEmpty($this->menu->getChildren());
    }

    public function testHidingTaxonomyOptionWhenTheUserHasNoAccess()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('no-access'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        self::assertNull($this->menu->getChild('Taxonomy'));
        self::assertEmpty($this->menu->getChildren());
    }

    public function testShowingTaxonomyOptionWhenTheUserHasAccess()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('taxonomy-access'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        $section = $this->menu->getChild('Taxonomy');
        $item = $section->getChild('Taxonomies');

        self::assertInstanceOf(MenuItem::class, $section);
        self::assertInstanceOf(MenuItem::class, $item);
    }

    public function testNotAddingTheMenuSectionIfItAlreadyExists()
    {
        $this->withTaxonomyContentType();
        $this->menu->addChild('Taxonomy');
        $this->tokenStorage->setToken($this->user('taxonomy-access'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        self::assertCount(1, $this->menu->getChildren());
    }

    public function testNotAddingTaxonomySectionsToUnrelatedMenus()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('taxonomy-access'));
        $unrelatedMenu = new MenuItem('dedicated_menu', new MenuFactory());

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $unrelatedMenu));

        self::assertEmpty($unrelatedMenu->getChildren());
    }

    private function withTaxonomyContentType(): void
    {
        $taxonomy = new ContentType();
        $taxonomy->setId('taxonomy');
        $permission = new Permission();
        $permission->setMask(PermissionInterface::READ);
        $permission->setGroup('taxonomy-access');
        $taxonomy->addPermission($permission);
        $this->repository->method('find')->willReturn($taxonomy);
    }

    private function user(string $group, string ...$roles): TokenInterface
    {
        $user = new User();
        $user->addGroup(new Group($group));
        foreach ($roles as $role) {
            $user->addRole(new Role($role));
        }

        return new PreAuthenticatedToken($user, 'main', ['foo']);
    }
}
