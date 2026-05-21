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

        // Akiket már elhúztál
        $swipedUserIds = UserSwipe::where('swiper_id', $user->id)
            ->pluck('swiped_id')
            ->toArray();

        $query = User::with(['photos'])
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $swipedUserIds)
            ->where('is_admin', false);

        // --- 1. KORSZŰRŐ (ENGEDÉKENY VERZIÓ) ---
        $minAge = clone now()->subYears($user->discovery_max_age);
        $maxAge = clone now()->subYears($user->discovery_min_age);

        $query->where(function ($q) use ($minAge, $maxAge, $user) {
            // A) Van születési dátuma és belefér
            $q->whereBetween('birth_date', [$minAge->toDateString(), $maxAge->toDateString()])
              // B) VAGY nincs dátum, de van kora és belefér
              ->orWhere(function ($q2) use ($user) {
                  $q2->whereNull('birth_date')
                     ->whereNotNull('age')
                     ->whereBetween('age', [$user->discovery_min_age, $user->discovery_max_age]);
              })
              // C) VAGY egyáltalán nincs kitöltve a kora, ezért "kegyelmet" kap
              ->orWhere(function ($q3) {
                  $q3->whereNull('birth_date')->whereNull('age');
              });
        });

        // --- 2. TÁVOLSÁG SZŰRŐ (ENGEDÉKENY VERZIÓ) ---
        if ($user->latitude && $user->longitude) {
            $distanceInMeters = $user->discovery_distance * 1000;
            $haversine = "(6371 * acos(cos(radians($user->latitude)) 
                        * cos(radians(latitude)) 
                        * cos(radians(longitude) - radians($user->longitude)) 
                        + sin(radians($user->latitude)) 
                        * sin(radians(latitude)))) * 1000";
            
            $query->where(function ($q) use ($haversine, $distanceInMeters) {
                // A) Benne van a távolságban
                $q->whereRaw("{$haversine} <= ?", [$distanceInMeters])
                  // B) VAGY nincs még megadva a GPS koordinátája, így engedjük
                  ->orWhereNull('latitude')
                  ->orWhereNull('longitude');
            });
        }

        $candidates = $query->get();

        // Get all candidate IDs
        $candidateIds = $candidates->pluck('id')->toArray();

        // Fetch all candidates who have liked the auth user in a single query
        $usersWhoLikedAuth = UserSwipe::whereIn('swiper_id', $candidateIds)
            ->where('swiped_id', $user->id)
            ->where('is_right_swipe', true)
            ->pluck('swiper_id')
            ->toArray();

        // Check if candidates also like the current user, so mobile app can get likesYou attribute
        foreach ($candidates as $candidate) {
            $candidate->likes_you = in_array($candidate->id, $usersWhoLikedAuth);
        }

        return response()->json($candidates);
    }

    /**
     * Clear past dislikes to recycle the swipe deck
     */
    public function recycle(Request $request)
    {
        try {
            // A te egyedi adatbázisodhoz igazítva: swiper_id és is_right_swipe
            \Illuminate\Support\Facades\DB::table('user_swipes')
                ->where('swiper_id', auth()->id())
                ->where('is_right_swipe', false)
                ->delete();

            return response()->json([
                'success' => true, 
                'message' => 'A balra húzott profilok sikeresen törölve!'
            ]);
            
        } catch (\Exception $e) {
            // Ha bármi baj van, írja ki pontosan, ne nyelje el!
            return response()->json([
                'success' => false, 
                'message' => 'Szerver hiba történt a törlés során: ' . $e->getMessage()
            ], 500);
        }
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

        // Find users the auth user already has a conversation with
        $authConvoIds = \Illuminate\Support\Facades\DB::table('conversation_participants')
            ->where('user_id', $user->id)
            ->pluck('conversation_id');

        $messagedUserIds = \Illuminate\Support\Facades\DB::table('conversation_participants')
            ->whereIn('conversation_id', $authConvoIds)
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id')
            ->toArray();

        // Fetch matched users with their photos
        $matchedUsers = User::with('photos')
            ->whereIn('id', $theyLikedMeIds)
            ->whereNotIn('id', $messagedUserIds)
            ->get();

        return response()->json($matchedUsers);
    }
}
