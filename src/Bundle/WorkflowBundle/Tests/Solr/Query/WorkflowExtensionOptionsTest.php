<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\WorkflowBundle\Tests\Solr\Query;

use Integrated\Bundle\WorkflowBundle\Solr\Query\WorkflowExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkflowExtensionOptionsTest extends TestCase
{
    public function testWorkflowOptionsIgnoreNonArrays(): void
    {
        $resolver = new OptionsResolver();
        $this->createExtension()->configureOptions($resolver);

        $options = $resolver->resolve([
            'workflow_state' => 'draft',
            'workflow_assigned' => null,
        ]);

        self::assertSame([], $options['workflow_state']);
        self::assertSame([], $options['workflow_assigned']);
    }

    public function testWorkflowOptionsSanitizeStringArrays(): void
    {
        $resolver = new OptionsResolver();
        $this->createExtension()->configureOptions($resolver);

        $options = $resolver->resolve([
            'workflow_state' => [' draft ', '', [], null],
            'workflow_assigned' => [' user_1 ', ' ', ['x']],
        ]);

        self::assertSame(['draft'], $options['workflow_state']);
        self::assertSame(['user_1'], $options['workflow_assigned']);
    }

    private function createExtension(): WorkflowExtension
    {
        return new WorkflowExtension($this->createMock(Security::class));
    }
}
