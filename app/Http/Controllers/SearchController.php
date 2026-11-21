<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use App\Models\Invoice;
use App\Models\Room;
use App\Models\Stay;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Header search: guests, bookings, rooms and invoices, each only for the
 * roles that can open them (admins see everything).
 */
class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $q = trim((string) ($request->validate(['q' => ['nullable', 'string', 'max:100']])['q'] ?? ''));
        $user = $request->user();
        $can = fn (array $roles) => $user->hasAnyRole(array_merge($roles, ['admin']));
        $results = collect();

        if (mb_strlen($q) >= 2) {
            if ($can(['manager', 'receptionist'])) {
                $results['Guests'] = Guest::with('user')->search($q)->limit(8)->get()->map(fn ($g) => [
                    'title' => $g->user?->name ?? 'Guest #' . $g->id,
                    'meta' => collect([$g->user?->phone, $g->user?->email, $g->id_number])->filter()->join(' · '),
                    'href' => route('guests.show', $g),
                ]);

                $id = ctype_digit(ltrim($q, '#')) ? (int) ltrim($q, '#') : null;
                $results['Bookings'] = Stay::with('guest')
                    ->where(fn ($s) => $s->when($id, fn ($s) => $s->whereKey($id))
                        ->orWhereHas('guest', fn ($g) => $g->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")))
                    ->latest('arrival_date')->limit(8)->get()->map(fn ($s) => [
                        'title' => 'Booking #' . $s->id . ' · ' . ($s->guest?->name ?? 'Guest'),
                        'meta' => $s->arrival_date?->format('d M Y') . ' → ' . $s->departure_date?->format('d M Y') . ' · ' . $s->state->label(),
                        'href' => route('stays.show', $s),
                    ]);
            }

            if ($can(['manager', 'receptionist', 'housekeeping'])) {
                $results['Rooms'] = Room::with('roomType')->where('room_number', 'like', "%{$q}%")->limit(8)->get()->map(fn ($r) => [
                    'title' => 'Room ' . $r->room_number,
                    'meta' => ($r->roomType?->name ?? '') . ' · ' . ucfirst(str_replace('_', ' ', (string) ($r->status instanceof \BackedEnum ? $r->status->value : $r->status))),
                    'href' => route('rooms.show', $r),
                ]);
            }

            if ($can(['manager', 'finance'])) {
                $results['Invoices'] = Invoice::search($q)->latest('id')->limit(8)->get()->map(fn ($i) => [
                    'title' => $i->invoice_number,
                    'meta' => number_format((float) $i->total, 2) . ' · ' . ucfirst($i->status),
                    'href' => route('invoices.show', $i),
                ]);
            }
        }

        return view('search', ['q' => $q, 'results' => $results->filter(fn ($rows) => $rows->isNotEmpty())]);
    }
}
