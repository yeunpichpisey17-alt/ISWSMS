<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

abstract class Controller
{
    protected function yearMonthExpression(string $column): string
    {
        $driver = DB::connection()->getDriverName();
        $wrappedColumn = DB::connection()->getQueryGrammar()->wrap($column);

        return match ($driver) {
            'pgsql' => "TO_CHAR({$wrappedColumn}, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', {$wrappedColumn})",
            'sqlsrv' => "FORMAT({$wrappedColumn}, 'yyyy-MM')",
            default => "DATE_FORMAT({$wrappedColumn}, '%Y-%m')",
        };
    }
}
