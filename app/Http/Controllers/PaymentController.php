<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Collections register: every payment taken, with totals per method for the
 * end-of-day cash-up. Payments are taken and voided on the stay folio.
 */
class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'method' => ['nullable', Rule::in(array_keys(Payment::METHODS))],
            'status' => ['nullable', Rule::in(['completed', 'cancelled'])],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        // Default to today's takings
        $from = Carbon::parse($filters['from'] ?? today())->startOfDay();
        $to = Carbon::parse($filters['to'] ?? $from)->endOfDay();

        $query = Payment::query()
            ->whereBetween('received_at', [$from, $to])
            ->when($filters['method'] ?? null, fn ($q, $method) => $q->where('method', $method))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($s) => $s
                ->where('payment_reference', 'like', "%{$term}%")
                ->orWhereHas('invoice', fn ($i) => $i->search($term))));

        $completed = (clone $query)->where('status', 'completed');
        $byMethod = (clone $completed)
            ->selectRaw('method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('method')
            ->get()
            ->keyBy('method');

        $stats = [
            'total' => (float) $byMethod->sum('total'),
            'count' => (int) $byMethod->sum('count'),
            'voided' => (float) (clone $query)->where('status', 'cancelled')->sum('amount'),
        ];

        $payments = $query
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->with(['invoice.stay.guest', 'invoice.facilityBooking', 'receiver'])
            ->latest('received_at')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        return view('payments.index', compact('payments', 'stats', 'byMethod', 'filters', 'from', 'to'));
    }
}
