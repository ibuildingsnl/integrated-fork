<?php

declare(strict_types=1);

namespace Integrated\Bundle\FormTypeBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ServicesConfigContractTest extends TestCase
{
    public function testFilterableContentChoiceServiceConsumesExcludedContentTypeParameter(): void
    {
        $services = file_get_contents(__DIR__.'/../../Resources/config/services.xml');

        self::assertIsString($services);
        self::assertStringContainsString('integrated_form_type.form_type.filterable_content_choice_type', $services);
        self::assertStringContainsString('%integrated_form_type.filterable_content_choice.excluded_content_type_keys%', $services);
    }
}
