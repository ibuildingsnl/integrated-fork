<?php

declare(strict_types=1);

namespace Integrated\Bundle\ContentBundle\Tests\Resources;

use PHPUnit\Framework\TestCase;

class BaseTemplateFlashContainerTest extends TestCase
{
    public function testFlashContainerIsRenderedOutsideWrapperHolder(): void
    {
        $template = file_get_contents(__DIR__.'/../../Resources/views/base.html.twig');

        $this->assertIsString($template);
        $this->assertStringContainsString('{% endblock body %}', $template);
        $this->assertStringContainsString('<div id="flash-messages" data-turbo-temporary>', $template);

        $bodyBlockEnd = strpos($template, '{% endblock body %}');
        $flashContainer = strpos($template, '<div id="flash-messages" data-turbo-temporary>');

        $this->assertIsInt($bodyBlockEnd);
        $this->assertIsInt($flashContainer);
        $this->assertGreaterThan($bodyBlockEnd, $flashContainer);
    }
}
