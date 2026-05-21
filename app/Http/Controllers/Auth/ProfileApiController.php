<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserSwipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileApiController extends Controller
{
    /**
     * Update the authenticated user's profile (name, bio, email, password).
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'         => 'sometimes|string|max:255',
            'bio'          => 'sometimes|string|max:1000|nullable',
            'email'        => 'sometimes|email|max:255|unique:users,email,' . $user->id,
            'password'     => 'sometimes|string|min:6',
            'vibes'        => 'sometimes|array',
            'vibes.*'      => 'string|max:100',
            'top_event_id' => 'sometimes|integer|nullable',
        ]);

        if ($request->has('name'))         $user->name         = $request->name;
        if ($request->has('bio'))          $user->bio          = $request->bio;
        if ($request->has('email'))        $user->email        = $request->email;
        if ($request->has('vibes'))        $user->vibes        = $request->vibes;      // auto-cast to JSON
        if ($request->has('top_event_id')) $user->top_event_id = $request->top_event_id;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // Return a fresh user with the top event relationship eager-loaded
        // so the mobile app can update its AuthContext in one round-trip.
        $fresh = $user->fresh()->load([
            'topEvent.location.city',
            'photos',
        ]);

        return response()->json([
            'message' => 'Profil frissítve',
            'user'    => $fresh,
        ]);
    }

    /**
     * Upload / replace a profile photo at a given position slot.
     * Accepts multipart/form-data with keys: photo (file), position (int, default 0).
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo'    => 'required|image|max:10240', // max 10 MB
            'position' => 'sometimes|integer|min:0|max:8',
        ]);

        $user     = $request->user();
        $sortOrder = (int) $request->input('position', 0); // 'position' from the mobile app maps to sort_order

        // Delete old photo at this sort_order slot if it exists
        $existing = \App\Models\UserPhoto::where('user_id', $user->id)
            ->where('sort_order', $sortOrder)
            ->first();

        if ($existing) {
            $oldRelativePath = ltrim(str_replace('/storage/', '', $existing->photo_url), '/');
            Storage::disk('public')->delete($oldRelativePath);
            $existing->delete();
        }

        $path = $request->file('photo')->store('photos', 'public');

        \App\Models\UserPhoto::create([
            'user_id'    => $user->id,
            'photo_url'  => '/storage/' . $path,
            'sort_order' => $sortOrder,
        ]);

        return response()->json([
            'photo_url' => asset('storage/' . $path),
            'position'  => $sortOrder,
        ]);
    }

    /**
     * Return real-time interaction stats for the authenticated user.
     * GET /api/profile/stats
     */
    public function stats(Request $request)
    {
        $userId = $request->user()->id;

        // --- Likes: users who swiped right (or super) on this user ---
        $likesCount = UserSwipe::where('swiped_id', $userId)
            ->where('is_right_swipe', true)
            ->count();

        // --- Matches: mutual right-swipes ---
        // IDs who swiped right on this user
        $theyLikedMe = UserSwipe::where('swiped_id', $userId)
            ->where('is_right_swipe', true)
            ->pluck('swiper_id');

        // Among those, how many did this user also swipe right on?
        $matchesCount = UserSwipe::where('swiper_id', $userId)
            ->where('is_right_swipe', true)
            ->whereIn('swiped_id', $theyLikedMe)
            ->count();

        // --- Super Likes: incoming swipes where direction was 'super' ---
        // The swipe table stores is_right_swipe=true for both 'right' and 'super'.
        // If your schema has a separate `is_super` boolean column, use that here.
        // Otherwise we fall back to 0 (safe default).
        $superLikesCount = 0;
        try {
            $superLikesCount = UserSwipe::where('swiped_id', $userId)
                ->where('is_super', true)
                ->count();
        } catch (\Exception $e) {
            // Column doesn't exist yet – return 0 gracefully
            $superLikesCount = 0;
        }

        return response()->json([
            'likes_count'       => $likesCount,
            'matches_count'     => $matchesCount,
            'super_likes_count' => $superLikesCount,
        ]);
    }
}
