<?php

namespace App\Services;

use App\Models\User;
use App\Models\Stay;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Task;
use App\Models\TaskType;
use App\Services\CoreService;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GuestService
{
    public function __construct(
        private CoreService $coreService,
        private FinanceService $financeService
    ) {}
    
    /**
     * Create guest and initial booking with multiple rooms.
     */
    public function createGuestWithBooking(array $guestData, array $bookingData): array
    {
        return DB::transaction(function () use ($guestData, $bookingData) {
            // 1. Create guest user
            $guest = $this->coreService->createUser($guestData, 'guest');
            
            // 2. Create stay/booking
            $stay = $this->createStay($guest, $bookingData);
            
            // 3. Assign multiple rooms if provided
            $roomAllocations = [];
            if (isset($bookingData['room_allocations']) && is_array($bookingData['room_allocations'])) {
                foreach ($bookingData['room_allocations'] as $allocationData) {
                    $roomAllocations[] = $this->assignRoom($stay, $allocationData);
                }
            } else {
                // Single room assignment (backward compatibility)
                $roomAllocations[] = $this->assignRoom($stay, $bookingData);
            }
            
            // 4. Create initial invoice
            $totalAmount = $this->calculateTotalStayAmount($stay, $roomAllocations);
            $invoice = $this->createStayInvoice($stay, $totalAmount, $roomAllocations);
            
            // 5. Send confirmation
            $this->sendBookingConfirmation($guest, $stay, $roomAllocations, $totalAmount);
            
            return [
                'guest' => $guest,
                'stay' => $stay,
                'room_allocations' => $roomAllocations,
                'invoice' => $invoice,
            ];
        });
    }
    
    /**
     * Create stay with multiple rooms.
     */
    public function createStayWithMultipleRooms(User $guest, array $data): array
    {
        return DB::transaction(function () use ($guest, $data) {
            // Create stay record
            $stay = $this->createStay($guest, [
                'arrival_date' => $data['arrival_date'],
                'departure_date' => $data['departure_date'],
                'adults' => collect($data['room_allocations'])->sum('adults'),
                'children' => collect($data['room_allocations'])->sum('children'),
                'special_requests' => $data['special_requests'] ?? null,
                'status' => $data['status'] ?? 'reserved',
            ]);
            
            // Assign multiple rooms
            $roomAllocations = [];
            if (isset($data['room_allocations']) && is_array($data['room_allocations'])) {
                foreach ($data['room_allocations'] as $allocationData) {
                    $roomAllocations[] = $this->assignRoom($stay, $allocationData);
                }
            }
            
            // Create invoice
            $totalAmount = $this->calculateTotalStayAmount($stay, $roomAllocations);
            $invoice = $this->createStayInvoice($stay, $totalAmount, $roomAllocations);
            
            // Check-in if status is checked_in
            if ($stay->status === 'checked_in') {
                $stay = $this->checkIn($stay);
            }
            
            return [
                'stay' => $stay,
                'room_allocations' => $roomAllocations,
                'invoice' => $invoice,
                'total_amount' => $totalAmount,
            ];
        });
    }
    
    /**
     * Create stay record.
     */
    public function createStay(User $guest, array $data): Stay
    {
        $stay = Stay::create([
            'guest_id' => $guest->id,
            'status' => $data['status'] ?? 'reserved',
            'arrival_date' => $data['arrival_date'],
            'departure_date' => $data['departure_date'],
            'adults' => $data['adults'] ?? 1,
            'children' => $data['children'] ?? 0,
            'special_requests' => $data['special_requests'] ?? null,
            'created_by' => auth()->id(),
        ]);
        
        $this->coreService->log(auth()->id(), 'stay_created', "Stay #{$stay->id} created for {$guest->name}");
        
        return $stay;
    }
    
    /**
     * Assign room with enhanced data structure.
     */
    public function assignRoom(Stay $stay, array $data): RoomAllocation
    {
        $room = Room::with('roomType')->findOrFail($data['room_id']);
        
        // Check room availability
        if (!$this->isRoomAvailable($room->id, $stay->arrival_date, $stay->departure_date, $stay->id)) {
            throw new \Exception("Room {$room->room_number} is not available for the selected dates");
        }
        
        // Use provided rate or room type base rate
        $rate = $data['rate'] ?? $room->roomType->base_rate ?? 0;
        
        $allocation = RoomAllocation::create([
            'stay_id' => $stay->id,
            'room_id' => $room->id,
            'from_date' => $stay->arrival_date,
            'to_date' => $stay->departure_date,
            'rate_applied' => $rate,
            'guest_notes' => $data['guest_notes'] ?? null,
            'adults' => $data['adults'] ?? 1,
            'children' => $data['children'] ?? 0,
        ]);
        
        // Update room status if checked in
        if ($stay->status === 'checked_in') {
            $room->update(['status' => 'occupied']);
        }
        
        $this->coreService->log(auth()->id(), 'room_assigned', 
            "Room " . $room->room_number . " assigned to Stay #" . $stay->id . " (" . ($data['guest_notes'] ?? 'Main') . ")");
        
        return $allocation;
    }
    
    /**
     * Calculate total amount for stay with multiple rooms.
     */
    private function calculateTotalStayAmount(Stay $stay, array $roomAllocations): float
    {
        $totalAmount = 0;
        $nights = $stay->arrival_date->diffInDays($stay->departure_date);
        
        foreach ($roomAllocations as $allocation) {
            // Calculate per room: rate × nights × (adults + children adjustment)
            $roomTotal = $allocation->rate_applied * $nights * $allocation->adults;
            
            // Add child adjustment (50% of adult rate per child)
            if ($allocation->children > 0) {
                $childRate = $allocation->rate_applied * 0.5;
                $roomTotal += $childRate * $nights * $allocation->children;
            }
            
            $totalAmount += $roomTotal;
        }
        
        return $totalAmount;
    }
    
    /**
     * Calculate room total amount.
     */
    private function calculateRoomTotal(RoomAllocation $allocation, int $nights): float
    {
        $adultTotal = $allocation->rate_applied * $nights * $allocation->adults;
        $childRate = $allocation->rate_applied * 0.5;
        $childTotal = $childRate * $nights * $allocation->children;
        
        return $adultTotal + $childTotal;
    }
    
    /**
     * Create invoice with multiple room items.
     */
    public function createStayInvoice(Stay $stay, float $totalAmount, array $roomAllocations): Invoice
    {
        $invoice = Invoice::create([
            'stay_id' => $stay->id,
            'guest_id' => $stay->guest_id,
            'invoice_number' => 'INV-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
            'issue_date' => now(),
            'due_date' => $stay->arrival_date,
            'subtotal' => $totalAmount,
            'tax_amount' => 0,
            'total_amount' => $totalAmount,
            'status' => 'draft',
            'created_by' => auth()->id(),
            'notes' => 'Initial booking invoice',
        ]);
        
        // Add separate items for each room allocation
        $nights = $stay->arrival_date->diffInDays($stay->departure_date);
        
        foreach ($roomAllocations as $allocation) {
            $room = $allocation->room;
            $roomTotal = $this->calculateRoomTotal($allocation, $nights);
            
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => sprintf(
                    'Room %s - %s (%s)',
                    $room->room_number,
                    $room->roomType->name ?? 'Standard',
                    $allocation->guest_notes ?: 'Main accommodation'
                ),
                'quantity' => $nights,
                'unit_price' => $allocation->rate_applied,
                'total' => $roomTotal,
                'item_type' => 'room_accommodation',
                'room_allocation_id' => $allocation->id,
                'details' => json_encode([
                    'adults' => $allocation->adults,
                    'children' => $allocation->children,
                    'nights' => $nights,
                ]),
            ]);
        }
        
        return $invoice;
    }
    
    /**
     * Send booking confirmation.
     */
    private function sendBookingConfirmation(User $guest, Stay $stay, array $roomAllocations, float $totalAmount): void
    {
        $roomDetails = array_map(function ($allocation) {
            $room = $allocation->room;
            return sprintf(
                'Room %s (%s) - %s adults, %s children',
                $room->room_number,
                $room->roomType->name ?? 'Standard',
                $allocation->adults,
                $allocation->children
            );
        }, $roomAllocations);
        
        $this->coreService->sendNotification($guest, 'booking_confirmation', [
            'guest_name' => $guest->name,
            'booking_id' => $stay->id,
            'check_in' => $stay->arrival_date->format('Y-m-d'),
            'check_out' => $stay->departure_date->format('Y-m-d'),
            'rooms' => $roomDetails,
            'room_count' => count($roomAllocations),
            'total_amount' => $totalAmount,
            'currency' => config('app.currency', 'USD'),
        ]);
    }
    
    /**
     * Check room availability (excluding current stay if updating).
     */
    public function isRoomAvailable(int $roomId, $checkIn, $checkOut, $excludeStayId = null): bool
    {
        $query = RoomAllocation::where('room_id', $roomId)
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('from_date', [$checkIn, $checkOut])
                      ->orWhereBetween('to_date', [$checkIn, $checkOut])
                      ->orWhere(function ($q) use ($checkIn, $checkOut) {
                          $q->where('from_date', '<=', $checkIn)
                            ->where('to_date', '>=', $checkOut);
                      });
            });
        
        if ($excludeStayId) {
            $query->whereHas('stay', function ($q) use ($excludeStayId) {
                $q->where('id', '!=', $excludeStayId);
            });
        }
        
        $conflict = $query->exists();
        
        $room = Room::find($roomId);
        
        return !$conflict && $room && in_array($room->status, ['available', 'cleaning']);
    }
    
    /**
     * Check-in guest.
     */
    public function checkIn(Stay $stay): Stay
    {
        return DB::transaction(function () use ($stay) {
            $stay->update([
                'status' => 'checked_in',
                'check_in' => now(),
            ]);
            
            // Update all allocated rooms status
            foreach ($stay->roomAllocations as $allocation) {
                $allocation->room->update(['status' => 'occupied']);
            }
            
            // Create check-in cleaning tasks
            try {
                $taskType = TaskType::where('code', 'CHECKIN_CLEAN')->first();
                if ($taskType) {
                    foreach ($stay->roomAllocations as $allocation) {
                        Task::create([
                            'task_type_id' => $taskType->id,
                            'title' => 'Check-in Cleaning - Room ' . $allocation->room->room_number,
                            'description' => 'Cleaning after guest check-in',
                            'room_id' => $allocation->room_id,
                            'stay_id' => $stay->id,
                            'due_date' => now()->addHours(2),
                            'priority' => 'high',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::info('Failed to create check-in tasks: ' . $e->getMessage());
            }
            
            $this->coreService->log(auth()->id(), 'check_in', "Guest checked in for Stay #{$stay->id}");
            
            return $stay->fresh();
        });
    }
    
    /**
     * Check-out guest.
     */
    public function checkOut(Stay $stay, array $data = []): array
    {
        return DB::transaction(function () use ($stay, $data) {
            // 1. Update stay status
            $stay->update([
                'status' => 'checked_out',
                'check_out' => now(),
            ]);
            
            // 2. Free all allocated rooms and mark for cleaning
            foreach ($stay->roomAllocations as $allocation) {
                $room = $allocation->room;
                $room->update(['status' => 'cleaning']);
            }
            
            // 3. Finalize invoice if it exists
            $invoice = $stay->invoices()->where('status', 'draft')->first();
            if ($invoice) {
                $invoice->update(['status' => 'final']);
            }
            
            // 4. Create checkout cleaning tasks
            try {
                foreach ($stay->roomAllocations as $allocation) {
                    $taskType = TaskType::where('code', 'CHECKOUT_CLEAN')->first();
                    if ($taskType) {
                        Task::create([
                            'task_type_id' => $taskType->id,
                            'title' => 'Check-out Cleaning - Room ' . $allocation->room->room_number,
                            'description' => 'Deep cleaning after guest check-out',
                            'room_id' => $allocation->room_id,
                            'stay_id' => $stay->id,
                            'due_date' => now()->addHours(1),
                            'priority' => 'urgent',
                            'created_by' => auth()->id(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::info('Failed to create check-out tasks: ' . $e->getMessage());
            }
            
            // 5. Log activity
            $this->coreService->log(auth()->id(), 'check_out', "Guest checked out from Stay #{$stay->id}");
            
            return [
                'stay' => $stay,
                'invoice' => $invoice,
            ];
        });
    }
    
    /**
     * Extend stay for all rooms.
     */
    public function extendStay(Stay $stay, $newDepartureDate, $newRoomIds = []): array
    {
        return DB::transaction(function () use ($stay, $newDepartureDate, $newRoomIds) {
            $oldDeparture = $stay->departure_date;
            
            // 1. Update stay
            $stay->update(['departure_date' => $newDepartureDate]);
            
            // 2. Extend all room allocations
            $additionalNights = $oldDeparture->diffInDays($newDepartureDate);
            $additionalAmount = 0;
            
            foreach ($stay->roomAllocations as $allocation) {
                // Extend room allocation
                $allocation->update(['to_date' => $newDepartureDate]);
                
                // Calculate additional amount for this room
                $additionalAmount += $allocation->rate_applied * $additionalNights * $allocation->adults;
                if ($allocation->children > 0) {
                    $childRate = $allocation->rate_applied * 0.5;
                    $additionalAmount += $childRate * $additionalNights * $allocation->children;
                }
            }
            
            // 3. Update invoice
            $invoice = $stay->invoices()->where('status', 'draft')->first();
            if ($invoice) {
                $invoice->update([
                    'subtotal' => $invoice->subtotal + $additionalAmount,
                    'total_amount' => $invoice->total_amount + $additionalAmount,
                ]);
                
                // Add extension items for each room
                foreach ($stay->roomAllocations as $allocation) {
                    $roomExtensionAmount = $this->calculateRoomTotal($allocation, $additionalNights);
                    
                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => 'Extended Stay - Additional Nights (Room ' . $allocation->room->room_number . ')',
                        'quantity' => $additionalNights,
                        'unit_price' => $allocation->rate_applied,
                        'total' => $roomExtensionAmount,
                        'item_type' => 'room_extension',
                        'room_allocation_id' => $allocation->id,
                    ]);
                }
            }
            
            $this->coreService->log(auth()->id(), 'stay_extended', 
                "Stay #{$stay->id} extended from {$oldDeparture} to {$newDepartureDate}");
            
            return [
                'stay' => $stay,
                'additional_amount' => $additionalAmount,
            ];
        });
    }
    
    /**
     * Get available rooms for dates.
     */
    public function getAvailableRooms($checkIn, $checkOut, $roomTypeId = null, $capacity = null)
    {
        $checkIn = Carbon::parse($checkIn)->setTimezone(config('app.timezone'));
        $checkOut = Carbon::parse($checkOut)->setTimezone(config('app.timezone'));
        
        $query = Room::where('status', 'available')
            ->with('roomType');
        
        if ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        }
        
        if ($capacity) {
            $query->whereHas('roomType', function ($q) use ($capacity) {
                $q->where('capacity', '>=', $capacity);
            });
        }
        
        $allRooms = $query->get();
        
        return $allRooms->filter(function ($room) use ($checkIn, $checkOut) {
            return $this->isRoomAvailable($room->id, $checkIn, $checkOut);
        });
    }
    
    /**
     * Get guest's current stays.
     */
    public function getGuestCurrentStays(User $guest)
    {
        return $guest->stays()
            ->whereIn('status', ['booked', 'checked_in'])
            ->where('departure_date', '>=', now())
            ->with(['roomAllocations.room.roomType', 'invoices'])
            ->latest()
            ->get();
    }
    
    /**
     * Get guest's past stays.
     */
    public function getGuestPastStays(User $guest, $limit = 10)
    {
        return $guest->stays()
            ->whereIn('status', ['checked_out', 'cancelled'])
            ->with(['roomAllocations.room.roomType', 'invoices.payments'])
            ->latest()
            ->paginate($limit);
    }
    
    /**
     * Add additional room to existing stay.
     */
    public function addRoomToStay(Stay $stay, array $roomData): RoomAllocation
    {
        return DB::transaction(function () use ($stay, $roomData) {
            // Check if room is available for stay dates
            if (!$this->isRoomAvailable($roomData['room_id'], $stay->arrival_date, $stay->departure_date, $stay->id)) {
                throw new \Exception("Room is not available for the selected dates");
            }
            
            // Add the room allocation
            $allocation = $this->assignRoom($stay, $roomData);
            
            // Update stay guest count
            $stay->update([
                'adults' => $stay->adults + ($roomData['adults'] ?? 1),
                'children' => $stay->children + ($roomData['children'] ?? 0),
            ]);
            
            // Add to existing invoice or create new one
            $invoice = $stay->currentInvoice() ?? $stay->invoices()->latest()->first();
            if ($invoice) {
                $nights = $stay->arrival_date->diffInDays($stay->departure_date);
                $roomTotal = $this->calculateRoomTotal($allocation, $nights);
                
                $invoice->update([
                    'subtotal' => $invoice->subtotal + $roomTotal,
                    'total_amount' => $invoice->total_amount + $roomTotal,
                ]);
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Additional Room - ' . $allocation->room->room_number,
                    'quantity' => $nights,
                    'unit_price' => $allocation->rate_applied,
                    'total' => $roomTotal,
                    'item_type' => 'additional_room',
                    'room_allocation_id' => $allocation->id,
                ]);
            }
            
            return $allocation;
        });
    }
    
    /**
     * Remove room from stay.
     */
    public function removeRoomFromStay(RoomAllocation $allocation): void
    {
        DB::transaction(function () use ($allocation) {
            $stay = $allocation->stay;
            
            // Update stay guest count
            $stay->update([
                'adults' => max(0, $stay->adults - $allocation->adults),
                'children' => max(0, $stay->children - $allocation->children),
            ]);
            
            // Remove from invoice if it exists
            $invoiceItem = InvoiceItem::where('room_allocation_id', $allocation->id)->first();
            if ($invoiceItem) {
                $invoice = $invoiceItem->invoice;
                $invoice->update([
                    'subtotal' => max(0, $invoice->subtotal - $invoiceItem->total),
                    'total_amount' => max(0, $invoice->total_amount - $invoiceItem->total),
                ]);
                $invoiceItem->delete();
            }
            
            // Delete the allocation
            $allocation->delete();
            
            // Update room status if needed
            $room = $allocation->room;
            if ($stay->status === 'checked_in') {
                $room->update(['status' => 'available']);
            }
        });
    }
}