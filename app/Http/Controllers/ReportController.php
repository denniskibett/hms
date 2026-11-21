<?php

namespace App\Http\Controllers;

use App\Enums\FacilityBookingStatus;
use App\Enums\KitchenOrderStatus;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Management reports. Each report builds the same shape so one page can show,
 * print and export any of them:
 *   cards:    [[label, value, type]]
 *   sections: [[title, columns: [[label, type]], rows: [[...]], footer: [...]|null, note?]]
 * type is text | money | number | percent | date.
 */
class ReportController extends Controller
{
    public const REPORTS = [
        'revenue' => ['Revenue', 'What was billed, by source, and what was collected, by payment method', 'fa-coins'],
        'occupancy' => ['Occupancy', 'Rooms sold, occupancy, average rate (ADR) and revenue per available room (RevPAR)', 'fa-bed'],
        'tax' => ['VAT & Tourism Levy', 'Tax in each invoice, for the KRA returns', 'fa-landmark'],
        'debtors' => ['Debtors', 'Unpaid invoices by how long they have been owed', 'fa-hand-holding-usd'],
        'restaurant' => ['Restaurant', 'Best sellers and sales by order type', 'fa-utensils'],
        'events' => ['Conferences & Events', 'Events and revenue by venue', 'fa-calendar-alt'],
        'payroll' => ['Payroll & Statutory', 'Pay and the PAYE, NSSF, SHA and Housing Levy to remit', 'fa-money-check-alt'],
        'stock' => ['Stock', 'Purchases, usage by department, write-offs and stock value', 'fa-boxes'],
        'expenses' => ['Expenses', 'Money paid out by kind, supplier and month, and what is owed to suppliers', 'fa-receipt'],
    ];

    /**
     * Reports that are a snapshot today and take no dates.
     */
    private const SNAPSHOT = ['debtors'];

    public function index(): View
    {
        return view('reports.index', ['reports' => self::REPORTS]);
    }

    public function show(Request $request, string $report): View|StreamedResponse
    {
        abort_unless(isset(self::REPORTS[$report]), 404);

        $range = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $from = Carbon::parse($range['from'] ?? now()->startOfMonth())->startOfDay();
        $to = Carbon::parse($range['to'] ?? today())->startOfDay();
        if ($from->diffInDays($to) > 400) {
            $from = $to->copy()->subDays(400);
        }

        $data = $this->{$report}($from, $to);
        [$title, $description] = self::REPORTS[$report];
        $snapshot = in_array($report, self::SNAPSHOT, true);

        if ($request->query('export') === 'csv') {
            return $this->csv($report, $title, $from, $to, $snapshot, $data);
        }

        return view('reports.show', compact('report', 'title', 'description', 'from', 'to', 'snapshot', 'data'));
    }

    // ---- Reports -----------------------------------------------------------

