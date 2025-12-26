<?php

namespace App\Traits;

use App\Models\LogActivity;
use AWS\CRT\Log;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{
    public static function bootLogsActivity()
    {
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                $log = [];
                $log['subject'] = 'Model ' . class_basename($model) . ' was ' . $event;
                $log['url'] = Request::fullUrl();
                $log['method'] = Request::method();
                $log['ip'] = Request::ip();
                $log['agent'] = Request::header('user-agent');
                $log['user_id'] = auth()->check() ? auth()->user()->id : 1;
                LogActivity::create($log);
            });
        }
    }
}
