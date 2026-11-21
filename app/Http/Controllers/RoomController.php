<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Helpers\SystemHelper;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(RoomStatus::class)],
            'floor' => ['nullable', 'integer'],
            'wing' => ['nullable', 'string', 'max:50'],
            'room_type_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:50'],
        ]);

        $rooms = Room::with(['roomType', 'currentStay.guest'])
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->withStatus($status))
            ->when($filters['floor'] ?? null, fn ($q, $floor) => $q->byFloor($floor))
            ->when($filters['wing'] ?? null, fn ($q, $wing) => $q->where('wing', 'like', "%{$wing}%"))
            ->when($filters['room_type_id'] ?? null, fn ($q, $type) => $q->byRoomType($type))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('room_number', 'like', "%{$search}%"))
            ->orderBy('floor')
            ->orderBy('room_number')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        // Totals across all rooms, not just the current page
        $statusCounts = Room::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        $stats = [
            'total' => $statusCounts->sum(),
            'available' => $statusCounts[RoomStatus::Available->value] ?? 0,
            'occupied' => $statusCounts[RoomStatus::Occupied->value] ?? 0,
            'cleaning' => $statusCounts[RoomStatus::Cleaning->value] ?? 0,
            'out_of_service' => ($statusCounts[RoomStatus::Maintenance->value] ?? 0) + ($statusCounts[RoomStatus::OutOfOrder->value] ?? 0),
        ];

        $roomTypes = RoomType::orderBy('name')->get(['id', 'name', 'base_rate', 'capacity', 'bed_type']);

        return view('rooms.index', compact('rooms', 'roomTypes', 'stats', 'filters'));
    }

    public function show(Room $room): View
    {
        $room->load([
            'roomType',
            'currentStay.guest',
            'allocations' => fn ($q) => $q->with('stay.guest')->latest('from_date')->take(10),
        ])->loadCount(['allocations', 'pendingTasks']);

        $roomTypes = RoomType::orderBy('name')->get(['id', 'name', 'base_rate', 'capacity', 'bed_type']);

        return view('rooms.show', compact('room', 'roomTypes'));
    }

    public function store(Request $request): JsonResponse
    {
        $room = Room::create($this->validated($request));

        return response()->json([
            'success' => true,
            'message' => "Room {$room->room_number} created.",
            'room_id' => $room->id,
        ], 201);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $room->update($this->validated($request, $room));

        return response()->json([
            'success' => true,
            'message' => "Room {$room->room_number} updated.",
        ]);
    }

    /**
     * Quick status change (front desk / housekeeping).
     */
    public function updateStatus(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(RoomStatus::class)],
        ]);

        $status = RoomStatus::from($data['status']);

        // A room with a checked-in guest stays occupied until the guest checks out
        if ($status !== RoomStatus::Occupied && $room->currentStay()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Room {$room->room_number} has a checked-in guest. Check the guest out first.",
            ], 422);
        }

        $room->update(['status' => $status]);

        return response()->json([
            'success' => true,
            'message' => "Room {$room->room_number} is now {$status->label()}.",
        ]);
    }

    public function destroy(Room $room): JsonResponse
    {
        // Keep booking history intact: rooms that were ever allocated can't be deleted
        if ($room->allocations()->exists()) {
            return response()->json([
                'success' => false,
                'message' => "Room {$room->room_number} has booking history and can't be deleted. Set it to Out of Order instead.",
            ], 422);
        }

        $room->delete();

        return response()->json([
            'success' => true,
            'message' => "Room {$room->room_number} deleted.",
        ]);
    }

    /**
     * Which rooms are free between two dates.
     */
    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'arrival_date' => ['required', 'date'],
            'departure_date' => ['required', 'date', 'after:arrival_date'],
            'room_type_id' => ['nullable', 'integer'],
        ]);

        $arrival = Carbon::parse($data['arrival_date'])->startOfDay();
        $departure = Carbon::parse($data['departure_date'])->startOfDay();

        $availableIds = Room::availableBetween($arrival, $departure)->pluck('id')->flip();

        $rooms = Room::with('roomType')
            ->when($data['room_type_id'] ?? null, fn ($q, $type) => $q->byRoomType($type))
            ->orderBy('room_number')
            ->get()
            ->map(function (Room $room) use ($availableIds, $arrival, $departure) {
                $isAvailable = $availableIds->has($room->id);

                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'floor' => $room->floor,
                    'wing' => $room->wing,
                    'status' => $room->status->value,
                    'status_label' => $room->status->label(),
                    'room_type' => [
                        'id' => $room->roomType?->id,
                        'name' => $room->roomType?->name ?? 'N/A',
                        'base_rate' => $room->roomType?->base_rate ?? 0,
                        'capacity' => $room->roomType?->capacity ?? 1,
                        'bed_type' => $room->roomType?->bed_type ?? 'double',
                    ],
                    'is_available' => $isAvailable,
                    'next_available_date' => $isAvailable || $room->status->isOutOfService()
                        ? null
                        : $room->conflictingAllocation($arrival, $departure)?->to_date?->toDateString(),
                ];
            });

        return response()->json([
            'success' => true,
            'rooms' => $rooms,
            'available_count' => $availableIds->count(),
            'arrival_date' => $arrival->toDateString(),
            'departure_date' => $departure->toDateString(),
            'nights' => (int) $arrival->diffInDays($departure),
        ]);
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        $data = $request->validate([
            'room_number' => ['required', 'string', 'max:20', Rule::unique('rooms', 'room_number')->ignore($room?->id)],
            'room_type_id' => ['required', 'exists:room_types,id'],
            'floor' => ['required', 'integer', 'min:0', 'max:200'],
            'wing' => ['nullable', 'string', 'max:50'],
            'status' => ['required', Rule::enum(RoomStatus::class)],
            'adult_price' => ['nullable', 'numeric', 'min:0'],
            'child_price' => ['nullable', 'numeric', 'min:0'],
            'features' => ['nullable', 'array', 'max:30'],
            'features.*' => ['string', 'max:50'],
        ]);

        $data['features'] = array_values(array_unique($data['features'] ?? []));
        $data['adult_price'] ??= 0;
        $data['child_price'] ??= 0;

        return $data;
    }
}
