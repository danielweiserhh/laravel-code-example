<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Traits;

use App\Http\Controllers\Traits\NormalizesInput;
use PHPUnit\Framework\TestCase;

class NormalizesInputTest extends TestCase
{
    use NormalizesInput;

    public function test_normalize_value_returns_value_when_set(): void
    {
        $validated = ['name' => 'John'];

        $result = $this->normalizeValue($validated, 'name');

        $this->assertEquals('John', $result);
    }

    public function test_normalize_value_returns_null_when_not_set(): void
    {
        $validated = [];

        $result = $this->normalizeValue($validated, 'name');

        $this->assertNull($result);
    }

    public function test_normalize_value_returns_null_for_empty_string(): void
    {
        $validated = ['name' => ''];

        $result = $this->normalizeValue($validated, 'name');

        $this->assertNull($result);
    }

    public function test_normalize_value_preserves_zero(): void
    {
        $validated = ['count' => 0];

        $result = $this->normalizeValue($validated, 'count');

        $this->assertSame(0, $result);
    }

    public function test_normalize_value_preserves_false(): void
    {
        $validated = ['active' => false];

        $result = $this->normalizeValue($validated, 'active');

        $this->assertFalse($result);
    }

    public function test_normalize_values_normalizes_multiple_keys(): void
    {
        $validated = [
            'name' => 'John',
            'email' => '',
            'age' => 30,
        ];

        $result = $this->normalizeValues($validated, ['name', 'email', 'phone']);

        $this->assertEquals('John', $result['name']);
        $this->assertNull($result['email']);
        $this->assertNull($result['phone']);
    }

    public function test_normalize_int_returns_int_when_set(): void
    {
        $validated = ['count' => '42'];

        $result = $this->normalizeInt($validated, 'count');

        $this->assertSame(42, $result);
    }

    public function test_normalize_int_returns_null_when_not_set(): void
    {
        $validated = [];

        $result = $this->normalizeInt($validated, 'count');

        $this->assertNull($result);
    }

    public function test_normalize_int_returns_null_for_empty_string(): void
    {
        $validated = ['count' => ''];

        $result = $this->normalizeInt($validated, 'count');

        $this->assertNull($result);
    }

    public function test_normalize_bool_returns_true_for_truthy(): void
    {
        $validated = ['active' => 1];

        $result = $this->normalizeBool($validated, 'active');

        $this->assertTrue($result);
    }

    public function test_normalize_bool_returns_false_for_falsy(): void
    {
        $validated = ['active' => 0];

        $result = $this->normalizeBool($validated, 'active');

        $this->assertFalse($result);
    }

    public function test_normalize_bool_returns_null_when_not_set(): void
    {
        $validated = [];

        $result = $this->normalizeBool($validated, 'active');

        $this->assertNull($result);
    }
}
