<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSwipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwipeController extends Controller
{
    /**
     * Get discovery profiles for the authenticated user.
     * Excludes self and any users already swiped on.
     */
    public function discover(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get IDs of users already swiped on
        $swipedUserIds = UserSwipe::where('swiper_id', $user->id)
            ->pluck('swiped_id')
            ->toArray();

        // Get discoverable users
        $query = User::with(['photos'])
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $swipedUserIds)
            ->where('is_admin', false); // typically exclude admin

        // Apply Age Filter
        $minAge = clone now()->subYears($user->discovery_max_age); // Born after this date
        $maxAge = clone now()->subYears($user->discovery_min_age); // Born before this date

        $query->where(function ($q) use ($minAge, $maxAge, $user) {
            // Include those with birth_date within the range
            $q->whereBetween('birth_date', [$minAge->toDateString(), $maxAge->toDateString()])
              // Or fallback to existing age column if birth_date is null
              ->orWhere(function ($q2) use ($user) {
                  $q2->whereNull('birth_date')
                     ->whereNotNull('age')
                     ->whereBetween('age', [$user->discovery_min_age, $user->discovery_max_age]);
              });
        });

        // Apply Distance Filter (if user has coordinates)
        if ($user->latitude && $user->longitude) {
            $distanceInMeters = $user->discovery_distance * 1000;
            // Haversine formula
            $haversine = "(6371 * acos(cos(radians($user->latitude)) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians($user->longitude)) 
                        + sin(radians($user->latitude)) 
                        * sin(radians(latitude)))) * 1000";
            
            $query->whereRaw("{$haversine} <= ?", [$distanceInMeters]);
        }

        $candidates = $query->get();

        // Check if candidates also like the current user, so mobile app can get likesYou attribute
        foreach ($candidates as $candidate) {
            $likesAuth = UserSwipe::where('swiper_id', $candidate->id)
                ->where('swiped_id', $user->id)
                ->where('is_right_swipe', true)
                ->exists();
            $candidate->likes_you = $likesAuth;
        }

        return response()->json($candidates);
    }

    /**
     * Clear past dislikes to recycle the swipe deck
     */
    public function recycle(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        UserSwipe::where('swiper_id', $user->id)
            ->where('is_right_swipe', false)
            ->delete();

        return response()->json([
            'success' => true, 
            'message' => 'Deck recycled'
        ]);
    }

    /**
     * Register a swipe action.
     */
    public function swipe(Request $request)
    {
        $request->validate([
            'swiped_id' => 'required|integer|exists:users,id',
            'direction'  => 'required|string|in:left,right,super'
        ]);

        $swiper = Auth::user();
        if (!$swiper) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $swipedId  = (int) $request->swiped_id;
        $direction = $request->direction; // 'left' | 'right' | 'super'

        // Prevent self-swiping
        if ($swiper->id === $swipedId) {
            return response()->json(['error' => 'Cannot swipe on yourself'], 400);
        }

        // Map the human-readable direction to the DB boolean column:
        // 'right' or 'super'  => is_right_swipe = true
        // 'left'              => is_right_swipe = false
        $isRightSwipe = in_array($direction, ['right', 'super']);

        // Save swipe (upsert on the pair, update the boolean)
        $swipe = UserSwipe::updateOrCreate(
            [
                'swiper_id' => $swiper->id,
                'swiped_id' => $swipedId,
            ],
            [
                'is_right_swipe' => $isRightSwipe,
            ]
        );

        $isMatch     = false;
        $matchedUser = null;

        // Only check for a mutual match when the current swipe is a like
        if ($isRightSwipe) {
            $reciprocalSwipe = UserSwipe::where('swiper_id', $swipedId)
                ->where('swiped_id', $swiper->id)
                ->where('is_right_swipe', true)
                ->first();

            if ($reciprocalSwipe) {
                $isMatch     = true;
                $matchedUser = User::with('photos')->find($swipedId);
            }
        }

        return response()->json([
            'success'      => true,
            'match'        => $isMatch,
            'matched_user' => $matchedUser,
        ]);
    }

    /**
     * Return all mutual matches for the authenticated user.
     * A match = auth user swiped right/super AND the other user also swiped right/super back.
     */
    public function matches(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // IDs the auth user has liked (right swipes)
        $iLikedIds = UserSwipe::where('swiper_id', $user->id)
            ->where('is_right_swipe', true)
            ->pluck('swiped_id');

        // IDs that liked the auth user back (right swipes)
        $theyLikedMeIds = UserSwipe::whereIn('swiper_id', $iLikedIds)
            ->where('swiped_id', $user->id)
            ->where('is_right_swipe', true)
            ->pluck('swiper_id');

        // Fetch matched users with their photos
        $matchedUsers = User::with('photos')
            ->whereIn('id', $theyLikedMeIds)
            ->get();

        return response()->json($matchedUsers);
    }
}
