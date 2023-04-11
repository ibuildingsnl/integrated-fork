<?php

namespace Integrated\Bundle\TaxonomyBundle\Tests\Features\Doubles;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class FakeUrlGenerator implements UrlGeneratorInterface
{
    public function setContext(RequestContext $context)
    {
    }

    public function getContext()
    {
    }

    public function generate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return $name;
    }
}
