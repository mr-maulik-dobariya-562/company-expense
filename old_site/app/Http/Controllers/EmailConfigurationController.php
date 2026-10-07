<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailConfiguration;
use App\Services\GmailService;
use App\Traits\DataTable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmailConfigurationController extends Controller implements HasMiddleware
{
    use DataTable;
    private $gmailService;

    public static function middleware(): array
    {
        return [
            new Middleware('permission:email-configuration-create', only: ['create']),
            new Middleware('permission:email-configuration-view', only: ['index', "getList"]),
            new Middleware('permission:email-configuration-edit', only: ['edit', "update"]),
            new Middleware('permission:email-configuration-delete', only: ['destroy']),
        ];
    }

    // public function __construct(GmailService $gmailService)
    // {
    //     $this->gmailService = $gmailService;
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = EmailConfiguration::first();
        return view('email_configuration.index', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "mrn_reminder_day" => "required|array",
            "mrn_reminder_day.*.day" => "required|string",
            "mrn_reminder_day.*.time" => "required|string",
            "payment_reminder_day" => "required|array",
            "payment_reminder_day.*.day" => "required|string",
            "payment_reminder_day.*.time" => "required|string",
        ]);

        $data = EmailConfiguration::find($request->id ?? null);

        $mrnReminderDays = json_encode($request->mrn_reminder_day);
        $paymentReminderDays = json_encode($request->payment_reminder_day);

        $updateData = [
            "mrn_reminder_day" => $mrnReminderDays,
            "payment_reminder_day" => $paymentReminderDays,
            "created_by" => auth()->id(),
        ];

        if ($data) {
            $data->update($updateData);
        } else {
            EmailConfiguration::create($updateData);
        }


        if ($request->ajax()) {
            return $this->withSuccess("Email Configuration created successfully");
        }
        return $this->withSuccess("Email Configuration created successfully")->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, EmailConfiguration $emailConfiguration)
    {
        $request->validate([
            // "name"       => "required|unique:buyers,name," . $buyer->id,
            "email" => "required|email|unique:email_configurations,email," . $emailConfiguration->id,
            "password" => "required",
            "port_no" => "required|numeric",
            "host" => "required",
            "mailer" => "required",
            "encryption" => "required"

        ]);

        $emailConfiguration->update([
            "email" => $request->email,
            "password" => $request->password,
            "port_no" => $request->port_no,
            "host" => $request->host,
            "mailer" => $request->mailer,
            "encryption" => $request->encryption
        ]);

        if ($request->ajax()) {
            return $this->withSuccess("Email Configuration Updated successfully");
        }
        return $this->withSuccess("Email Configuration Updated successfully")->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmailConfiguration $emailConfiguration)
    {
        $emailConfiguration->delete();
        if (request()->ajax()) {
            return $this->withSuccess("Email Configuration Deleted successfully");
        }
        return $this->withSuccess("Email Configuration Deleted successfully")->back();
    }

    public function getList(Request $request)
    {
        $searchableColumns = [
            'id',
            'email',
            'port_no',
            'host',
            'mailer',
            'encryption'
        ];

        $this->model(model: EmailConfiguration::class);


        $editPermission = $this->hasPermission("email-configuration-edit");
        $deletePermission = $this->hasPermission("email-configuration-delete");

        $this->formateArray(function ($row, $index) use ($editPermission, $deletePermission) {
            $delete = route("email-configuration.delete", ['emailConfiguration' => $row->id]);
            $action = "";

            if ($editPermission) {
                $action .= "
                            <a class='btn edit-btn  btn-action bg-success text-white me-2'
                                data-id='{$row->id}'
                                data-email='{$row->email}'
                                data-port_no='{$row->port_no}',
                                data-host='{$row->host}',
                                data-mailer='{$row->mailer}',
                                data-encryption='{$row->encryption}',
                                data-password='{$row->password}'
                                data-bs-toggle='tooltip' data-bs-placement='top' data-bs-original-title='Edit' href='javascript:void(0);'>
                                <i class='far fa-edit' aria-hidden='true'></i>
                            </a>
                        ";
            }
            if ($deletePermission) {
                $action .= "
                            <a class='btn btn-action bg-danger text-white me-2 btn-delete'
                                data-id='{$row->id}'
                                data-bs-toggle='tooltip'
                                data-bs-placement='top' data-bs-original-title='Delete'
                                href='{$delete}'>
                                <i class='fas fa-trash'></i>
                            </a>
                        ";
            }

            return [
                "id" => $row->id,
                "email" => $row->email,
                "password" => $row->password,
                "port_no" => $row->port_no,
                "host" => $row->host,
                "mailer" => $row->mailer,
                "encryption" => $row->encryption,
                "action" => $action,
                "created_by" => $row->createdBy?->displayName(),
                "created_at" => $row->created_at ? $row->created_at->format('d/m/Y H:i:s') : '',
                "updated_at" => $row->updated_at ? $row->updated_at->format('d/m/Y H:i:s') : '',
            ];
        });
        return $this->getListAjax($searchableColumns);
    }

    public function sendEmail(Request $request)
    {
        $recipients = ['pcpatel006gmail.com']; // Array of emails
        $subject = 'Testing Email';
        $messageText = 'Testing Message';

        if (!is_array($recipients) || count($recipients) < 1) {
            return response()->json(['error' => 'Recipients must be an array with at least one email'], 400);
        }

        $response = $this->gmailService->sendEmail($recipients, $subject, $messageText);

        return response()->json($response);
    }

    public function emailEnable(Request $request)
    {
        $config = EmailConfiguration::find($request->id);

        $config->update([
            "email_enable" => $request->value == 1 ? true : false,
        ]);

        return response()->json(['success' => true, 'message' => 'Global Email Configuration Updated.']);
    }
}

