<?php
namespace App\Services;

use App\Models\ActivityLog;

class ActivityLogService
{
    public static function log($module, $action, $recordId = null, $description = null)
    {
        ActivityLog::create([
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()->name ?? 'System',
            'module'      => $module,
            'action'      => $action,
            'record_id'   => $recordId,
            'description' => $description,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'url'         => request()->fullUrl(),
        ]);
    }
}