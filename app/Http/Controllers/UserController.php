<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\CoreService;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;

class UserController extends Controller
{
    public function __construct(
        private CoreService $coreService
    ) {}
    
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);
        
        $role = $request->input('role');
        $status = $request->input('status');
        $search = $request->input('search');
        
        $users = $this->coreService->getUsersByRole($role ?? 'all', [
            'status' => $status,
            'search' => $search,
            'per_page' => $request->input('per_page', 20),
        ]);
        
        $roles = Role::all();
        
        return view('users.index', compact('users', 'roles'));
    }
    
    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);
        
        $roles = Role::where('name', '!=', 'guest')->get();
        $departments = Department::all();
        
        return view('users.create', compact('roles', 'departments'));
    }
    
    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);
        
        try {
            $user = $this->coreService->createUser(
                $request->validated(),
                $request->input('role')
            );
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating user: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        $user->load(['roles', 'guestProfile', 'staffProfile.department']);
        
        return view('users.show', compact('user'));
    }
    
    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        
        $roles = Role::all();
        $departments = Department::all();
        $user->load(['roles', 'staffProfile']);
        
        return view('users.edit', compact('user', 'roles', 'departments'));
    }
    
    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        
        try {
            $updatedUser = $this->coreService->updateUser($user, $request->validated());
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating user: ' . $e->getMessage());
        }
    }
    
    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        try {
            // Soft delete the user
            $user->delete();
            
            return redirect()->route('users.index')
                ->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
    
    /**
     * Deactivate a user.
     */
    public function deactivate(User $user, Request $request)
    {
        $this->authorize('deactivate', $user);
        
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);
        
        try {
            $this->coreService->deactivateUser($user, $request->input('reason'));
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User deactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deactivating user: ' . $e->getMessage());
        }
    }
    
    /**
     * Reactivate a user.
     */
    public function reactivate(User $user)
    {
        $this->authorize('reactivate', $user);
        
        try {
            $user->update(['status' => 'active']);
            
            return redirect()->route('users.show', $user)
                ->with('success', 'User reactivated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error reactivating user: ' . $e->getMessage());
        }
    }
    
    /**
     * Assign roles to user.
     */
    public function assignRoles(User $user, Request $request)
    {
        $this->authorize('assignRoles', $user);
        
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);
        
        try {
            $this->coreService->assignRoles($user, $request->input('roles'));
            
            return redirect()->route('users.show', $user)
                ->with('success', 'Roles assigned successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error assigning roles: ' . $e->getMessage());
        }
    }
    
    /**
     * Get users by role (API).
     */
    public function getByRole(Request $request)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);
        
        $users = User::whereHas('roles', function ($q) use ($request) {
            $q->where('name', $request->input('role'));
        })->get(['id', 'name', 'email']);
        
        return response()->json($users);
    }
}