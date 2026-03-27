<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Form\Type;

use PHPUnit\Framework\TestCase;

final class SeoMetaTypeFallbackContractTest extends TestCase
{
    public function testSeoMetaTypeSupportsPrefillFallbacks(): void
    {
        $source = file_get_contents(__DIR__.'/../../../Form/Type/SeoMetaType.php');

        $this->assertIsString($source);
        $this->assertStringContainsString("'meta_title_fallback' => '%%title%% %%separator%% %%channel%%'", $source);
        $this->assertStringContainsString("'meta_description_fallback' => null", $source);
        $this->assertStringContainsString("'data' => \$metaTitle !== '' ? \$metaTitle : \$options['meta_title_fallback']", $source);
        $this->assertStringContainsString("'data' => \$metaDescription !== '' ? \$metaDescription : \$options['meta_description_fallback']", $source);
    }
}
