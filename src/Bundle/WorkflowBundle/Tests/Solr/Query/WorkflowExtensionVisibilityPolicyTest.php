<?php

declare(strict_types=1);

namespace Integrated\Bundle\WorkflowBundle\Tests\Solr\Query;

use Integrated\Bundle\UserBundle\Model\User;
use Integrated\Bundle\WorkflowBundle\Solr\Query\WorkflowExtension;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Select\Query\Query;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WorkflowExtensionVisibilityPolicyTest extends TestCase
{
    public function testBuildIncludesContentTypeVisibilityGateForProtectedContentTypes(): void
    {
        $query = new Query();
        $resolver = new OptionsResolver();
        $extension = $this->createExtension();
        $extension->configureOptions($resolver);

        $extension->build($query, $resolver->resolve([]));

        self::assertArrayHasKey('workflow', $query->getFilterQueries());

        $securityQuery = $query->getFilterQueries()['workflow']->getQuery();

        self::assertStringContainsString('-security_content_type_required:[* TO *]', $securityQuery);
        self::assertMatchesRegularExpression('/security_workflow_read:\s*\(\(21\s*\)\s*OR\s*\(29\)\)/', $securityQuery);
        self::assertMatchesRegularExpression('/security_workflow_write:\s*\(\(21\s*\)\s*OR\s*\(29\)\)/', $securityQuery);
        self::assertMatchesRegularExpression('/security_content_type_read:\s*\(\(21\s*\)\s*OR\s*\(29\)\)/', $securityQuery);
        self::assertMatchesRegularExpression('/security_content_type_write:\s*\(\(21\s*\)\s*OR\s*\(29\)\)/', $securityQuery);
        self::assertStringContainsString('facet_workflow_assigned_id: 2621', $securityQuery);
    }

    private function createExtension(): WorkflowExtension
    {
        $security = $this->createMock(Security::class);
        $security->expects(self::once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);
        $security->expects(self::atLeastOnce())
            ->method('getUser')
            ->willReturn($this->createUser());

        return new WorkflowExtension($security);
    }

    private function createUser(): User
    {
        $user = $this->createMock(User::class);
        $user->expects(self::atLeastOnce())
            ->method('getId')
            ->willReturn(2621);
        $user->expects(self::atLeastOnce())
            ->method('getGroups')
            ->willReturn([
                new class() {
                    public function getId(): int
                    {
                        return 21;
                    }
                },
                new class() {
                    public function getId(): int
                    {
                        return 29;
                    }
                },
            ]);
        $user->expects(self::atLeastOnce())
            ->method('getRelation')
            ->willReturn(null);

        return $user;
    }
}
