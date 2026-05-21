<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

// Modellek importálása
use App\Models\User;
use App\Models\Location;

// Controllerek importálása
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EventReactionController; // 👈 Megvan a hiányzó láncszem!
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SwipeController;
use App\Http\Controllers\DiscoverySettingsController;
use App\Http\Controllers\Auth\ProfileApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ==========================================
// 🌍 1. PUBLIKUS ÚTVONALAK (Bárki elérheti)
// ==========================================

// Alapadatok
Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
Route::apiResource('cities', CityController::class)->only(['index', 'show']);
Route::apiResource('locations', LocationController::class)->only(['index', 'show']);

// Események (FONTOS: a 'filter' legyen az '{id}' előtt!)
Route::get('/events/filter', [EventController::class, 'filter']); 
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{id}', [EventController::class, 'show']);

// Lokációk szűrése város szerint
Route::get('/locations', function (Request $request) {
    if ($request->has('city_id')) {
        return Location::where('city_id', $request->city_id)->get();
    }
    return Location::all();
});

// Regisztráció és Login (JSON válaszokkal)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();
    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['error' => 'Hibás adatok'], 401);
    }
    return response()->json([
        'user' => $user,
        'token' => $user->createToken('auth_token')->plainTextToken
    ]);
});


// ==========================================
// 🔒 2. VÉDETT ÚTVONALAK (Csak bejelentkezve)
// ==========================================
Route::middleware(['auth:sanctum'])->group(function () {
    
    // 👤 User info és Profil
    Route::get('/user', function (Request $request) { 
        return $request->user(); 
    });
    Route::put('/profile', [ProfileApiController::class, 'update']);
    Route::post('/profile/photo', [ProfileApiController::class, 'uploadPhoto']);

    // 🃏 Swipe & Discover API
    Route::get('/discover', [SwipeController::class, 'discover']);
    Route::post('/discover/recycle', [SwipeController::class, 'recycle']);
    Route::post('/swipe', [SwipeController::class, 'swipe']);
    Route::get('/matches', [SwipeController::class, 'matches']);
    Route::post('/settings/discovery', [DiscoverySettingsController::class, 'update']);

    // 📅 Saját események
    Route::get('/events/my', [EventController::class, 'myEvents']);
    
    // ⭐ Kedvencek és Reakciók
    Route::post('/events/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/events/favorites', [FavoriteController::class, 'myFavorites']);

    // 🎟️ Chip In (Ott leszek)
    Route::post('/events/{event}/chip-in', [EventController::class, 'toggleChipIn']);

    // 💬 Közvetlen üzenetek (Live Chat)
    Route::get('/messages/{user}', [MessageController::class, 'getConversation']);
    Route::post('/messages/{user}', [MessageController::class, 'sendMessage']);

    // 🗺️ Live Map API
    Route::get('/map/events', [EventController::class, 'getMapEvents']);

    // 🔔 Egyéb funkciók
    Route::apiResource('notifications', NotificationController::class);
    Route::post('/events/{id}/refresh-facebook', [EventController::class, 'refreshFacebookStats']);
});


// ==========================================
// 👑 3. ADMIN ÚTVONALAK (Csak Admin)
// ==========================================
Route::middleware(['auth:sanctum', 'isAdmin'])->group(function () {

    // 🟢 Bulik jóváhagyása admin által
    Route::post('/events/{id}/approve', [EventController::class, 'approve']);
    
    // Események teljes menedzsmentje
    Route::post('/events', [EventController::class, 'store']);
    Route::put('/events/{id}', [EventController::class, 'update']);
    Route::delete('/events/{id}', [EventController::class, 'destroy']);
    Route::post('/events/{id}/image', [EventController::class, 'uploadImage']);
    
    // Teljes CRUD az erőforrásokhoz (létrehozás/törlés)
    Route::apiResource('countries', CountryController::class)->except(['index', 'show']);
    Route::apiResource('cities', CityController::class)->except(['index', 'show']);
    Route::apiResource('locations', LocationController::class)->except(['index', 'show']);
});