    private function revenue(Carbon $from, Carbon $to): array
    {
        $invoices = Invoice::where('status', '!=', 'cancelled')->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()]);
        $sources = ['room' => 'Rooms', 'service' => 'Services', 'food' => 'Food & drinks', 'facility' => 'Conferences & events', 'other' => 'Extra charges'];

        $bySource = DB::table('invoice_items')
            ->joinSub((clone $invoices)->select('id'), 'i', 'i.id', '=', 'invoice_items.invoice_id')
            ->selectRaw('source_type, SUM(quantity * unit_price) as amount')
            ->groupBy('source_type')
            ->pluck('amount', 'source_type');

        $discounts = (float) (clone $invoices)->sum('discount_amount');
        $billed = (float) (clone $invoices)->sum('total');
        $rows = collect($sources)->map(fn ($label, $key) => [$label, (float) ($bySource[$key] ?? 0)])->values()->all();
        if ($discounts > 0) {
            $rows[] = ['Less discounts', -$discounts];
        }

        $payments = Payment::where('status', 'completed')->whereBetween('received_at', [$from, $to->copy()->endOfDay()]);
        $byMethod = (clone $payments)->selectRaw('method, COUNT(*) as n, SUM(amount) as total')->groupBy('method')->orderByDesc('total')->get();
        $collected = (float) $byMethod->sum('total');

        $billedByDay = (clone $invoices)->selectRaw('DATE(issue_date) as d, SUM(total) as t')->groupBy('d')->pluck('t', 'd');
        $collectedByDay = (clone $payments)->selectRaw('DATE(received_at) as d, SUM(amount) as t')->groupBy('d')->pluck('t', 'd');
        $daily = collect(CarbonPeriod::create($from, $to))->map(fn ($day) => [
            $day->toDateString(),
            (float) ($billedByDay[$day->toDateString()] ?? 0),
            (float) ($collectedByDay[$day->toDateString()] ?? 0),
        ])->all();

        return [
            'cards' => [
                ['Billed (incl. tax)', $billed, 'money'],
                ['Collected', $collected, 'money'],
                ['VAT in billing', (float) (clone $invoices)->sum('vat_amount'), 'money'],
                ['Tourism Levy in billing', (float) (clone $invoices)->sum('levy_amount'), 'money'],
            ],
            'sections' => [
                [
                    'title' => 'Billed by source',
                    'columns' => [['Source', 'text'], ['Amount', 'money']],
                    'rows' => $rows,
                    'footer' => ['Total billed', $billed],
                    'note' => 'By invoice date. A stay is invoiced when it is booked; prices include VAT and Tourism Levy.',
                ],
                [
                    'title' => 'Collected by payment method',
                    'columns' => [['Method', 'text'], ['Payments', 'number'], ['Amount', 'money']],
                    'rows' => $byMethod->map(fn ($m) => [Payment::METHODS[$m->method] ?? $m->method, (int) $m->n, (float) $m->total])->all(),
                    'footer' => ['Total collected', (int) $byMethod->sum('n'), $collected],
                ],
                [
                    'title' => 'By day',
                    'columns' => [['Date', 'date'], ['Billed', 'money'], ['Collected', 'money']],
                    'rows' => $daily,
                    'footer' => ['Total', $billed, $collected],
                ],
            ],
        ];
    }

    private function occupancy(Carbon $from, Carbon $to): array
    {
        $rooms = Room::with('roomType:id,name')->get(['id', 'room_type_id']);
        $roomCount = $rooms->count();

        // Each allocation night inside the range, from bookings that weren't cancelled
        $allocations = DB::table('room_allocations as a')
            ->join('stays as s', 's.id', '=', 'a.stay_id')
            ->whereNull('s.deleted_at')
            ->whereIn('s.status', ['reserved', 'booked', 'checked_in', 'checked_out'])
            ->where('a.from_date', '<=', $to->toDateString())
            ->where('a.to_date', '>', $from->toDateString())
            ->get(['a.room_id', 'a.from_date', 'a.to_date', 'a.rate_applied']);

        $nights = collect(CarbonPeriod::create($from, $to))->mapWithKeys(fn ($d) => [$d->toDateString() => ['sold' => 0, 'revenue' => 0.0]])->all();
        $byType = [];
        $typeOf = $rooms->pluck('roomType.name', 'id');

        foreach ($allocations as $a) {
            $night = Carbon::parse($a->from_date)->max($from);
            $end = Carbon::parse($a->to_date)->min($to->copy()->addDay());
            for (; $night->lt($end); $night->addDay()) {
                $key = $night->toDateString();
                $nights[$key]['sold']++;
                $nights[$key]['revenue'] += (float) $a->rate_applied;
                $type = $typeOf[$a->room_id] ?? 'Other';
                $byType[$type]['sold'] = ($byType[$type]['sold'] ?? 0) + 1;
                $byType[$type]['revenue'] = ($byType[$type]['revenue'] ?? 0) + (float) $a->rate_applied;
            }
        }

        $days = count($nights);
        $sold = array_sum(array_column($nights, 'sold'));
        $revenue = array_sum(array_column($nights, 'revenue'));
        $available = $roomCount * $days;
        $pct = fn ($part, $whole) => $whole > 0 ? round($part / $whole * 100, 1) : 0;

        $typeRooms = $rooms->groupBy(fn ($r) => $r->roomType->name ?? 'Other')->map->count();

        return [
            'cards' => [
                ['Occupancy', $pct($sold, $available), 'percent'],
                ['Room nights sold', $sold, 'number'],
                ['Average daily rate (ADR)', $sold ? round($revenue / $sold, 2) : 0, 'money'],
                ['RevPAR', $available ? round($revenue / $available, 2) : 0, 'money'],
            ],
            'sections' => [
                [
                    'title' => 'By room type',
                    'columns' => [['Room type', 'text'], ['Rooms', 'number'], ['Nights sold', 'number'], ['Occupancy', 'percent'], ['Room revenue', 'money'], ['ADR', 'money']],
                    'rows' => $typeRooms->map(fn ($count, $type) => [
                        $type, $count, $byType[$type]['sold'] ?? 0, $pct($byType[$type]['sold'] ?? 0, $count * $days),
                        round($byType[$type]['revenue'] ?? 0, 2), ($byType[$type]['sold'] ?? 0) ? round($byType[$type]['revenue'] / $byType[$type]['sold'], 2) : 0,
                    ])->values()->all(),
                    'footer' => ['All rooms', $roomCount, $sold, $pct($sold, $available), round($revenue, 2), $sold ? round($revenue / $sold, 2) : 0],
                    'note' => 'Room revenue is the nightly room rate (incl. tax) of rooms sold; meals on the room rate are included.',
                ],
                [
                    'title' => 'By night',
                    'columns' => [['Night of', 'date'], ['Rooms sold', 'number'], ['Occupancy', 'percent'], ['Room revenue', 'money']],
                    'rows' => collect($nights)->map(fn ($n, $date) => [$date, $n['sold'], $pct($n['sold'], $roomCount), round($n['revenue'], 2)])->values()->all(),
                    'footer' => null,
                ],
            ],
        ];
    }

    private function tax(Carbon $from, Carbon $to): array
    {
        $invoices = Invoice::with(['stay.guest', 'facilityBooking', 'kitchenOrder'])
            ->where('status', '!=', 'cancelled')
            ->whereBetween('issue_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('issue_date')
            ->orderBy('id')
            ->get();

        $sum = fn ($field) => round((float) $invoices->sum($field), 2);

        return [
            'cards' => [
                ['Sales (incl. tax)', $sum('total'), 'money'],
                ['Net of tax', $sum('subtotal'), 'money'],
                ['VAT', $sum('vat_amount'), 'money'],
                ['Tourism Levy', $sum('levy_amount'), 'money'],
            ],
            'sections' => [[
                'title' => 'Invoices',
                'columns' => [['Invoice', 'text'], ['Date', 'date'], ['Billed to', 'text'], ['Total', 'money'], ['Net', 'money'], ['VAT', 'money'], ['Levy', 'money']],
                'rows' => $invoices->map(fn (Invoice $i) => [
                    $i->invoice_number, $i->issue_date->toDateString(), $i->billed_to ?? '—',
                    (float) $i->total, (float) $i->subtotal, (float) $i->vat_amount, (float) $i->levy_amount,
                ])->all(),
                'footer' => ['Total', '', '', $sum('total'), $sum('subtotal'), $sum('vat_amount'), $sum('levy_amount')],
                'note' => 'Uses the VAT and levy rates saved on each invoice. Check the figures with your accountant before filing.',
            ]],
        ];
    }

    private function debtors(Carbon $from, Carbon $to): array
    {
        $invoices = Invoice::unpaid()->with(['stay.guest', 'facilityBooking', 'kitchenOrder'])->orderBy('due_date')->get();
        $buckets = ['Not yet due' => 0.0, '1–30 days' => 0.0, '31–60 days' => 0.0, '61–90 days' => 0.0, 'Over 90 days' => 0.0];
        $bucketOf = function (Invoice $i) {
            $late = $i->due_date && $i->due_date->isPast() ? (int) $i->due_date->diffInDays(today()) : 0;

            return match (true) {
                $late <= 0 => 'Not yet due',
                $late <= 30 => '1–30 days',
                $late <= 60 => '31–60 days',
                $late <= 90 => '61–90 days',
                default => 'Over 90 days',
            };
        };

        $rows = $invoices->map(function (Invoice $i) use (&$buckets, $bucketOf) {
            $bucket = $bucketOf($i);
            $buckets[$bucket] += $i->balance;

            return [$i->invoice_number, $i->billed_to ?? '—', $i->source_label, $i->due_date?->toDateString(), $bucket, (float) $i->total, $i->balance];
        })->all();

        $owed = round(array_sum($buckets), 2);

        return [
            'cards' => [
                ['Owed to the hotel', $owed, 'money'],
                ['Overdue', round($owed - $buckets['Not yet due'], 2), 'money'],
                ['Over 60 days', round($buckets['61–90 days'] + $buckets['Over 90 days'], 2), 'money'],
                ['Unpaid invoices', count($rows), 'number'],
            ],
            'sections' => [
                [
                    'title' => 'By age',
                    'columns' => [['How late', 'text'], ['Owed', 'money']],
                    'rows' => collect($buckets)->map(fn ($v, $k) => [$k, round($v, 2)])->values()->all(),
                    'footer' => ['Total', $owed],
                ],
                [
                    'title' => 'Unpaid invoices',
                    'columns' => [['Invoice', 'text'], ['Billed to', 'text'], ['For', 'text'], ['Due', 'date'], ['How late', 'text'], ['Total', 'money'], ['Balance', 'money']],
                    'rows' => $rows,
                    'footer' => ['Total', '', '', '', '', round((float) $invoices->sum('total'), 2), $owed],
                    'note' => 'Guests still in house appear as not yet due until their departure date.',
                ],
            ],
        ];
    }

    private function restaurant(Carbon $from, Carbon $to): array
    {
        $lines = DB::table('kitchen_order_items as i')
            ->join('kitchen_orders as o', 'o.id', '=', 'i.order_id')
            ->join('menu_item_variants as v', 'v.id', '=', 'i.menu_item_variant_id')
            ->join('menu_items as m', 'm.id', '=', 'v.menu_item_id')
            ->leftJoin('menu_categories as c', 'c.id', '=', 'm.category_id')
            ->where('o.status', '!=', KitchenOrderStatus::Cancelled->value)
            ->whereBetween('o.created_at', [$from, $to->copy()->endOfDay()]);

        $dishes = (clone $lines)
            ->selectRaw("m.name as dish, v.name as variant_name, c.name as category, SUM(i.quantity) as qty, SUM(i.quantity * i.price_at_order) as sales")
            ->groupBy('m.name', 'v.name', 'c.name')
            ->orderByDesc('sales')
            ->get();

        $byType = DB::table('kitchen_orders as o')
            ->join('kitchen_order_items as i', 'i.order_id', '=', 'o.id')
            ->where('o.status', '!=', KitchenOrderStatus::Cancelled->value)
            ->whereBetween('o.created_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw('o.type, o.charge_to_room, COUNT(DISTINCT o.id) as orders, SUM(i.quantity * i.price_at_order) as sales')
            ->groupBy('o.type', 'o.charge_to_room')
            ->get();

        $sales = round((float) $dishes->sum('sales'), 2);
        $orders = (int) $byType->sum('orders');
        $types = \App\Models\KitchenOrder::TYPES;

        return [
            'cards' => [
                ['Sales (incl. tax)', $sales, 'money'],
                ['Orders', $orders, 'number'],
                ['Average order', $orders ? round($sales / $orders, 2) : 0, 'money'],
                ['Charged to rooms', round((float) $byType->where('charge_to_room', 1)->sum('sales'), 2), 'money'],
            ],
            'sections' => [
                [
                    'title' => 'By order type',
                    'columns' => [['Type', 'text'], ['Orders', 'number'], ['Sales', 'money']],
                    'rows' => $byType->map(fn ($t) => [($types[$t->type] ?? $t->type) . ($t->charge_to_room ? ' (room charge)' : ''), (int) $t->orders, (float) $t->sales])->all(),
                    'footer' => ['Total', $orders, $sales],
                ],
                [
                    'title' => 'Dishes',
                    'columns' => [['Dish', 'text'], ['Category', 'text'], ['Sold', 'number'], ['Sales', 'money'], ['Share', 'percent']],
                    'rows' => $dishes->map(fn ($d) => [
                        $d->dish . (in_array(strtolower($d->variant_name), ['regular', 'standard'], true) ? '' : " ({$d->variant_name})"),
                        $d->category ?? '—', (int) $d->qty, (float) $d->sales, $sales ? round($d->sales / $sales * 100, 1) : 0,
                    ])->all(),
                    'footer' => ['Total', '', (int) $dishes->sum('qty'), $sales, 100],
                ],
            ],
        ];
    }

    private function events(Carbon $from, Carbon $to): array
    {
        $events = \App\Models\FacilityBooking::with(['facility:id,name', 'items', 'invoices' => fn ($q) => $q->where('status', '!=', 'cancelled')])
            ->whereIn('status', [FacilityBookingStatus::Confirmed->value, FacilityBookingStatus::Completed->value, FacilityBookingStatus::Tentative->value])
            ->whereBetween('start_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('start_date')
            ->get();

        $confirmed = $events->where('status', '!=', FacilityBookingStatus::Tentative);
        $value = fn ($e) => (float) ($e->invoices->sortBy('id')->first()?->total ?? $e->total);
        $byVenue = $confirmed->groupBy(fn ($e) => $e->facility->name ?? '—');

        return [
            'cards' => [
                ['Confirmed events', $confirmed->count(), 'number'],
                ['Revenue (confirmed)', round($confirmed->sum($value), 2), 'money'],
                ['Delegate days', (int) $confirmed->sum(fn ($e) => $e->pax * $e->days), 'number'],
                ['Tentative (not counted)', round($events->where('status', FacilityBookingStatus::Tentative)->sum($value), 2), 'money'],
            ],
            'sections' => [
                [
                    'title' => 'By venue',
                    'columns' => [['Venue', 'text'], ['Events', 'number'], ['Event days', 'number'], ['Delegate days', 'number'], ['Revenue', 'money']],
                    'rows' => $byVenue->map(fn ($list, $venue) => [$venue, $list->count(), (int) $list->sum('days'), (int) $list->sum(fn ($e) => $e->pax * $e->days), round($list->sum($value), 2)])->values()->all(),
                    'footer' => ['Total', $confirmed->count(), (int) $confirmed->sum('days'), (int) $confirmed->sum(fn ($e) => $e->pax * $e->days), round($confirmed->sum($value), 2)],
                ],
                [
                    'title' => 'Events',
                    'columns' => [['Date', 'date'], ['Event', 'text'], ['Client', 'text'], ['Venue', 'text'], ['Pax', 'number'], ['Status', 'text'], ['Value', 'money']],
                    'rows' => $events->map(fn ($e) => [$e->start_date->toDateString(), $e->title, $e->client_name, $e->facility->name ?? '—', $e->pax, $e->status->label(), $value($e)])->all(),
                    'footer' => null,
                ],
            ],
        ];
    }

    private function payroll(Carbon $from, Carbon $to): array
    {
        $months = DB::table('payrolls')
            ->whereIn('status', ['approved', 'paid'])
            ->whereBetween('period_month', [$from->copy()->startOfMonth()->toDateString(), $to->toDateString()])
            ->selectRaw('period_month, COUNT(*) as staff, SUM(gross) as gross, SUM(paye) as paye, SUM(nssf) as nssf, SUM(shif) as shif, SUM(housing_levy) as levy, SUM(other_deductions) as other, SUM(net) as net')
            ->groupBy('period_month')
            ->orderBy('period_month')
            ->get();

        $sum = fn ($f) => round((float) $months->sum($f), 2);

        return [
            'cards' => [
                ['Gross pay', $sum('gross'), 'money'],
                ['PAYE', $sum('paye'), 'money'],
                ['NSSF + SHA + Levy (employee)', round($sum('nssf') + $sum('shif') + $sum('levy'), 2), 'money'],
                ['Net pay', $sum('net'), 'money'],
            ],
            'sections' => [[
                'title' => 'By month',
                'columns' => [['Month', 'text'], ['Staff', 'number'], ['Gross', 'money'], ['PAYE', 'money'], ['NSSF', 'money'], ['SHA', 'money'], ['Housing Levy', 'money'], ['Other deductions', 'money'], ['Net pay', 'money']],
                'rows' => $months->map(fn ($m) => [Carbon::parse($m->period_month)->format('F Y'), (int) $m->staff, (float) $m->gross, (float) $m->paye, (float) $m->nssf, (float) $m->shif, (float) $m->levy, (float) $m->other, (float) $m->net])->all(),
                'footer' => ['Total', '', $sum('gross'), $sum('paye'), $sum('nssf'), $sum('shif'), $sum('levy'), $sum('other'), $sum('net')],
                'note' => 'Approved and paid payroll only. These are the employee deductions; the employer also owes matching NSSF and Housing Levy contributions.',
            ]],
        ];
    }

    private function stock(Carbon $from, Carbon $to): array
    {
        $moves = DB::table('inventory_transactions as t')->whereBetween('t.created_at', [$from, $to->copy()->endOfDay()]);

        $purchases = (clone $moves)
            ->where('t.reference_type', 'purchase_order')
            ->join('purchase_orders as p', 'p.id', '=', 't.reference_id')
            ->join('suppliers as s', 's.id', '=', 'p.supplier_id')
            ->selectRaw('s.name as supplier, COUNT(DISTINCT p.id) as orders, SUM(t.total_value) as value')
            ->groupBy('s.name')
            ->orderByDesc('value')
            ->get();

        $usage = (clone $moves)
            ->where('t.type', 'out')
            ->leftJoin('departments as d', 'd.id', '=', 't.department_id')
            ->selectRaw("COALESCE(d.name, 'No department') as department, COUNT(*) as issues, SUM(t.total_value) as value")
            ->groupBy('department')
            ->orderByDesc('value')
            ->get();

        $writeOffs = (clone $moves)
            ->whereIn('t.type', ['damage', 'expired'])
            ->join('inventory_items as i', 'i.id', '=', 't.inventory_item_id')
            ->selectRaw('i.name as item, t.type, SUM(-t.quantity) as qty, i.unit_of_measure as unit, SUM(t.total_value) as value')
            ->groupBy('i.name', 't.type', 'i.unit_of_measure')
            ->orderByDesc('value')
            ->get();

        $onHand = InventoryItem::active()
            ->selectRaw('item_type, COUNT(*) as items, SUM(quantity * unit_cost) as value')
            ->groupBy('item_type')
            ->orderByDesc('value')
            ->get();

        return [
            'cards' => [
                ['Bought (received)', round((float) $purchases->sum('value'), 2), 'money'],
                ['Issued / used', round((float) $usage->sum('value'), 2), 'money'],
                ['Written off', round((float) $writeOffs->sum('value'), 2), 'money'],
                ['Stock value now', round((float) $onHand->sum('value'), 2), 'money'],
            ],
            'sections' => [
                [
                    'title' => 'Received from suppliers',
                    'columns' => [['Supplier', 'text'], ['Orders', 'number'], ['Value', 'money']],
                    'rows' => $purchases->map(fn ($p) => [$p->supplier, (int) $p->orders, (float) $p->value])->all(),
                    'footer' => ['Total', (int) $purchases->sum('orders'), round((float) $purchases->sum('value'), 2)],
                ],
                [
                    'title' => 'Used by department',
                    'columns' => [['Department', 'text'], ['Issues', 'number'], ['Value at average cost', 'money']],
                    'rows' => $usage->map(fn ($u) => [$u->department, (int) $u->issues, (float) $u->value])->all(),
                    'footer' => ['Total', (int) $usage->sum('issues'), round((float) $usage->sum('value'), 2)],
                ],
                [
                    'title' => 'Written off',
                    'columns' => [['Item', 'text'], ['Reason', 'text'], ['Quantity', 'text'], ['Value', 'money']],
                    'rows' => $writeOffs->map(fn ($w) => [$w->item, ucfirst($w->type), rtrim(rtrim(number_format((float) $w->qty, 2), '0'), '.') . ' ' . $w->unit, (float) $w->value])->all(),
                    'footer' => ['Total', '', '', round((float) $writeOffs->sum('value'), 2)],
                ],
                [
                    'title' => 'Stock on hand now',
                    'columns' => [['Kind', 'text'], ['Items', 'number'], ['Value', 'money']],
                    'rows' => $onHand->map(fn ($o) => [InventoryItem::TYPES[$o->item_type] ?? $o->item_type, (int) $o->items, round((float) $o->value, 2)])->all(),
                    'footer' => ['Total', (int) $onHand->sum('items'), round((float) $onHand->sum('value'), 2)],
                ],
            ],
        ];
    }

    private function expenses(Carbon $from, Carbon $to): array
    {
        $paid = \App\Models\Expense::paid()->whereBetween('spent_on', [$from->toDateString(), $to->toDateString()]);
        $categories = \App\Models\Expense::CATEGORIES;

        $byCategory = (clone $paid)->selectRaw('category, COUNT(*) as n, SUM(amount) as total')->groupBy('category')->orderByDesc('total')->get();
        $bySupplier = (clone $paid)->whereNotNull('supplier_id')->with('supplier:id,name')
            ->selectRaw('supplier_id, COUNT(*) as n, SUM(amount) as total')->groupBy('supplier_id')->orderByDesc('total')->get();
        $byMonth = (clone $paid)->selectRaw("DATE_FORMAT(spent_on, '%Y-%m') as month, SUM(CASE WHEN category = 'supplier_payment' THEN amount ELSE 0 END) as suppliers, SUM(CASE WHEN category <> 'supplier_payment' THEN amount ELSE 0 END) as running, SUM(amount) as total")
            ->groupBy('month')->orderBy('month')->get();

        $total = round((float) $byCategory->sum('total'), 2);
        $supplierPaid = round((float) ($byCategory->firstWhere('category', 'supplier_payment')?->total ?? 0), 2);

        // Owed now: everything received from suppliers less everything paid to them
        $received = (float) DB::table('inventory_transactions')->where('reference_type', 'purchase_order')->sum('total_value');
        $paidEver = (float) \App\Models\Expense::paid()->where('category', 'supplier_payment')->sum('amount');

        return [
            'cards' => [
                ['Paid out', $total, 'money'],
                ['Running costs', round($total - $supplierPaid, 2), 'money'],
                ['Paid to suppliers', $supplierPaid, 'money'],
                ['Owed to suppliers now', round($received - $paidEver, 2), 'money'],
            ],
            'sections' => [
                [
                    'title' => 'By kind',
                    'columns' => [['Kind', 'text'], ['Payments', 'number'], ['Amount', 'money'], ['Share', 'percent']],
                    'rows' => $byCategory->map(fn ($c) => [$categories[$c->category] ?? $c->category, (int) $c->n, (float) $c->total, $total ? round($c->total / $total * 100, 1) : 0])->all(),
                    'footer' => ['Total', (int) $byCategory->sum('n'), $total, 100],
                ],
                [
                    'title' => 'Paid to suppliers',
                    'columns' => [['Supplier', 'text'], ['Payments', 'number'], ['Amount', 'money']],
                    'rows' => $bySupplier->map(fn ($s) => [$s->supplier->name ?? '—', (int) $s->n, (float) $s->total])->all(),
                    'footer' => ['Total', (int) $bySupplier->sum('n'), round((float) $bySupplier->sum('total'), 2)],
                ],
                [
                    'title' => 'By month',
                    'columns' => [['Month', 'text'], ['Running costs', 'money'], ['Suppliers', 'money'], ['Total', 'money']],
                    'rows' => $byMonth->map(fn ($m) => [Carbon::parse($m->month . '-01')->format('F Y'), (float) $m->running, (float) $m->suppliers, (float) $m->total])->all(),
                    'footer' => ['Total', round($total - $supplierPaid, 2), $supplierPaid, $total],
                    'note' => 'Voided expenses are left out. Payroll is in the Payroll report.',
                ],
            ],
        ];
    }

    // ---- Export ------------------------------------------------------------

    private function csv(string $report, string $title, Carbon $from, Carbon $to, bool $snapshot, array $data): StreamedResponse
    {
        $name = $report . '-' . ($snapshot ? today()->toDateString() : $from->toDateString() . '-to-' . $to->toDateString()) . '.csv';

        return response()->streamDownload(function () use ($title, $from, $to, $snapshot, $data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // so Excel reads the file as UTF-8
            fputcsv($out, [$title, $snapshot ? 'As at ' . today()->toDateString() : $from->toDateString() . ' to ' . $to->toDateString()]);
            fputcsv($out, []);
            foreach ($data['cards'] as [$label, $value]) {
                fputcsv($out, [$label, $value]);
            }
            foreach ($data['sections'] as $section) {
                fputcsv($out, []);
                fputcsv($out, [$section['title']]);
                fputcsv($out, array_column($section['columns'], 0));
                foreach ($section['rows'] as $row) {
                    fputcsv($out, $row);
                }
                if (! empty($section['footer'])) {
                    fputcsv($out, $section['footer']);
                }
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
