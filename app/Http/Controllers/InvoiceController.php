<?php

namespace App\Http\Controllers;

use App\Enums\InvoiceStatus;
use App\Helpers\SystemHelper;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Finance's invoice register. Invoices are created and changed through the
 * stay folio (BillingService); this is where finance finds and follows them up.
 */
class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(InvoiceStatus::options()))],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $query = Invoice::query()
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->search($term))
            ->when($filters['status'] ?? null, fn ($q, $status) => match ($status) {
                'overdue' => $q->overdue(),
                'sent', 'partial' => $q->where('status', $status)->whereDate('due_date', '>=', today()),
                default => $q->where('status', $status),
            })
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('issue_date', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('issue_date', '<=', $to));

        // Totals for everything the filters match, not just this page
        $live = (clone $query)->where('status', '!=', 'cancelled');
        $stats = [
            'billed' => (float) (clone $live)->sum('total'),
            'collected' => (float) (clone $live)->sum('paid_amount'),
            'outstanding' => (float) (clone $query)->unpaid()->sum('due_amount'),
            'overdue' => (float) (clone $query)->overdue()->sum('due_amount'),
        ];

        $invoices = $query->with('stay.guest')
            ->latest('issue_date')
            ->latest('id')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        return view('invoices.index', compact('invoices', 'stats', 'filters'));
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['items', 'payments.receiver']);
        $stay = $invoice->stay->load(['guest.guestProfile', 'roomAllocations.room', 'invoices.items', 'invoices.payments']);

        return view('invoices.show', compact('invoice', 'stay'));
    }
}
