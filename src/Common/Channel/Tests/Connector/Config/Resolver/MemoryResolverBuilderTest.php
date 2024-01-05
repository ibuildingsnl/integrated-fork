<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Common\Channel\Tests\Connector\Config\Resolver;

use Integrated\Common\Channel\Connector\Config\Config;
use Integrated\Common\Channel\Connector\Config\Options;
use Integrated\Common\Channel\Connector\Config\Resolver\MemoryResolverBuilder;
use Integrated\Common\Channel\Tests\Fixtures\Channel;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @author Jan Sanne Mulder <jansanne@e-active.nl>
 */
class MemoryResolverBuilderTest extends TestCase
{
    #[DataProvider('addConfigProvider')]
    public function testAddConfig(array $calls, array $expected)
    {
        $builder = $this->getInstance();

        foreach ($calls as $arguments) {
            foreach ($arguments[0] as $config) {
                $builder->addConfig($config, $arguments[1]);
            }
        }

        $resolver = $builder->getResolver();

        foreach ($expected['defaults'] as $config) {
            self::assertSame($config, $resolver->getConfig($config->getName()));
        }
        foreach ($expected['channels'] as $configSet) {
            foreach ($configSet as $config) {
                self::assertSame($config, $resolver->getConfig($config->getName()));
            }
        }
    }

    #[DataProvider('addConfigProvider')]
    public function testAddConfigs(array $calls, array $expected)
    {
        $builder = $this->getInstance();

        foreach ($calls as $arguments) {
            $builder->addConfigs($arguments[0], $arguments[1]);
        }

        $resolver = $builder->getResolver();

        foreach ($expected['defaults'] as $config) {
            self::assertSame($config, $resolver->getConfig($config->getName()));
        }
        foreach ($expected['channels'] as $configSet) {
            foreach ($configSet as $config) {
                self::assertSame($config, $resolver->getConfig($config->getName()));
            }
        }
    }

    public static function addConfigProvider()
    {
        $config1 = new Config('name1', 'adaptor1', new Options(), null);
        $config2 = new Config('name2', 'adaptor2', new Options(), null);
        $config3 = new Config('name3', 'adaptor3', new Options(), null);

        return [
            'with channel string id' => [
                [
                    [[$config1, $config2, $config3], 'channel'],
                ],
                ['channels' => ['channel' => [$config1, $config2, $config3]], 'defaults' => []],
            ],
            'with channel object' => [
                [
                    [[$config1, $config2, $config3], new Channel('channel')],
                ],
                ['channels' => ['channel' => [$config1, $config2, $config3]], 'defaults' => []],
            ],
            'with mixed channels' => [
                [
                    [[$config1], 'channel1'], [[$config2], 'channel2'], [[$config3], new Channel('channel2')],
                ],
                ['channels' => ['channel1' => [$config1], 'channel2' => [$config2, $config3]], 'defaults' => []],
            ],
            'multiple channels' => [
                [
                    [[$config1], 'channel1'], [[$config1, $config2, $config3], 'channel2'], [[$config3], 'channel3'],
                ],
                ['channels' => ['channel1' => [$config1], 'channel2' => [$config1, $config2, $config3], 'channel3' => [$config3]], 'defaults' => []],
            ],
            'defaults' => [
                [
                    [[$config1, $config2, $config3], null],
                ],
                ['channels' => [], 'defaults' => [$config1, $config2, $config3]],
            ],
            'defaults override channel' => [
                [
                    [[$config1, $config2, $config3], 'channel1'], [[$config1], null], [[$config1, $config2, $config3], 'channel2'],
                ],
                ['channels' => ['channel1' => [$config2, $config3], 'channel2' => [$config2, $config3]], 'defaults' => [$config1]],
            ],
        ];
    }

    public function testAddConfigInvalidArgument()
    {
        $this->expectException(\Integrated\Common\Channel\Exception\ExceptionInterface::class);

        $builder = $this->getInstance();
        $builder->addConfig(new Config('name', 'adaptor', new Options(), null), 42);
    }

    /**
     * @return MemoryResolverBuilder
     */
    protected function getInstance()
    {
        return new MemoryResolverBuilder();
    }
}
