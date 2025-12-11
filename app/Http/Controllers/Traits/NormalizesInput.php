<?php

declare(strict_types=1);

namespace App\Http\Controllers\Traits;

trait NormalizesInput
{
    protected function normalizeValue(array $validated, string $key): mixed
    {
        if (! isset($validated[$key])) {
            return null;
        }

        $value = $validated[$key];

        if ($value === '') {
            return null;
        }

        return $value;
    }
    
    protected function normalizeValues(array $validated, array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->normalizeValue($validated, $key);
        }

        return $result;
    }
    
    protected function normalizeInt(array $validated, string $key): ?int
    {
        $value = $this->normalizeValue($validated, $key);

        if ($value === null) {
            return null;
        }

        return (int) $value;
    }
    
    protected function normalizeBool(array $validated, string $key): ?bool
    {
        if (! isset($validated[$key])) {
            return null;
        }

        return (bool) $validated[$key];
    }
}
