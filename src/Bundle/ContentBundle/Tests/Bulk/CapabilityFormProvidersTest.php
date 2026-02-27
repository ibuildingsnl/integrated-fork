<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Bulk;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Integrated\Bundle\ContentBundle\Bulk\BulkCapabilityResolver;
use Integrated\Bundle\ContentBundle\Bulk\CanonicalFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\CanonicalHandler;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\FeaturedHandler;
use Integrated\Bundle\ContentBundle\Bulk\PremiumFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\PremiumHandler;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\PublishWindowHandler;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowAssignFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowAssignHandler;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowStateFormProvider;
use Integrated\Bundle\ContentBundle\Bulk\WorkflowStateHandler;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\CanonicalAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\FeaturedAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PremiumAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\PublishWindowAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\WorkflowAssignAction;
use Integrated\Bundle\ContentBundle\Document\Bulk\Action\WorkflowStateAction;
use Integrated\Bundle\ContentBundle\Document\Content\Content;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionCanonicalType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionFeaturedType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPremiumType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionPublishWindowType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionWorkflowAssignType;
use Integrated\Bundle\ContentBundle\Form\Type\BulkActionWorkflowStateType;
use Integrated\Bundle\UserBundle\Doctrine\UserManager;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\ContentType\ResolverInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CapabilityFormProvidersTest extends TestCase
{
    private BulkCapabilityResolver&MockObject $resolver;
    private ResolverInterface&MockObject $contentTypeResolver;
    private EntityManagerInterface&MockObject $entityManager;
    /** @var EntityRepository<Definition>&MockObject */
    private EntityRepository&MockObject $definitionRepository;
    private UserManager&MockObject $userManager;

    protected function setUp(): void
    {
        $this->resolver = $this->createMock(BulkCapabilityResolver::class);
        $this->contentTypeResolver = $this->createMock(ResolverInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->definitionRepository = $this->createMock(EntityRepository::class);
        $this->userManager = $this->createMock(UserManager::class);
    }

    public function testCanonicalProviderReturnsEmptyConfigWhenUnsupported(): void
    {
        $provider = new CanonicalFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'canonical')
            ->willReturn(false);

        self::assertSame([], $provider->getConfig([]));
    }

    public function testCanonicalProviderReturnsConfigWhenSupported(): void
    {
        $provider = new CanonicalFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'canonical')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertInstanceOf(Config::class, $config[0]);
        self::assertSame(CanonicalHandler::class, $config[0]->getHandler());
        self::assertSame('canonical', $config[0]->getName());
        self::assertSame(BulkActionCanonicalType::class, $config[0]->getType());

        $action = (new CanonicalAction(CanonicalHandler::class))
            ->setSource('Reuters')
            ->setSourceUrl('https://example.org');
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testFeaturedProviderReturnsConfigWhenSupported(): void
    {
        $provider = new FeaturedFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'featured')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(FeaturedHandler::class, $config[0]->getHandler());
        self::assertSame('featured', $config[0]->getName());
        self::assertSame(BulkActionFeaturedType::class, $config[0]->getType());

        $action = (new FeaturedAction(FeaturedHandler::class))->setFeatured(true);
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testPremiumProviderReturnsConfigWhenSupported(): void
    {
        $provider = new PremiumFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'premium')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(PremiumHandler::class, $config[0]->getHandler());
        self::assertSame('premium', $config[0]->getName());
        self::assertSame(BulkActionPremiumType::class, $config[0]->getType());

        $action = (new PremiumAction(PremiumHandler::class))->setPremium(true);
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testPublishWindowProviderReturnsConfigWhenSupported(): void
    {
        $provider = new PublishWindowFormProvider($this->resolver);

        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'publishTime')
            ->willReturn(true);

        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(PublishWindowHandler::class, $config[0]->getHandler());
        self::assertSame('publishTime', $config[0]->getName());
        self::assertSame(BulkActionPublishWindowType::class, $config[0]->getType());

        $action = (new PublishWindowAction(PublishWindowHandler::class))
            ->setStartDate(new \DateTimeImmutable('2026-01-01 10:00:00'));
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testWorkflowStateProviderReturnsConfigWhenSupported(): void
    {
        $this->resolver->expects($this->once())
            ->method('supports')
            ->with($this->isType('array'), 'workflow')
            ->willReturn(true);

        $contentTypeId = 'news';
        $workflowId = 'workflow-a';

        $content = new class extends Content {
            public function __toString(): string
            {
                return '';
            }
        };
        $content->setContentType($contentTypeId);

        $contentType = $this->createConfiguredMock(\Integrated\Common\ContentType\ContentTypeInterface::class, [
            'hasOption' => true,
            'getOption' => $workflowId,
        ]);

        $workflow = new Definition();
        $state = new Definition\State();
        $state->setName('In review');
        $workflow->addState($state);

        $this->contentTypeResolver->expects($this->once())
            ->method('hasType')
            ->with($contentTypeId)
            ->willReturn(true);

        $this->contentTypeResolver->expects($this->once())
            ->method('getType')
            ->with($contentTypeId)
            ->willReturn($contentType);

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Definition::class)
            ->willReturn($this->definitionRepository);

        $this->definitionRepository->expects($this->once())
            ->method('find')
            ->with($workflowId)
            ->willReturn($workflow);

        $provider = new WorkflowStateFormProvider($this->resolver, $this->contentTypeResolver, $this->entityManager);
        $config = $provider->getConfig([$content]);

        self::assertCount(1, $config);
        self::assertSame(WorkflowStateHandler::class, $config[0]->getHandler());
        self::assertSame('workflowState', $config[0]->getName());
        self::assertSame(BulkActionWorkflowStateType::class, $config[0]->getType());

        $action = (new WorkflowStateAction(WorkflowStateHandler::class))->setState($state->getId());
        self::assertTrue($config[0]->getMatcher()->match($action));
    }

    public function testWorkflowAssignProviderReturnsConfigWhenSupported(): void
    {
        $this->resolver->expects($this->once())
            ->method('supports')
            ->with([], 'workflow')
            ->willReturn(true);

        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $this->userManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $queryBuilder->expects($this->once())
            ->method('join')
            ->with('User.scope', 'scope')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('where')
            ->with('scope.admin = 1')
            ->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($query);

        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([
                ['id' => 'u1', 'username' => 'alice'],
            ]);

        $provider = new WorkflowAssignFormProvider($this->resolver, $this->userManager);
        $config = $provider->getConfig([]);

        self::assertCount(1, $config);
        self::assertSame(WorkflowAssignHandler::class, $config[0]->getHandler());
        self::assertSame('workflowAssign', $config[0]->getName());
        self::assertSame(BulkActionWorkflowAssignType::class, $config[0]->getType());

        $action = (new WorkflowAssignAction(WorkflowAssignHandler::class))->setAssigned('u1');
        self::assertTrue($config[0]->getMatcher()->match($action));
    }
}
