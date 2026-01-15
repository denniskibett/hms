<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use App\Services\CoreService;

class ProfileController extends Controller
{
    public function __construct(
        private CoreService $coreService
    ) {}
    
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $social = json_decode($user->social, true) ?? [];
        
        // Extract usernames from URLs for display
        $socialUsernames = [
            'facebook' => $this->extractUsernameFromUrl($social['facebook'] ?? ''),
            'twitter' => $this->extractUsernameFromUrl($social['twitter'] ?? ''),
            'linkedin' => $this->extractUsernameFromUrl($social['linkedin'] ?? '', 'linkedin.com/in/'),
            'instagram' => $this->extractUsernameFromUrl($social['instagram'] ?? ''),
        ];
        
        return view('profile.edit', [
            'user' => $user,
            'socialUsernames' => $socialUsernames,
        ]);
    }

    public function show(Request $request): View
    {
        $user = $request->user();
        $social = json_decode($user->social, true) ?? [];
        
        // Extract usernames from URLs for display
        $socialUsernames = [
            'facebook' => $this->extractUsernameFromUrl($social['facebook'] ?? ''),
            'twitter' => $this->extractUsernameFromUrl($social['twitter'] ?? ''),
            'linkedin' => $this->extractUsernameFromUrl($social['linkedin'] ?? '', 'linkedin.com/in/'),
            'instagram' => $this->extractUsernameFromUrl($social['instagram'] ?? ''),
        ];
        
        // Load additional data based on user role
        if ($user->isGuest()) {
            $user->load(['guestProfile', 'stays' => function ($q) {
                $q->latest()->limit(5);
            }]);
        } elseif ($user->isStaff()) {
            $user->load(['staffProfile.department']);
        }
        
        return view('profile.show', [
            'user' => $user,
            'socialUsernames' => $socialUsernames,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $user = $request->user();
            
            // Update user using CoreService
            $updatedUser = $this->coreService->updateUser($user, $request->validated());
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Store new avatar
                $path = $request->file('avatar')->store('avatars', 'public');
                $updatedUser->update(['avatar' => $path]);
            }
            
            // Handle social links
            $socialData = [];
            $socialPlatforms = ['facebook', 'twitter', 'linkedin', 'instagram'];
            
            foreach ($socialPlatforms as $platform) {
                $value = $request->input("social.{$platform}");
                if (!empty($value)) {
                    $socialData[$platform] = $this->formatSocialLink($platform, $value);
                }
            }
            
            if (!empty($socialData)) {
                $updatedUser->update(['social' => json_encode($socialData)]);
            }
            
            // Log the action
            $this->coreService->log($user->id, 'profile_updated', 'User updated their profile');
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully',
                    'user' => $updatedUser->fresh(),
                    'avatar_url' => $updatedUser->avatar ? asset('storage/' . $updatedUser->avatar) : null,
                    'social' => json_decode($updatedUser->social, true) ?? []
                ]);
            }
            
            return Redirect::route('profile.edit')->with('status', 'profile-updated');
            
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating profile',
                    'error' => $e->getMessage()
                ], 422);
            }
            
            return Redirect::route('profile.edit')
                ->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }

    /**
     * Delete user's avatar
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
                $user->avatar = null;
                $user->save();
                
                $this->coreService->log($user->id, 'avatar_deleted', 'User deleted their avatar');
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting avatar',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Extract username from social media URL
     */
    private function extractUsernameFromUrl(?string $url, string $prefix = ''): string
    {
        if (empty($url)) {
            return '';
        }
        
        // If it's already a username (no http), return it
        if (!str_starts_with($url, 'http')) {
            return $url;
        }
        
        // Remove the base URL to get username
        $prefixes = [
            'https://facebook.com/',
            'https://www.facebook.com/',
            'https://twitter.com/',
            'https://www.twitter.com/',
            'https://linkedin.com/in/',
            'https://www.linkedin.com/in/',
            'https://instagram.com/',
            'https://www.instagram.com/',
            'http://facebook.com/',
            'http://www.facebook.com/',
            'http://twitter.com/',
            'http://www.twitter.com/',
            'http://linkedin.com/in/',
            'http://www.linkedin.com/in/',
            'http://instagram.com/',
            'http://www.instagram.com/',
        ];
        
        foreach ($prefixes as $p) {
            if (str_starts_with($url, $p)) {
                return substr($url, strlen($p));
            }
        }
        
        return $url; // Return as-is if no match
    }

    /**
     * Format social media link based on platform and username
     */
    private function formatSocialLink(string $platform, string $username): string
    {
        $platformUrls = [
            'facebook' => 'https://facebook.com/',
            'twitter' => 'https://twitter.com/',
            'linkedin' => 'https://linkedin.com/in/',
            'instagram' => 'https://instagram.com/',
        ];
        
        if (isset($platformUrls[$platform])) {
            // Clean the username (remove any @ symbols and trim)
            $cleanUsername = trim($username, '@');
            return $platformUrls[$platform] . $cleanUsername;
        }
        
        return $username;
    }

    public function updateAddress(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'country' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'tax_id' => 'nullable|string|max:50',
            ]);
            
            $user->update($validated);
            
            $this->coreService->log($user->id, 'address_updated', 'User updated their address');
            
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating address',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Log the deletion
        $this->coreService->log($user->id, 'account_deleted', 'User deleted their account');

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function getData()
    {
        $user = auth()->user();
        
        return response()->json([
            'user' => $user->only(['name', 'email', 'phone', 'bio', 'country', 'city', 'state', 'postal_code', 'tax_id'])
        ]);
    }
}