<?php

namespace App\Console\Commands;

use AWS\CRT\HTTP\Request;
use Illuminate\Console\Command;
use App\Http\Controllers\ReminderEmailsController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendMRNReminderEmails extends Command
{
    protected $signature = 'send:mrn-reminders';
    protected $description = 'Send MRN reminders based on the schedule stored in DB';

    public function handle()
    {
        // Get current day and time
        $currentDay = Carbon::now()->format('l'); // "Tuesday"
        $currentTime = Carbon::now()->format('H:i'); // "10:00"
      
 		Log::info("currentDay : $currentDay");
      	Log::info("currentTime : $currentTime");
      
      
        // Fetch records from DB where the reminder matches the current day and time
        $email_configurations = DB::table('email_configurations')
            ->whereJsonContains('mrn_reminder_day->' . $currentDay . '->day', $currentDay)
            ->whereJsonContains('mrn_reminder_day->' . $currentDay . '->time', $currentTime)
            ->where('email_enable', true)
            ->get();

        $controller = (new ReminderEmailsController());

        if ($email_configurations->count() > 0) {
            $controller->sendMRNReminders(request());
        }

        $email_configurations = DB::table('email_configurations')
            ->whereJsonContains('payment_reminder_day->' . $currentDay . '->day', $currentDay)
            ->whereJsonContains('payment_reminder_day->' . $currentDay . '->time', $currentTime)
            ->where('email_enable', true)
            ->get();

        if ($email_configurations->count() > 0) {
            $controller->sendPaymentReminders(request());
        }

        $this->info("MRN Reminders sent for $currentDay at $currentTime");
    }
}
