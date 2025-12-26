<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\EmailHistory;
use App\Models\EmailContent;
use App\Services\GmailService;

class ReminderEmailsController extends Controller
{
    /**
     * Handle the cron job to send MRN reminder emails.
     */
    public function sendMRNReminders(Request $request)
    {

        $workCategoryId = $request->input('work_category');
        $siteId = $request->input('site');
        $buyerId = $request->input('buyer');

        $query = Sale::selectRaw('work_category_id, site_id, buyer_id, COUNT(*) as total_sales, SUM(amount) as total_amount')
            ->groupBy('work_category_id', 'site_id', 'buyer_id')
            ->with(['site:id,name,email_enable'])
            ->where('mrn_reminder', true)
            ->orderByDesc('total_sales')
            ->where(function ($subQuery) {
                $subQuery->orWhere(function ($q) {
                    $q->whereNotNull('lead_delivery_date')
                        ->WhereNull('mrn_date')
                        ->where('payment_checkbox', 0)
                        ->whereRaw("lead_delivery_date < NOW()"); // ✅ Red: lead_delivery_date expired
                });
            });
        $check = 1;
        if ($workCategoryId) {
            $check = 0;
            $query->where('work_category_id', $workCategoryId);
        }
        if ($siteId) {
            $check = 0;
            $query->where('site_id', $siteId);
        }
        if ($buyerId) {
            $check = 0;
            $query->where('buyer_id', $buyerId);
        }

        if ($check == 1) {
            $query->whereHas('site', function ($q) {
                $q->where('email_enable', true);
            });
        }

        $groupedSales = $query->get();

        if ($groupedSales->isEmpty()) {
            return response()->json([
                'message' => 'No MRN Reminders found',
                'status' => 'error',
            ]);
            // return redirect()->back()->with('error', 'No MRN Reminders found');
        }

        foreach ($groupedSales as $group) {
            $salesEntries = Sale::where('work_category_id', $group->work_category_id)
                ->with([
                    'details',
                    'site:id,name',
                    'saleOrder',
                    'billDetails',
                ])
                ->where('site_id', $group->site_id)
                ->where('buyer_id', $group->buyer_id)
                ->where('mrn_reminder', true)
                ->where(function ($query) {
                    $query->orWhere(function ($q) {
                        $q->whereNotNull('lead_delivery_date')
                            ->WhereNull('mrn_date')
                            ->where('payment_checkbox', 0)
                            ->whereRaw("lead_delivery_date < NOW()"); // ✅ Red: lead_delivery_date expired
                    });
                })
                ->get();


            $saleIds = $salesEntries->pluck('id')->sort()->values()->implode(',');

            $emailList = [];
            $ccEmails = [];

            foreach ($salesEntries as $entry) {
                if (!empty($entry->store_email)) {
                    $emailList[] = explode(',', $entry->store_email);
                }
                if (!empty($entry->store_cc_email)) {
                    $ccEmails[] = explode(',', $entry->store_cc_email);
                }
            }

            $ccEmails = array_unique(array_map('trim', array_filter(array_reduce($ccEmails, 'array_merge', []))));
            sort($ccEmails);

            $uniqueEmails = array_unique(array_map('trim', array_filter(array_reduce($emailList, 'array_merge', []))));
            sort($uniqueEmails);
            if (empty($uniqueEmails))
                continue;

            $emailsString = implode(',', $uniqueEmails);

            $ccemailsString = implode(',', $ccEmails);




            $existingHistory = EmailHistory::where('sale_ids', $saleIds)
                ->where('emails', $emailsString)
                ->where('cc_emails', $ccemailsString)
                ->first();
            $reminder_count = 0;
            if ($existingHistory) {

                $existingHistory->increment('send_count');
                $reminder_count = $existingHistory->send_count;

                // $existingContent = EmailContent::where('email_history_id', $existingHistory->id)
                //     ->where('email_content', $emailContent)
                //     ->first();

                // if ($existingContent) {
                // $existingContent->reminder_count += 1;
                // $existingContent->save();
                // view()->share('reminder_count', $existingHistory->send_count);

                // } else {
                // $existingContent = EmailContent::create([
                //     'email_history_id' => $existingHistory->id,
                //     'thread_id' => $existingHistory->thread_id,
                //     'email_content' => $emailContent,
                //     'reminder_count' => 1
                // ]);
                // $reminderText = "";
                // }

                $gmailThreadId = $existingHistory->thread_id;
            } else {
                $reminder_count = 1;
                $gmailThreadId = null;
                $existingHistory = EmailHistory::create([
                    'thread_id' => null,
                    'sale_ids' => $saleIds,
                    'emails' => $emailsString,
                    'cc_emails' => implode(',', $ccEmails),
                    'send_count' => 1
                ]);
                $reminderText = "";
            }
            $emailContent = view("emails.mrn_reminder", compact('salesEntries', 'reminder_count'))->render();
            $updatedEmailContent = $emailContent;
            $doc = [];

            if ($reminder_count == 1) {
                foreach ($salesEntries as $sale) {
                    if (!empty($sale->document)) {
                        $doc[] = public_path('uploads/sale_invoice_document/' . $sale->document);
                    }
                }
            }

            $gmailService = new GmailService();
            $gmailService->setCcEmails($ccEmails)
                ->addAttachments($doc)
                ->setToMails($uniqueEmails)
                ->setThreadId($gmailThreadId);

            $newThreadId = $gmailService->sendEmail("MRN Reminder For {$group?->site?->name} Site", $updatedEmailContent);

            // $newThreadId = $gmailService->getThreadId();

            if (!$gmailThreadId && !empty($newThreadId)) {
                $existingHistory->update(['thread_id' => $newThreadId]);
            }

            EmailContent::create([
                'email_history_id' => $existingHistory->id,
                'thread_id' => $newThreadId,
                'email_content' => $updatedEmailContent,
                'reminder_count' => $existingHistory->send_count ?? 1
            ]);

        }
        return response()->json(['message' => "MRN reminder emails have been scheduled.", 'success' => true]);
        // return $this->withSuccess("MRN reminder emails have been scheduled.");
        // return redirect()->back()->with('success', 'MRN reminder emails have been scheduled.');
    }
    /**
     * Handle the cron job to send Payment reminder emails.
     */
    public function sendPaymentReminders(Request $request)
    {
        $workCategoryId = $request->input('work_category');
        $siteId = $request->input('site');
        $buyerId = $request->input('buyer');

        $query = Sale::selectRaw('work_category_id, site_id, buyer_id, COUNT(*) as total_sales, SUM(amount) as total_amount')
            ->groupBy('work_category_id', 'site_id', 'buyer_id')
            ->with(['site:id,name,email_enable'])
            ->where('payment_reminder', true)
            ->orderByDesc('total_sales')
            ->where(function ($subQuery) {
                $subQuery->orWhere(function ($q) {
                    $q->whereNotNull('mrn_date')
                        ->where('payment_checkbox', 0)
                        ->whereNull('amount')
                        ->whereRaw("DATE_ADD(mrn_date, INTERVAL credited_days DAY) < NOW()"); // ✅ Red: Due date expired
                });
            });

        $check = 1;
        if ($workCategoryId) {
            $check = 0;
            $query->where('work_category_id', $workCategoryId);
        }
        if ($siteId) {
            $check = 0;
            $query->where('site_id', $siteId);
        }
        if ($buyerId) {
            $check = 0;
            $query->where('buyer_id', $buyerId);
        }

        if ($check == 1) {
            $query->whereHas('site', function ($q) {
                $q->where('email_enable', true);
            });
        }



        $groupedSales = $query->get();

        if ($groupedSales->isEmpty()) {
            return redirect()->back()->with('error', 'No Payment Reminders found');
        }

        // echo "<pre>";

        // foreach ($groupedSales as $group) {
        //     $salesEntries = Sale::where('work_category_id', $group->work_category_id)
        //         ->with([
        //             'details',
        //             'site:id,name',
        //             'saleOrder',
        //             'billDetails',
        //         ])
        //         ->where('site_id', $group->site_id)
        //         ->where('buyer_id', $group->buyer_id)
        //         ->where('payment_reminder', true)
        //         ->where(function ($query) {
        //             $query->orWhere(function ($q) {
        //                 $q->whereNotNull('mrn_date')
        //                     ->where('payment_checkbox', 0)
        //                     ->whereRaw("DATE_ADD(mrn_date, INTERVAL credited_days DAY) < NOW()"); // ✅ Red: Due date expired
        //             });
        //         })
        //         ->get();


        //     echo $group->work_category_id . "|" . $group->site_id . "|" . $group->buyer_id . "</br>";

        //     $saleIds = $salesEntries->pluck('id')->sort()->values()->implode(',');

        //     echo $saleIds . "</br><hr>";
        // }
        // exit;

        foreach ($groupedSales as $group) {
            $salesEntries = Sale::where('work_category_id', $group->work_category_id)
                ->with([
                    'details',
                    'site:id,name',
                    'saleOrder',
                    'billDetails',
                ])
                ->where('site_id', $group->site_id)
                ->where('buyer_id', $group->buyer_id)
                ->where('payment_reminder', true)
                ->where(function ($query) {
                    $query->orWhere(function ($q) {
                        $q->whereNotNull('mrn_date')
                            ->where('payment_checkbox', 0)
                            ->whereNull('amount')
                            ->whereRaw("DATE_ADD(mrn_date, INTERVAL credited_days DAY) < NOW()"); // ✅ Red: Due date expired
                    });
                })
                ->get();


            $saleIds = $salesEntries->pluck('id')->sort()->values()->implode(',');

            $emailList = [];
            $ccEmails = [];
            $amount = 0;

            foreach ($salesEntries as $key => $entry) {
                if (!empty($entry->purchase_email)) {
                    $emailList[] = explode(',', $entry->purchase_email);
                }
                if (!empty($entry->purchase_cc_email)) {
                    $ccEmails[] = explode(',', $entry->purchase_cc_email);
                }


                $amount += $entry->details->sum('amount') + $entry->billDetails->sum('amount');
                $salesEntries[$key]['amount'] = $entry->details->sum('amount') + $entry->billDetails->sum('amount');
            }

            $ccEmails = array_unique(array_map('trim', array_filter(array_reduce($ccEmails, 'array_merge', []))));
            sort($ccEmails);

            $uniqueEmails = array_unique(array_map('trim', array_filter(array_reduce($emailList, 'array_merge', []))));

            sort($uniqueEmails);
            if (empty($uniqueEmails))
                continue;

            $emailsString = implode(',', $uniqueEmails);
            $ccemailsString = implode(',', $ccEmails);


            $existingHistory = (new EmailHistory)
                ->setTable('payment_emails')
                ->where('sale_ids', $saleIds)
                ->where('emails', $emailsString)
                ->where('cc_emails', $ccemailsString)
                ->first();
            $reminder_count = 0;
            if ($existingHistory) {

                $existingHistory->increment('send_count');
                $reminder_count = $existingHistory->send_count;
                // $existingContent = (new EmailContent)->setTable('payment_contents')->where('payment_email_id', $existingHistory->id)
                //     ->where('email_content', $emailContent)
                //     ->first();

                // // if ($existingContent) {
                //     $existingContent->reminder_count += 1;
                //     $existingContent->save();
                // view()->share('reminder_count', $existingHistory->send_count);
                // // } else {
                // $existingContent = (new EmailContent)->setTable('payment_contents')->create([
                //     'payment_email_id' => $existingHistory->id,
                //     'thread_id' => $existingHistory->thread_id,
                //     'email_content' => $emailContent,
                //     'reminder_count' => 1
                // ]);
                // $reminderText = "";
                // }

                $gmailThreadId = $existingHistory->thread_id;
            } else {
                $reminder_count = 1;
                $gmailThreadId = null;
                $existingHistory = (new EmailHistory)
                    ->setTable('payment_emails')
                    ->create([
                        'thread_id' => null,
                        'sale_ids' => $saleIds,
                        'emails' => $emailsString,
                        'cc_emails' => implode(',', $ccEmails),
                        'send_count' => 1
                    ]);
                $reminderText = "";
            }
            // return view("emails.payment_reminder", compact('salesEntries'));

            $emailContent = view("emails.payment_reminder", compact('salesEntries', 'reminder_count'))->render();
            $updatedEmailContent = $emailContent;

            $amount = sprintf("%.1f", ($amount / 100000));
            ;

            $gmailService = new GmailService();
            $gmailService->setCcEmails($ccEmails)
                ->setToMails($uniqueEmails)
                ->setThreadId($gmailThreadId);

            $newThreadId = $gmailService->sendEmail("REQUEST TO RELEASE OVERDUE PAYMENT OF APPROX {$amount} LAKHS OF M/S PARSHVA BUILDTECH LLP", $updatedEmailContent);
            // $newThreadId = "11";
            // $newThreadId = $gmailService->getThreadId();

            if (!$gmailThreadId && !empty($newThreadId)) {
                $existingHistory->update(['thread_id' => $newThreadId]);
            }

            (new EmailContent)->setTable('payment_contents')
                ->create([
                    'payment_email_id' => $existingHistory->id,
                    'thread_id' => $newThreadId,
                    'email_content' => $updatedEmailContent,
                    'reminder_count' => $existingHistory->send_count ?? 1
                ]);
            // dd( $newThreadId );
        }
        return $this->withSuccess("Payment reminder emails have been scheduled.");
        // return redirect()->back()->with('success', 'Payment reminder emails have been scheduled.');
    }
}


