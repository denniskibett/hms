<?php

namespace App\Http\Controllers;

use App\Exceptions\BillingException;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Company accounts (city ledger): profiles, negotiated rate, credit limit and
 * terms; the statement of invoices billed to the company; and payments that
 * settle its oldest invoices first.
 */
class CompanyController extends Controller
{
    public function __construct(private BillingService $billing) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $companies = Company::query()
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('kra_pin', 'like', "%{$search}%")->orWhere('contact_name', 'like', "%{$search}%")))
            ->orderByDesc('is_active')->orderBy('name')
            ->paginate(25)->withQueryString();

        $balances = Invoice::unpaid()->whereIn('company_id', $companies->pluck('id'))->get()
            ->groupBy('company_id')->map(fn ($rows) => round($rows->sum(fn (Invoice $i) => $i->balance), 2));

        return view('companies.index', compact('companies', 'balances', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = Company::create($this->validated($request));

        return redirect()->route('companies.show', $company)->with('success', "{$company->name} added.");
    }

    public function show(Company $company): View
    {
        $invoices = $company->invoices()->with(['stay.guest', 'facilityBooking', 'payments'])
            ->where('status', '!=', 'cancelled')->orderByDesc('issue_date')->orderByDesc('id')->paginate(30);

        return view('companies.show', [
            'company' => $company,
            'invoices' => $invoices,
            'balance' => $company->balance(),
            'ageing' => $company->ageing(),
            'methods' => Payment::METHODS,
        ]);
    }

    public function statement(Company $company): View
    {
        return view('companies.statement', [
            'company' => $company,
            'invoices' => $company->invoices()->unpaid()->with('stay.guest', 'facilityBooking')->orderBy('due_date')->get(),
            'balance' => $company->balance(),
            'ageing' => $company->ageing(),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $company->update($this->validated($request));

        return back()->with('success', "{$company->name} saved.");
    }

    /**
     * One payment from the company, applied to its oldest open invoices first.
     */
    public function pay(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'payment_reference' => ['nullable', 'string', 'max:40', 'required_unless:method,cash'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], ['payment_reference.required_unless' => 'Enter the cheque, transfer or M-Pesa reference.']);

        $left = round((float) $data['amount'], 2);
        if ($left > $company->balance()) {
            return back()->withInput()->with('error', 'That is more than the company owes (' . number_format($company->balance(), 2) . ').');
        }

        try {
            DB::transaction(function () use ($company, $data, &$left, $request) {
                $part = 0;
                foreach ($company->invoices()->unpaid()->orderBy('due_date')->orderBy('id')->get() as $invoice) {
                    if ($left <= 0) {
                        break;
                    }
                    $amount = min($left, $invoice->balance);
                    $part++;
                    $this->billing->recordPayment($invoice, [
                        'amount' => $amount,
                        'method' => $data['method'],
                        // Each invoice needs its own unique reference
                        'payment_reference' => filled($data['payment_reference'] ?? null) ? $data['payment_reference'] . ($part > 1 ? '/' . $part : '') : null,
                        'notes' => trim('Company payment from ' . $company->name . '. ' . ($data['notes'] ?? '')),
                    ], $request->user());
                    $left = round($left - $amount, 2);
                }
            });
        } catch (BillingException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Payment of ' . number_format((float) $data['amount'], 2) . " recorded against {$company->name}'s oldest invoices.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'max:20'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'credit_limit' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'payment_terms_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'rate_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:90'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return array_merge($data, [
            'credit_limit' => $data['credit_limit'] ?? 0,
            'payment_terms_days' => $data['payment_terms_days'] ?? 30,
            'rate_discount_percent' => $data['rate_discount_percent'] ?? 0,
            'kra_pin' => isset($data['kra_pin']) ? strtoupper(trim($data['kra_pin'])) : null,
            'is_active' => $request->boolean('is_active', true),
        ]);
    }
}
