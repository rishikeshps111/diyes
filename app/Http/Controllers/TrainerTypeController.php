<?php

namespace App\Http\Controllers;

use App\Services\TitleMasterService;
use App\Services\TrainerTypeService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TrainerTypeController extends TitleMasterController implements HasMiddleware
{
    public function __construct(private readonly TrainerTypeService $trainerTypeService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.trainer-type', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.trainer-type', only: ['create', 'store']),
            new Middleware('can:edit.trainer-type', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.trainer-type', only: ['destroy']),
        ];
    }

    protected function service(): TitleMasterService
    {
        return $this->trainerTypeService;
    }

    protected function masterConfig(): array
    {
        return [
            'singular' => 'Trainer Type',
            'plural' => 'Trainer Types',
            'route' => 'trainer-types',
            'permission' => 'trainer-type',
            'filename' => 'trainer-types',
        ];
    }
}
