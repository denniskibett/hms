<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\GuestMessage;
use App\Models\System;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

use function Illuminate\Support\defer;

/**
 * Guest messages: the history of every SMS and email, sending one by hand,
 * retrying a failed one, and (managers) the wording of the automatic ones.
 */
class GuestMessageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'tab' => ['nullable', Rule::in(['history', 'templates'])],
            'channel' => ['nullable', Rule::in(array_keys(GuestMessage::CHANNELS))],
            'status' => ['nullable', Rule::in(array_keys(GuestMessage::STATUSES))],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $tab = $filters['tab'] ?? 'history';

        $messages = GuestMessage::with(['guest:id,name', 'guest.guestProfile:id,user_id', 'sender:id,name'])
            ->when($filters['channel'] ?? null, fn ($q, $channel) => $q->where('channel', $channel))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('recipient', 'like', "%{$search}%")
                ->orWhere('body', 'like', "%{$search}%")
                ->orWhereHas('guest', fn ($q) => $q->where('name', 'like', "%{$search}%"))))
            ->latest('id')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        return view('guest-messages.index', [
            'tab' => $tab,
            'filters' => $filters,
            'messages' => $messages,
            'failedCount' => GuestMessage::failed()->count(),
            'canEditTemplates' => $request->user()->hasAnyRole(['admin', 'manager']),
        ]);
    }

    /**
     * A message typed by staff to one guest, optionally about one booking.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'guest_id' => ['required', 'exists:users,id'],
            'stay_id' => ['nullable', Rule::exists('stays', 'id')->where('guest_id', $request->input('guest_id'))],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => [Rule::in(array_keys(GuestMessage::CHANNELS))],
            'subject' => ['nullable', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:918'], // six SMS parts
        ]);

        $guest = User::findOrFail($data['guest_id']);
        $attributes = [
            'guest_id' => $guest->id,
            'stay_id' => $data['stay_id'] ?? null,
            'sent_by' => $request->user()->id,
        ];
        $sent = [];

        if (in_array('sms', $data['channels'], true)) {
            if (! $phone = GuestMessage::normalisePhone($guest->phone)) {
                return back()->withInput()->with('error', "{$guest->name} has no phone number we can text.");
            }
            GuestMessage::queue('sms', $phone, $data['body'], $attributes);
            $sent[] = 'SMS';
        }

        if (in_array('email', $data['channels'], true)) {
            if (! $guest->email) {
                return back()->withInput()->with('error', "{$guest->name} has no email address.");
            }
            GuestMessage::queue('email', $guest->email, $data['body'], $attributes + [
                'subject' => $data['subject'] ?: 'A message from ' . SystemHelper::appName(),
            ]);
            $sent[] = 'email';
        }

        return back()->with('success', implode(' and ', $sent) . " sent to {$guest->name}.");
    }

    public function retry(GuestMessage $guestMessage): RedirectResponse
    {
        if ($guestMessage->status !== 'failed') {
            return back()->with('error', 'Only failed messages can be sent again.');
        }

        $guestMessage->update(['status' => 'pending', 'error' => null]);
        defer(fn () => $guestMessage->deliver());

        return back()->with('success', "Sending again to {$guestMessage->recipient}.");
    }

    /**
     * Wording of the automatic messages and whether each one is sent.
     */
    public function updateTemplates(Request $request): RedirectResponse
    {
        $keys = array_keys(GuestMessage::TEMPLATES);
        $data = $request->validate([
            'templates' => ['required', 'array'],
            'templates.*' => ['required', 'string', 'max:480'],
            'auto' => ['nullable', 'array'],
        ]);

        $system = System::settings();
        $settings = $system->settings ?? [];

        foreach ($keys as $key) {
            $body = trim($data['templates'][$key] ?? '');
            // Store only wording that differs from the default, so improved defaults still arrive
            $settings['messages']['templates'][$key] = $body !== GuestMessage::TEMPLATES[$key]['body'] ? $body : null;
            $settings['messages']['auto'][$key] = (bool) ($data['auto'][$key] ?? false);
        }

        $system->update(['settings' => $settings]);
        Cache::forget('system_settings');

        return redirect()->route('guest-messages.index', ['tab' => 'templates'])->with('success', 'Message templates saved.');
    }
}
