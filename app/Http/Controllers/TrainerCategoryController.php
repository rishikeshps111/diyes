<?php

namespace App\Http\Controllers;

use App\Services\TitleMasterService;
use App\Services\TrainerCategoryService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TrainerCategoryController extends TitleMasterController implements HasMiddleware
{
    public function __construct(private readonly TrainerCategoryService $trainerCategoryService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:view.trainer-category', only: ['index', 'data', 'exportExcel', 'exportPdf']),
            new Middleware('can:create.trainer-category', only: ['create', 'store']),
            new Middleware('can:edit.trainer-category', only: ['edit', 'update', 'toggleStatus']),
            new Middleware('can:delete.trainer-category', only: ['destroy']),
        ];
    }

    protected function service(): TitleMasterService
    {
        return $this->trainerCategoryService;
    }

    protected function masterConfig(): array
    {
        return [
            'singular' => 'Trainer Category',
            'plural' => 'Trainer Categories',
            'route' => 'trainer-categories',
            'permission' => 'trainer-category',
            'filename' => 'trainer-categories',
        ];
    }
}
