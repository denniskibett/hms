<?php

namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use App\Models\AuditTrail;
use App\Models\Role;
use App\Models\User;
use App\Services\CoreService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Accounts and access (admins). HR keeps the employment side of staff; the
 * front desk keeps guests. Here an admin sets roles, status and passwords.
 */
class UserController extends Controller
{
    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ];

    public function __construct(private CoreService $core)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'role' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $filters['role'] ??= 'staff';

        $users = User::with(['roles', 'staffProfile:id,user_id', 'guestProfile:id,user_id'])
            ->when($filters['role'] === 'staff', fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', '!=', 'guest')))
            ->when(! in_array($filters['role'], ['staff', 'all'], true), fn ($q) => $q->whereHas('roles', fn ($r) => $r->where('name', $filters['role'])))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['search'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")))
            ->orderBy('name')
            ->paginate(SystemHelper::paginationLimit())
            ->withQueryString();

        $staff = User::whereHas('roles', fn ($r) => $r->where('name', '!=', 'guest'));
        $stats = [
            'staff' => (clone $staff)->where('status', 'active')->count(),
            'admins' => User::where('status', 'active')->whereHas('roles', fn ($r) => $r->where('name', 'admin'))->count(),
            'guests' => User::whereHas('roles', fn ($r) => $r->where('name', 'guest'))->count(),
            'blocked' => (clone $staff)->where('status', '!=', 'active')->count(),
        ];

        return view('users.index', ['users' => $users, 'stats' => $stats, 'filters' => $filters, 'roles' => $this->roles()]);
    }

    public function show(User $user): View
    {
        $user->load(['roles', 'staffProfile.department', 'guestProfile']);
        $activity = AuditTrail::where('user_id', $user->id)->latest('id')->take(25)->get();

        return view('users.show', ['user' => $user, 'activity' => $activity, 'roles' => $this->roles()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $user = DB::transaction(function () use ($data) {
            $user = User::create($data['user'] + ['password' => Hash::make($data['password'])]);
            $user->forceFill(['status' => $data['status']])->save();
            $user->roles()->sync(Role::whereIn('name', $data['roles'])->pluck('id'));

            return $user;
        });

        $this->core->log($request->user()->id, 'user_created', "Account for {$user->name} created with roles " . implode(', ', $data['roles']), ['user_id' => $user->id]);

        return response()->json(['success' => true, 'message' => "{$user->name} added.", 'url' => route('users.show', $user)], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $this->validated($request, $user);
        $this->guardAdmins($request->user(), $user, $data['roles'], $data['status']);

        $before = $user->roles->pluck('name')->sort()->values()->all();

        DB::transaction(function () use ($user, $data) {
            $user->update($data['user'] + (! empty($data['password']) ? ['password' => Hash::make($data['password'])] : []));
            $user->forceFill(['status' => $data['status']])->save();
            $user->roles()->sync(Role::whereIn('name', $data['roles'])->pluck('id'));
        });

        $after = collect($data['roles'])->sort()->values()->all();
        $changes = array_filter([
            $before !== $after ? 'roles ' . implode(', ', $before) . ' → ' . implode(', ', $after) : null,
            ! empty($data['password']) ? 'password reset' : null,
            "status {$data['status']}",
        ]);
        $this->core->log($request->user()->id, 'user_updated', "{$user->name}: " . implode('; ', $changes), ['user_id' => $user->id]);

        return response()->json(['success' => true, 'message' => "{$user->name} updated."]);
    }

    /**
     * Delete an account that was never used. Anyone with history is deactivated instead.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($user->id === $request->user()->id) {
            return response()->json(['success' => false, 'message' => "You can't delete your own account."], 422);
        }
        if ($user->staffProfile()->exists() || $user->guestProfile()->exists() || DB::table('stays')->where('guest_id', $user->id)->exists()) {
            return response()->json(['success' => false, 'message' => "{$user->name} has staff or guest records. Set the account to Inactive instead."], 422);
        }

        try {
            DB::transaction(function () use ($user) {
                $user->roles()->detach();
                $user->delete();
            });
        } catch (QueryException) {
            return response()->json(['success' => false, 'message' => "{$user->name} has history in the system. Set the account to Inactive instead."], 422);
        }

        $this->core->log($request->user()->id, 'user_deleted', "Account {$user->name} ({$user->email}) deleted");

        return response()->json(['success' => true, 'message' => "{$user->name} deleted.", 'url' => route('users.index')]);
    }

    /**
     * An admin can't lock themselves out, and the hotel always keeps one active admin.
     */
    private function guardAdmins(User $actor, User $user, array $roles, string $status): void
    {
        $losesAdmin = $user->hasRole('admin') && (! in_array('admin', $roles, true) || $status !== 'active');
        if (! $losesAdmin) {
            return;
        }

        if ($user->id === $actor->id) {
            abort(response()->json(['success' => false, 'errors' => ['roles' => ["You can't remove your own admin access or deactivate yourself."]]], 422));
        }

        $otherAdmins = User::whereKeyNot($user->id)->where('status', 'active')->whereHas('roles', fn ($r) => $r->where('name', 'admin'))->exists();
        if (! $otherAdmins) {
            abort(response()->json(['success' => false, 'errors' => ['roles' => ['This is the only active admin. Make someone else an admin first.']]], 422));
        }
    }

    private function roles(): array
    {
        return Role::orderBy('name')->pluck('name')->all();
    }

    /**
     * @return array{user: array, roles: array, status: string, password: ?string}
     */
    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id),
                Rule::requiredIf(fn () => collect($request->input('roles', []))->diff(['guest'])->isNotEmpty())],
            'phone' => ['nullable', 'string', 'max:30'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
            'password' => [$user ? 'nullable' : 'required', 'string', Password::min(8)],
        ], [
            'email.required' => 'Staff accounts sign in with their email, so it is needed.',
            'roles.required' => 'Give the account at least one role.',
        ]);

        return [
            'user' => ['name' => $data['name'], 'email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null],
            'roles' => array_values(array_unique($data['roles'])),
            'status' => $data['status'],
            'password' => $data['password'] ?? null,
        ];
    }
}
