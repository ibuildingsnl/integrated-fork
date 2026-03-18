<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

final class ContentTypeFeaturedExpirationSidebarOptionTest extends TestCase
{
    public function testSidebarContainsFeaturedExpirationOption(): void
    {
        $formType = file_get_contents(__DIR__.'/../../Form/Type/ContentTypeFormType.php');

        $this->assertIsString($formType);
        $this->assertStringContainsString("'options_featured_expiration', CheckboxSwitcherType::class, [", $formType);
        $this->assertStringContainsString("'property_path' => 'options[featured_expiration]'", $formType);
        $this->assertStringContainsString("'label' => 'Featured expiration'", $formType);
    }
}
