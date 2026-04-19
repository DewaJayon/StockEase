<?php

use App\Helpers\FormatBytes;

it('formats bytes correctly', function ($bytes, $expected) {
    expect(FormatBytes::formatBytes($bytes))->toBe($expected);
})->with([
    'zero' => [0, '0 B'],
    'bytes' => [100, '100 B'],
    'KB' => [1024, '1 KB'],
    'MB' => [1024 * 1024, '1 MB'],
    'GB' => [1024 * 1024 * 1024, '1 GB'],
    'TB' => [1024 * 1024 * 1024 * 1024, '1 TB'],
    'PB' => [1024 * 1024 * 1024 * 1024 * 1024, '1024 TB'], // Max unit is TB
]);

it('formats bytes with precision', function () {
    expect(FormatBytes::formatBytes(1500, 1))->toBe('1.5 KB');
    expect(FormatBytes::formatBytes(1500, 0))->toBe('1 KB');
});
