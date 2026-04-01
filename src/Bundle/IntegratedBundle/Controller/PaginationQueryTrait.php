<?php

namespace Integrated\Bundle\IntegratedBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

trait PaginationQueryTrait
{
    protected function getPositiveIntQueryParameter(Request $request, string $key, int $default): int
    {
        return $request->query->getInt($key, $default);
    }
}
