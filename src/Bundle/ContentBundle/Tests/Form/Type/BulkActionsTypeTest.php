<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Form\Type;

use Integrated\Bundle\ContentBundle\Form\Type\BulkActionsType;
use Integrated\Common\Bulk\BulkActionInterface;
use Integrated\Common\Bulk\Form\ActionMatcherInterface;
use Integrated\Common\Bulk\Form\Config;
use Integrated\Common\Bulk\Form\ConfigProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class BulkActionsTypeTest extends TypeTestCase
{
    /** @var ConfigProviderInterface&MockObject */
    private $provider;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(ConfigProviderInterface::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        return [
            new PreloadedExtension([
                new BulkActionsType($this->provider),
            ], []),
        ];
    }

    public function testReadonlyActionsAreDisabled(): void
    {
        $this->provider
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn([
                new Config(
                    'handler',
                    'sample',
                    TextType::class,
                    [],
                    new class implements ActionMatcherInterface {
                        public function match(BulkActionInterface $action)
                        {
                            return false;
                        }
                    }
                ),
            ]);

        $form = $this->factory->create(BulkActionsType::class, [], [
            'content' => [],
            'readonly' => true,
        ]);

        self::assertTrue($form->has(bin2hex('handler').'_sample'));
        self::assertTrue($form->get(bin2hex('handler').'_sample')->getConfig()->getOption('disabled'));
    }
}
