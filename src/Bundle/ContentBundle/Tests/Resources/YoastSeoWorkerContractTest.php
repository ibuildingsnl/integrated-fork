<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class YoastSeoWorkerContractTest extends TestCase
{
    public function testWebpackConfigProvidesProcessShimForWorkerBuild(): void
    {
        $config = file_get_contents(__DIR__.'/../../YoastSeo/webpack.config.js');
        $shim = file_get_contents(__DIR__.'/../../YoastSeo/src/shims/process.js');

        $this->assertIsString($config);
        $this->assertIsString($shim);
        $this->assertStringContainsString("new webpack.ProvidePlugin({", $config);
        $this->assertStringContainsString("process: path.resolve(__dirname, './src/shims/process.js')", $config);
        $this->assertStringContainsString('module.exports = self.process || {', $shim);
    }

    public function testCompiledWorkersExposeBrowserSafeProcessShim(): void
    {
        $bundleWorker = file_get_contents(__DIR__.'/../../Resources/public/js/webWorker.js');
        $publicWorker = file_get_contents(__DIR__.'/../../../../../../../../public/bundles/integratedcontent/js/webWorker.js');

        $this->assertIsString($bundleWorker);
        $this->assertIsString($publicWorker);
        $this->assertStringContainsString('self.process=self.process||{env:{},throwDeprecation:!1,traceDeprecation:!1};', $bundleWorker);
        $this->assertStringContainsString('self.global=self.global||self;', $bundleWorker);
        $this->assertStringContainsString('self.process=self.process||{env:{},throwDeprecation:!1,traceDeprecation:!1};', $publicWorker);
        $this->assertStringContainsString('self.global=self.global||self;', $publicWorker);
    }
}
