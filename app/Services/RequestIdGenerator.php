<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Membuat nomor ID otomatis berformat PREFIX-TAHUN-0001, urut per tahun.
 * Contoh: ERF-2026-0001, GA-2026-0001
 */
class RequestIdGenerator
{
    public static function generate(string $table, string $column, string $prefix): string
    {
        $year = now()->year;

        return DB::transaction(function () use ($table, $column, $prefix, $year) {
            $lastNumber = DB::table($table)
                ->where($column, 'like', "{$prefix}-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc($column)
                ->value($column);

            $next = 1;

            if ($lastNumber) {
                $parts = explode('-', $lastNumber);
                $next = ((int) end($parts)) + 1;
            }

            return sprintf('%s-%d-%04d', $prefix, $year, $next);
        });
    }
}
