<?php

namespace App\Http\Controllers;

use App\Enums\FacilityBookingStatus;
use App\Models\Facility;
use App\Models\FacilityBooking;
use App\Models\FacilityPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Venues and the conference packages sold with them. Events are booked in
 * FacilityBookingController.
 */
class FacilityController extends Controller
{
    public function index(): View
    {
        $facilities = Facility::withCount(['bookings as upcoming_count' => fn ($q) => $q->upcoming()])
            ->orderBy('name')
            ->get();

        $packages = FacilityPackage::with('facility:id,name')
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $stats = [
            'venues' => $facilities->count(),
            'today' => FacilityBooking::upcoming()->whereDate('start_date', '<=', today())->count(),
            'upcoming' => FacilityBooking::upcoming()->count(),
            'tentative' => FacilityBooking::withStatus(FacilityBookingStatus::Tentative)->whereDate('end_date', '>=', today())->count(),
        ];

        return view('facilities.index', compact('facilities', 'packages', 'stats'));
    }

    public function show(Facility $facility): View
    {
        $with = ['items', 'invoices' => fn ($q) => $q->where('status', '!=', 'cancelled')];
        $upcoming = $facility->bookings()->with($with)->upcoming()->orderBy('start_date')->get();
        $recent = $facility->bookings()
            ->with($with)
            ->whereNotIn('id', $upcoming->pluck('id'))
            ->latest('start_date')
            ->take(10)
            ->get();
        $packages = FacilityPackage::active()->forFacility($facility->id)->orderBy('name')->get();

        return view('facilities.show', compact('facility', 'upcoming', 'recent', 'packages'));
    }

    public function store(Request $request): JsonResponse
    {
        $facility = Facility::create($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => "{$facility->name} added.",
            'url' => route('facilities.show', $facility),
        ], 201);
    }

    public function update(Request $request, Facility $facility): JsonResponse
    {
        $facility->update($this->validated($request, $facility));

        return response()->json([
            'success' => true,
            'message' => "{$facility->name} updated.",
        ]);
    }

    public function destroy(Facility $facility): JsonResponse
    {
        // Keep event history intact
        if ($facility->bookings()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$facility->name} has events booked and can't be deleted. Set it to Unavailable instead.",
            ], 422);
        }

        $facility->tasks()->update(['facility_id' => null]);
        $facility->delete();

        return response()->json([
            'success' => true,
            'message' => "{$facility->name} deleted.",
            'url' => route('facilities.index'),
        ]);
    }

    private function validated(Request $request, ?Facility $facility = null): array
    {
        $request->merge(['code' => Str::slug($request->input('code') ?: $request->input('name'), '_')]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('facilities', 'code')->ignore($facility?->id)],
            'capacity' => ['required', 'integer', 'min:1', 'max:100000'],
            'base_rate' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amenities' => ['nullable', 'array', 'max:30'],
            'amenities.*' => ['string', 'max:50'],
            'status' => ['required', Rule::in(array_keys(Facility::STATUSES))],
        ]);

        $data['amenities'] = array_values(array_unique($data['amenities'] ?? []));
        $data['base_rate'] ??= 0;

        return $data;
    }
}
