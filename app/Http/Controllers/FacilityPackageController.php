<?php

namespace App\Http\Controllers;

use App\Models\FacilityPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Conference packages and per-day extras. Listed on the facilities page.
 */
class FacilityPackageController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $package = FacilityPackage::create($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => "{$package->name} added.",
        ], 201);
    }

    public function update(Request $request, FacilityPackage $facilityPackage): JsonResponse
    {
        $facilityPackage->update($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => "{$facilityPackage->name} updated.",
        ]);
    }

    public function destroy(FacilityPackage $facilityPackage): JsonResponse
    {
        // Events keep their own copy of the price, but keep the link for reporting
        if ($facilityPackage->bookingItems()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "{$facilityPackage->name} has been sold on events and can't be deleted. Deactivate it instead.",
            ], 422);
        }

        $facilityPackage->delete();

        return response()->json([
            'success' => true,
            'message' => "{$facilityPackage->name} deleted.",
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'facility_id' => ['nullable', 'exists:facilities,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'charge_basis' => ['required', Rule::in(array_keys(FacilityPackage::BASES))],
            'inclusions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ]);
    }
}
