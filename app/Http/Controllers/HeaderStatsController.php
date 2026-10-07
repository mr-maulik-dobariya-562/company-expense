<?php

namespace App\Http\Controllers;

use App\Services\SettlementService;
use Illuminate\Http\Request;

/**
 * Small JSON endpoint for the money pills in the top bar (loaded via AJAX on every page).
 */
class HeaderStatsController extends Controller
{
    public function __invoke(Request $request, SettlementService $settlements)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $funds = $settlements->getCompanyFundSummary();

            $stats = [
                ['key' => 'balance', 'label' => 'Company balance', 'value' => $funds['available_balance'], 'tone' => $funds['available_balance'] < 0 ? 'danger' : 'success', 'icon' => 'fas fa-piggy-bank'],
                ['key' => 'pending', 'label' => 'Pending payable', 'value' => $settlements->getTotalPendingEmployeePayable(), 'tone' => 'warning', 'icon' => 'fas fa-hourglass-half'],
            ];
        } else {
            $outstanding = $settlements->getEmployeeOutstanding($user->id);

            $stats = [
                ['key' => 'pending', 'label' => 'Pending receivable', 'value' => $outstanding['pending_receivable'], 'tone' => $outstanding['pending_receivable'] > 0 ? 'warning' : 'success', 'icon' => 'fas fa-hourglass-half'],
            ];
        }

        return response()->json(['stats' => $stats])->header('Cache-Control', 'no-store');
    }
}
