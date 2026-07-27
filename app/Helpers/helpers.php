<?php
use App\Models\ActivityLog;

if (!function_exists('activityLog')) {

    function activityLog($module, $action, $recordId = null, $description = null)
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

            'url'         => request()->fullUrl()

        ]);
    }
}