<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Channel\WebsiteChannel;
use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Security\ChannelVoter;
use Integrated\Bundle\ContentBundle\Security\ContentChannelVoter;
use Integrated\Bundle\TaxonomyBundle\Domain\TaxonomyRepositoryInterface;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexer;
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

final class TaxonomyPermissionsTest extends TestCase
{
    private TaxonomyOverview $indexer;
    private TaxonomyRepositoryInterface $taxonomies;
    private TokenStorageInterface $tokenStorage;
    private array $users;
    private array $channels;

    protected function setUp(): void
    {
        $this->taxonomies = new MemoryTaxonomyRepository();
        $this->tokenStorage = new TokenStorage();
        $this->indexer = new TaxonomyIndexer($this->taxonomies, new AuthorizationChecker(
            $this->tokenStorage,
            new AccessDecisionManager([
                new ContentChannelVoter(new AccessDecisionManager([
                    new ChannelVoter(),
                ])),
            ]),
        ));
        $this->users = [
            'admin' => $this->user('ROLE_ADMIN'),
            'user 1' => $this->user('group_1'),
            'user 2' => $this->user('group_2'),
        ];
        $this->channels = [
            'brood' => (new WebsiteChannel())->setId('brood')->addPermission((new Permission())->setGroup('group_1')->setMask(3)),
            'vis' => (new WebsiteChannel())->setId('vis')->addPermission((new Permission())->setGroup('group_2')->setMask(1)),
            'kip' => (new WebsiteChannel())->setId('kip')->addPermission((new Permission())->setGroup('group_3')->setMask(3)),
        ];
        $this->taxonomy('brood', null, 'brood');
        $this->taxonomy('beleg', 'brood', 'brood');
        $this->taxonomy('graan', 'brood', 'brood');
        $this->taxonomy('ovens', 'brood', 'brood');
        $this->taxonomy('vissen', null, 'vis');
        $this->taxonomy('hengels', 'vissen', 'vis');
        $this->taxonomy('baars', 'vissen', 'vis');
        $this->taxonomy('snoekbaars', 'baars', 'vis');
        $this->taxonomy('pos', 'baars', 'vis');
        $this->taxonomy('kippetjes', null, 'kip');
    }

    public function testAdminSeesAllTaxonomies()
    {
        $this->loginAs('admin');

        self::assertCount(10, $this->indexer->overviewFor('taxonomy'));
    }

    public function testUser1SeesOnlyBreadTaxonomies()
    {
        $this->loginAs('user 1');

        self::assertCount(4, $this->indexer->overviewFor('taxonomy'));
    }

    public function testUser2SeesOnlyFishTaxonomies()
    {
        $this->loginAs('user 2');

        self::assertCount(5, $this->indexer->overviewFor('taxonomy'));
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
        string $parent = null,
        string ...$channels
    ): void {
        $taxonomy = new Taxonomy();
        $taxonomy->setId($id);
        $taxonomy->setParentID($parent);
        $taxonomy->setContentType('taxonomy');
        foreach ($channels as $channel) {
            $taxonomy->addChannel($this->channels[$channel]);
        }

        $this->taxonomies->add($taxonomy);
    }
}
