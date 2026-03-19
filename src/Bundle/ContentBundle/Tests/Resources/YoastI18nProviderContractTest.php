<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class YoastI18nProviderContractTest extends TestCase
{
    public function testYoastI18nProviderRegistersAllDomainsAndKeepsYoastComponentsFallback(): void
    {
        $provider = file_get_contents(__DIR__.'/../../YoastSeo/src/provider/I18nProvider.jsx');

        $this->assertIsString($provider);
        $this->assertStringContainsString('Object.entries(localeData).forEach(([domain, messages]) => {', $provider);
        $this->assertStringContainsString("setLocaleData(messages, domain);", $provider);
        $this->assertStringContainsString("if (localeData['js-text-analysis'] && !localeData['yoast-components']) {", $provider);
        $this->assertStringContainsString("setLocaleData(localeData['js-text-analysis'], 'yoast-components');", $provider);
    }
}
