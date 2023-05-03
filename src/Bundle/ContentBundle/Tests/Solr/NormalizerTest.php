<?php

/*
 * This file is part of the Integrated package.
 *
 * (c) e-Active B.V. <integrated@e-active.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integrated\Bundle\ContentBundle\Tests\Solr;

use Integrated\Bundle\ContentBundle\Solr\Normalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class NormalizerTest extends TestCase
{
    #[DataProvider('normalizeProvider')]
    public function testNormalize($expected, $actual)
    {
        $this->assertEquals($expected, Normalizer::normalize($actual));
    }

    public static function normalizeProvider(): array
    {
        return [
            'strtolower' => [
                'test', 'Test',
            ],
            'trim' => [
                'test', '  test  ',
            ],
            'diacritics' => [
                'eeaaooii', 'éëáäóöíï',
            ],
            'new lines and tabs' => [
                'test test', "\ntest\n\ttest\t",
            ],
            'reduce spaces' => [
                'test test', 'test  test',
            ],
            'mixed' => [
                'test eaoi test', "\n Test\n\t éäóï \t\n TEST",
            ],
        ];
    }
}
