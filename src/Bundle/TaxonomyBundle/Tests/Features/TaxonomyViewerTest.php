<?php

declare(strict_types=1);

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features;

use Integrated\Bundle\ContentBundle\Document\Content\Taxonomy;
use Integrated\Bundle\ContentBundle\Document\ContentType\ContentType;
use Integrated\Bundle\ContentBundle\Document\ContentType\Embedded\Field;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyIndexer;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyLister;
use Integrated\Bundle\TaxonomyBundle\Services\TaxonomyViewer;
use Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles\MemoryTaxonomyRepository;
use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Common\ContentType\ContentTypeInterface;
use Integrated\Common\ContentType\Iterator;
use Integrated\Common\ContentType\ResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\Strategy\AffirmativeStrategy;

final class TaxonomyViewerTest extends TestCase
{
    public function testCachingHasParentsLookupPerContentType(): void
    {
        $taxonomyType = (new ContentType())
            ->setId('taxonomy')
            ->setClass(Taxonomy::class)
            ->setFields([
                (new Field())->setName('parent_id'),
            ]);

        $resolver = new CountingTypeResolver(['taxonomy' => $taxonomyType]);
        $repository = new MemoryTaxonomyRepository();
        $authorization = new AuthorizationChecker(
            $tokens = new TokenStorage(),
            new AccessDecisionManager([], new AffirmativeStrategy(true)),
        );
        $tokens->setToken(new PreAuthenticatedToken(new User(), 'main', ['foo']));

        $viewer = new TaxonomyViewer(
            $resolver,
            new TaxonomyIndexer($repository, $authorization),
            new TaxonomyLister($repository),
        );

        $viewer->childrenOf('taxonomy', 'root');
        $viewer->overviewFor('taxonomy');
        $viewer->countFor('taxonomy');

        self::assertSame(1, $resolver->getTypeCalls);
    }
}

final class CountingTypeResolver implements ResolverInterface
{
    /** @var array<string, ContentTypeInterface> */
    private array $types;

    public int $getTypeCalls = 0;

    /** @param array<string, ContentTypeInterface> $types */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    public function getType($type)
    {
        ++$this->getTypeCalls;

        return $this->types[(string) $type];
    }

    public function hasType($type)
    {
        return isset($this->types[(string) $type]);
    }

    public function getTypes()
    {
        return new Iterator($this->types);
    }
}
