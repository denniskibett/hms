<?php

namespace App\Http\Controllers;

use App\Enums\StayStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\View\View;

/**
 * Finance overview: takings, what's owed, and tax for the month.
 */
class FinanceController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth();
        $collected = Payment::where('status', 'completed');
        $issuedThisMonth = Invoice::where('status', '!=', 'cancelled')->whereDate('issue_date', '>=', $monthStart);

        $stats = [
            'collected_today' => (float) (clone $collected)->whereDate('received_at', today())->sum('amount'),
            'collected_month' => (float) (clone $collected)->where('received_at', '>=', $monthStart)->sum('amount'),
            'outstanding' => (float) Invoice::unpaid()->sum('due_amount'),
            'overdue' => (float) Invoice::overdue()->sum('due_amount'),
            'in_house' => (float) Invoice::unpaid()->whereHas('stay', fn ($q) => $q->inHouse())->sum('due_amount'),
        ];

        $tax = [
            'billed' => (float) (clone $issuedThisMonth)->sum('total'),
            'vat' => (float) (clone $issuedThisMonth)->sum('vat_amount'),
            'levy' => (float) (clone $issuedThisMonth)->sum('levy_amount'),
        ];

        $byMethod = (clone $collected)
            ->where('received_at', '>=', $monthStart)
            ->selectRaw('method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        // Guests who have left (or were due to) without settling
        $overdue = Invoice::overdue()
            ->whereHas('stay', fn ($q) => $q->where('status', '!=', StayStatus::CheckedIn->value))
            ->with('stay.guest')
            ->orderBy('due_date')
            ->take(10)
            ->get();

        $recentPayments = Payment::with(['invoice.stay.guest', 'receiver'])
            ->latest('received_at')
            ->take(8)
            ->get();

        return view('finance.index', compact('stats', 'tax', 'byMethod', 'overdue', 'recentPayments'));
    }
}
