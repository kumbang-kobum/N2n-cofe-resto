<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    protected function normalizeNumericInputs(Request $request, array $paths): void
    {
        $data = $request->input();

        foreach ($paths as $path) {
            $this->normalizeNumericPath($data, explode('.', $path));
        }

        $request->merge($data);
    }

    private function normalizeNumericPath(array &$target, array $segments): void
    {
        if ($segments === []) {
            return;
        }

        $segment = array_shift($segments);

        if ($segment === '*') {
            foreach ($target as &$value) {
                if ($segments === []) {
                    $value = $this->normalizeNumericValue($value);
                } elseif (is_array($value)) {
                    $this->normalizeNumericPath($value, $segments);
                }
            }
            unset($value);

            return;
        }

        if (! array_key_exists($segment, $target)) {
            return;
        }

        if ($segments === []) {
            $target[$segment] = $this->normalizeNumericValue($target[$segment]);

            return;
        }

        if (is_array($target[$segment])) {
            $this->normalizeNumericPath($target[$segment], $segments);
        }
    }

    private function normalizeNumericValue(mixed $value): mixed
    {
        if ($value === null || is_int($value) || is_float($value)) {
            return $value;
        }

        if (! is_string($value)) {
            return $value;
        }

        $value = trim(str_replace("\xc2\xa0", ' ', $value));
        if ($value === '') {
            return null;
        }

        $normalized = str_replace(' ', '', $value);
        $normalized = preg_replace('/^(Rp|IDR)\.?/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\.?(Rp|IDR)$/i', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/^[\$€£¥]|[\$€£¥]$/', '', $normalized) ?? $normalized;

        if (preg_match('/[^0-9,.\-]/', $normalized) === 1) {
            return $value;
        }

        if ($normalized === '' || $normalized === '-') {
            return $value;
        }

        $commaPos = strrpos($normalized, ',');
        $dotPos = strrpos($normalized, '.');

        if ($commaPos !== false && $dotPos !== false) {
            if ($commaPos > $dotPos) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif ($commaPos !== false) {
            if (preg_match('/^-?[1-9]\d{0,2}(,\d{3})+$/', $normalized) === 1) {
                $normalized = str_replace(',', '', $normalized);
            } else {
                $normalized = str_replace(',', '.', $normalized);
            }
        } elseif ($dotPos !== false && preg_match('/^-?[1-9]\d{0,2}(\.\d{3})+$/', $normalized) === 1) {
            $normalized = str_replace('.', '', $normalized);
        }

        return $normalized;
    }
}
