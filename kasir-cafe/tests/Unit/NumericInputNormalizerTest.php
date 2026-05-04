<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tests\TestCase;

class NumericInputNormalizerTest extends TestCase
{
    public function test_it_normalizes_common_rupiah_and_decimal_formats(): void
    {
        $request = new Request([
            'plain' => '1950',
            'id_thousand' => '1.950',
            'id_decimal' => '1.950,75',
            'us_decimal' => '1,950.75',
            'fraction' => '0,01',
            'small_decimal' => '0.001',
            'currency' => 'Rp 1.950.000,50',
            'invalid_text' => 'abc123',
            'scientific' => '1e5',
        ]);

        $this->normalizer()->normalize($request, [
            'plain',
            'id_thousand',
            'id_decimal',
            'us_decimal',
            'fraction',
            'small_decimal',
            'currency',
            'invalid_text',
            'scientific',
        ]);

        $this->assertSame('1950', $request->input('plain'));
        $this->assertSame('1950', $request->input('id_thousand'));
        $this->assertSame('1950.75', $request->input('id_decimal'));
        $this->assertSame('1950.75', $request->input('us_decimal'));
        $this->assertSame('0.01', $request->input('fraction'));
        $this->assertSame('0.001', $request->input('small_decimal'));
        $this->assertSame('1950000.50', $request->input('currency'));
        $this->assertSame('abc123', $request->input('invalid_text'));
        $this->assertSame('1e5', $request->input('scientific'));
    }

    public function test_it_normalizes_nested_numeric_lines(): void
    {
        $request = new Request([
            'lines' => [
                72 => ['qty' => '1.000', 'unit_cost' => '195,50'],
                73 => ['qty' => '0,25', 'unit_cost' => 'Rp 2.500'],
            ],
        ]);

        $this->normalizer()->normalize($request, ['lines.*.qty', 'lines.*.unit_cost']);

        $this->assertSame('1000', $request->input('lines.72.qty'));
        $this->assertSame('195.50', $request->input('lines.72.unit_cost'));
        $this->assertSame('0.25', $request->input('lines.73.qty'));
        $this->assertSame('2500', $request->input('lines.73.unit_cost'));
    }

    private function normalizer(): object
    {
        return new class extends Controller {
            public function normalize(Request $request, array $paths): void
            {
                $this->normalizeNumericInputs($request, $paths);
            }
        };
    }
}
