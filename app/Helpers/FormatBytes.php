<?php

namespace App\Helpers;

class FormatBytes
{
    /**
     * Format the given bytes to human-readable format.
     *
     * @param int $bytes The size of the file in bytes.
     * @param int $precision The number of decimal places to round to.
     * @return string The formatted size of the file.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
