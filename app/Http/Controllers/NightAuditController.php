<?php

namespace App\Http\Controllers;

use App\Models\NightAudit;
use App\Services\NightAudit as NightAuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NightAuditController extends Controller
{
    public function index(): View
    {
        return view('night-audits.index', [
            'audits' => NightAudit::with('runner')->orderByDesc('business_date')->paginate(30),
            'lastClosed' => NightAudit::max('business_date'),
        ]);
    }

    public function show(NightAudit $nightAudit): View
    {
        return view('night-audits.show', ['audit' => $nightAudit->load('runner')]);
    }

    public function store(Request $request, NightAuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'business_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $record = $audit->run(Carbon::parse($data['business_date']), $request->user(), $data['notes'] ?? null);

        return redirect()->route('night-audits.show', $record)
            ->with('success', 'Day closed for ' . $record->business_date->format('D d M Y') . '.');
    }
}
