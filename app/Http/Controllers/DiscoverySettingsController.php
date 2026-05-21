<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiscoverySettingsController extends Controller
{
    /**
     * Update the authenticated user's discovery preferences.
     */
    public function update(Request $request)
    {
        $request->validate([
            'birth_date' => 'nullable|date',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'discovery_distance' => 'nullable|integer|min:1',
            'discovery_min_age' => 'nullable|integer|min:18',
            'discovery_max_age' => 'nullable|integer|gte:discovery_min_age',
        ]);

        $user = Auth::user();
        
        $data = $request->only([
            'birth_date',
            'latitude',
            'longitude',
            'discovery_distance',
            'discovery_min_age',
            'discovery_max_age'
        ]);

        // Remove null values from array if you don't want to overwrite with null
        // But in this case, user might want to clear lat/lng, so we just update whatever is provided.
        $user->update($data);

        return response()->json([
            'message' => 'Discovery settings updated successfully',
            'user' => $user->fresh()
        ]);
    }
}
