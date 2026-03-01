<?php

declare(strict_types=1);

namespace Integrated\Bundle\PageBundle\Tests\PageBuilder\V2;

use Integrated\Bundle\PageBundle\PageBuilder\V2\Validation\LayoutPayloadValidator;
use PHPUnit\Framework\TestCase;

final class LayoutPayloadValidatorTest extends TestCase
{
    public function testRejectsPayloadWithoutRootNode(): void
    {
        $validator = new LayoutPayloadValidator(__DIR__.'/../../../Resources/schema/pagebuilder/v2');
        $errors = $validator->validate(['components' => []], 'twindigital');

        self::assertNotEmpty($errors);
        self::assertSame('/root', $errors[0]->path);
        self::assertSame('required', $errors[0]->code);
    }
}

