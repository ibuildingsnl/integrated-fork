<?php

namespace Integrated\Bundle\IntegratedBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

trait PaginationQueryTrait
{
    protected function getPositiveIntQueryParameter(Request $request, string $key, int $default): int
    {
        $value = $request->query->all()[$key] ?? $default;

        if (\is_int($value) && $value > 0) {
            return $value;
        }

        if (\is_string($value)) {
            $int = filter_var($value, \FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            if (false !== $int) {
                return $int;
            }
        }

        return $default;
    }
}
