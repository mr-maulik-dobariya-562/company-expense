<?php

namespace App\Http\Controllers;

use App\Models\IPAddress;
use App\Models\LogActivity;
use App\Models\User;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class Log_activityController extends Controller implements HasMiddleware
{
    use DataTable;
    public static function middleware(): array
    {
        return [
            new Middleware('permission:log-activity-view', only: ['index', "getList"]),
        ];
    }


    public function index()
    {
        $users = User::get();
        return view('log_activity.index', compact('users'));
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',

            'vch_no',
            'subject',
        ];

        $this->model(model: LogActivity::class);

        $this->filter([
            "user_id" => $request->user_id ?? '',
        ]);

        $this->enableDateFilters();

        $this->formateArray(function ($row, $index) use ($request) {
            $isBlocked = !isset($row->ip) || !IPAddress::where('name', $row->ip)->exists();
            $rowClass = $isBlocked ? 'background-color:#ffc2c2; color: black;' : '';

            return [
                "id" => $row->id,
                'subject' => $row->subject,
                'url' => $row->url,
                'method' => $row->method,
                'ip' => $row->ip,
                'agent' => $row->agent,
                'table-id' => $row->table_id,
                'vch_No' => $row->vch_no,
                'agent_view' => '<button class="btn btn-info view-btn"><i class="fas fa-eye"></i> View</button>',
                "created_by" => $row->createdBy?->name,
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "DT_RowAttr" => ["style" => $rowClass],
            ];
        });
        return $this->getListAjax($searchableColumns);
    }
}
