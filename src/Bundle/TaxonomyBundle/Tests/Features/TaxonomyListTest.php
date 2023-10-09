<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Channel\WebsiteChannel;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Security\ChannelVoter;
use Integrated\Bundle\ContentBundle\Security\ContentChannelVoter;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyLister;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOptions;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyOverview;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTaxonomyRepository;
use Integrated\Bundle\UserBundle\Model\Group;
use Integrated\Bundle\UserBundle\Model\Role;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\Security\Permission;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

final class TaxonomyListTest extends TestCase
{
    private TaxonomyOverview $list;
    private TaxonomyRepositoryInterface $taxonomies;
    private TokenStorageInterface $tokenStorage;
    private array $users;
    private array $channels;

    protected function setUp(): void
    {
        $this->tokenStorage = new TokenStorage();
        $this->taxonomies = new MemoryTaxonomyRepository(new AuthorizationChecker(
            $this->tokenStorage,
            new AccessDecisionManager([
                new ContentChannelVoter(new AccessDecisionManager([
                    new ChannelVoter(),
                ])),
            ]),
        ));
        $this->list = new TaxonomyLister($this->taxonomies);
        $this->users = [
            'admin' => $this->user('ROLE_ADMIN'),
            'user 1' => $this->user('group_1'),
            'user 2' => $this->user('group_2'),
        ];
        $this->tokenStorage->setToken($this->users['admin']);
        $this->channels = [
            'brood' => (new WebsiteChannel())->setId('brood')->addPermission((new Permission())->setGroup('group_1')->setMask(3)),
            'vis' => (new WebsiteChannel())->setId('vis')->addPermission((new Permission())->setGroup('group_2')->setMask(1)),
            'kip' => (new WebsiteChannel())->setId('kip')->addPermission((new Permission())->setGroup('group_3')->setMask(3)),
        ];
    }

    public function testShowingOnlyTheThreeTagsThatFitOnThePage()
    {
        $this->add(
            $this->taxonomy('foo', 'Foo'),
            $this->taxonomy('bar', 'Bar'),
            $this->taxonomy('baz', 'Baz'),
            $this->taxonomy('yolo', 'Yolo'),
        );

        $list = $this->list->overviewFor('tag', TaxonomyOptions::page(1, 3));

        self::assertCount(3, $list);
        self::assertEquals('Bar', $list[0]->getTitle());
        self::assertEquals('Baz', $list[1]->getTitle());
        self::assertEquals('Foo', $list[2]->getTitle());
    }

    public function testShowingOnlyTheFiveTagsThatFitOnThePage()
    {
        $this->add(
            $this->taxonomy('foo', 'Foo'),
            $this->taxonomy('bar', 'Bar'),
            $this->taxonomy('baz', 'Baz'),
            $this->taxonomy('yolo', 'Yolo'),
            $this->taxonomy('tag', 'Tag'),
            $this->taxonomy('abc', 'Abc'),
        );

        $list = $this->list->overviewFor('tag', TaxonomyOptions::page(1, 5));

        self::assertCount(5, $list);
        self::assertEquals('Abc', $list[0]->getTitle());
        self::assertEquals('Bar', $list[1]->getTitle());
        self::assertEquals('Baz', $list[2]->getTitle());
        self::assertEquals('Foo', $list[3]->getTitle());
        self::assertEquals('Tag', $list[4]->getTitle());
    }

    public function testOnlyShowingTheFiveTagsWeCanSee()
    {
        $this->loginAs('user 1');

        $this->add(
            $this->taxonomy('foo', 'Foo', 'kip'),
            $this->taxonomy('bar', 'Bar', 'vis', 'brood'),
            $this->taxonomy('baz', 'Baz', 'vis'),
            $this->taxonomy('yolo', 'Yolo', 'brood'),
            $this->taxonomy('tag', 'Tag', 'brood', 'vis'),
            $this->taxonomy('abc', 'Abc', 'brood'),
            $this->taxonomy('def', 'Def', 'brood', 'kip'),
        );

        $list = $this->list->overviewFor('tag', TaxonomyOptions::page(1, 5));

        self::assertCount(5, $list);
        self::assertEquals('Abc', $list[0]->getTitle());
        self::assertEquals('Bar', $list[1]->getTitle());
        self::assertEquals('Def', $list[2]->getTitle());
        self::assertEquals('Tag', $list[3]->getTitle());
        self::assertEquals('Yolo', $list[4]->getTitle());
    }

    private function add(Taxonomy ...$taxonomies): void
    {
        foreach ($taxonomies as $taxonomy) {
            $this->taxonomies->add($taxonomy);
        }
    }

    private function setUsages(array $usageCounts): void
    {
        if (!$this->taxonomies instanceof MemoryTaxonomyRepository) {
            return;
        }
        foreach ($usageCounts as $id => $count) {
            $this->taxonomies->setUsageCount($id, $count);
        }
    }

    private function loginAs(string $user): void
    {
        $this->tokenStorage->setToken($this->users[$user]);
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

    private function taxonomy(
        string $id,
        string $title,
        string ...$channels
    ): Taxonomy {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setTitle($title);
        $taxonomy->setContentType('tag');

        foreach ($channels as $channel) {
            $taxonomy->addChannel($this->channels[$channel]);
        }

        return $taxonomy;
    }
}
