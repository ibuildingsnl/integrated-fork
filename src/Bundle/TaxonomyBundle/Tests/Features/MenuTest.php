<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\ContentBundle\Doctrine\ContentTypeManager;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Security\ContentTypeVoter;
use Integrated\Bundle\MenuBundle\Event\ConfigureMenuEvent;
use Integrated\Bundle\TaxonomyBundle\EventListener\ConfigureMenuSubscriber;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTypeResolver;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\ContentType\ResolverInterface;
use Integrated\Common\Security\Permission;
use Integrated\Common\Security\PermissionInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use Knp\Menu\MenuItem;
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
    private ResolverInterface $repository;
    private ItemInterface $menu;
    private ConfigureMenuSubscriber $menuSubscriber;

    protected function setUp(): void
    {
        $this->repository = new MemoryTypeResolver([]);
        $this->tokenStorage = new TokenStorage();
        $this->authorizationChecker = new AuthorizationChecker(
            $this->tokenStorage,
            new AccessDecisionManager(
                [new ContentTypeVoter($this->createMock(ObjectRepository::class))],
                new UnanimousStrategy(),
            ),
        );
        $this->menuSubscriber = new ConfigureMenuSubscriber($this->authorizationChecker, new ContentTypeManager(
            $this->repository,
            ContentType::class
        ));
        $this->menu = new MenuItem('integrated_menu', new MenuFactory());
    }

    public function testShowingTaxonomyOptionToAdmins()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('no-access', 'ROLE_ADMIN'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        $section = $this->menu->getChild('Taxonomy');
        $item = $section->getChild('Taxonomy index');

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

    public function testHidingTaxonomyOptionWhenTheUserHasOnlyReadAccess()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('taxonomy-read'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        self::assertNull($this->menu->getChild('Taxonomy'));
        self::assertEmpty($this->menu->getChildren());
    }

    public function testShowingTaxonomyOptionWhenTheUserHasWriteAccess()
    {
        $this->withTaxonomyContentType();
        $this->tokenStorage->setToken($this->user('taxonomy-access'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        $section = $this->menu->getChild('Taxonomy');
        $item = $section->getChild('Taxonomy index');

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

    public function testShowingBothTagsAndCategoryOptions()
    {
        $this->withTaxonomyContentType('tag');
        $this->withTaxonomyContentType('category');
        $this->tokenStorage->setToken($this->user('tag-access', 'category-access'));

        $this->menuSubscriber->onMenuConfigure(new ConfigureMenuEvent(new MenuFactory(), $this->menu));

        $section = $this->menu->getChild('Taxonomy');

        self::assertCount(2, $section->getChildren());
    }

    private function withTaxonomyContentType(string $name = 'taxonomy'): void
    {
        $taxonomy = new ContentType();
        $taxonomy->setId($name);
        $taxonomy->setName(ucfirst($name));
        $taxonomy->setClass(Taxonomy::class);
        $taxonomy->addPermission($this->permission(PermissionInterface::WRITE, $name.'-access'));
        $taxonomy->addPermission($this->permission(PermissionInterface::READ, $name.'-read'));
        $this->repository->addType($taxonomy);
    }

    private function user(string ...$roles): TokenInterface
    {
        $user = new User();
        foreach ($roles as $role) {
            if (str_starts_with($role, 'ROLE')) {
                $user->addRole(new Role($role));
            } else {
                $user->addGroup(new Group($role));
            }
        }

        return new PreAuthenticatedToken($user, 'main', ['foo']);
    }

    private function permission(int $type, string $group): Permission
    {
        $permission = new Permission();
        $permission->setMask($type);
        $permission->setGroup($group);

        return $permission;
    }
}
