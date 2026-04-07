<?php

namespace Integrated\Bundle\WorkflowBundle\Tests\Form\Type;

use Doctrine\Persistence\ObjectRepository;
use Integrated\Bundle\WorkflowBundle\Entity\Definition;
use Integrated\Bundle\WorkflowBundle\Form\Type\WorkflowStateType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowStateTypeTest extends TestCase
{
    public function testConfigureOptionsMemoizesWorkflowDefinitionLookupsById(): void
    {
        $definition = new Definition();

        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects(self::once())
            ->method('find')
            ->with('workflow-id')
            ->willReturn($definition);

        $type = new WorkflowStateType($repository);
        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);

        self::assertSame($definition, $resolver->resolve(['workflow' => 'workflow-id'])['workflow']);
        self::assertSame($definition, $resolver->resolve(['workflow' => 'workflow-id'])['workflow']);
    }
}
