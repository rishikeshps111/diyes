<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ModulePrefix;
use Illuminate\Database\Eloquent\Model;

class PrefixCodeService
{
    private const DEFAULT_PREFIXES = [
        'academic_year' => 'AT',
        'grade' => 'GRD',
        'subject' => 'SUB',
        'division' => 'DIV',
        'department' => 'DEP',
        'designation' => 'DSG',
        'classroom' => 'CLS',
        'venue' => 'VEN',
        'holiday' => 'HOL',
        'time_table_category' => 'TTC',
        'timetable' => 'TT',
        'teacher' => 'EMP',
        'user' => 'EMP',
    ];

    public function next(string $module, string $modelClass, string $column = 'code'): string
    {
        $prefix = $this->prefix($module);
        $yearSegment = $this->yearSegment();
        $base = $prefix . $yearSegment;

        /** @var class-string<Model> $modelClass */
        $lastCode = $modelClass::query()
            ->where($column, 'like', $base . '%')
            ->orderByDesc('id')
            ->value($column);

        $nextNumber = 1;

        if (is_string($lastCode) && str_starts_with($lastCode, $base)) {
            $nextNumber = ((int) substr($lastCode, strlen($base))) + 1;
        }

        return $base . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function format(string $module, int $sequence): string
    {
        return $this->prefix($module)
            . $this->yearSegment()
            . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function prefix(string $module): string
    {
        $prefix = ModulePrefix::query()
            ->where('module', $module)
            ->value('prefix');

        return strtoupper($prefix ?: (self::DEFAULT_PREFIXES[$module] ?? str($module)->substr(0, 3)->upper()->toString()));
    }

    public function yearSegment(): string
    {
        $academicYear = AcademicYear::query()
            ->where('is_active', true)
            ->orderByDesc('start_date')
            ->value('academic_year');

        if (! $academicYear) {
            $startYear = now()->year;

            return $startYear . '-' . substr((string) ($startYear + 1), -2);
        }

        if (preg_match('/(\d{4})\D+(\d{2,4})/', $academicYear, $matches)) {
            return $matches[1] . '-' . substr($matches[2], -2);
        }

        return preg_replace('/\s+/', '', $academicYear);
    }
}
