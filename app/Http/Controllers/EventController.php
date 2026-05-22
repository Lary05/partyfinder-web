<?php
 
namespace App\Http\Controllers;
 
use App\Models\Event;
use App\Models\Location;
use App\Models\EventReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
class EventController extends Controller
{
    // --- FELHASZNÁLÓI OLDALAK ---
 
    // 1. FŐOLDAL
    public function index(Request $request) {
        $events = Event::with(['location.city.country'])
            ->where('status', 'approved')
            ->whereDate('start_time', '>=', now()->toDateString())
            ->orderBy('start_time', 'asc')
            ->get();
 
        if ($request->wantsJson() || $request->is('api/*')) {
            $userId = auth('sanctum')->id();
            $eventIds = $events->pluck('id')->toArray();
 
            $ticketCounts = \Illuminate\Support\Facades\DB::table('user_tickets')
                ->whereIn('event_id', $eventIds)
                ->selectRaw('event_id, count(*) as count')
                ->groupBy('event_id')
                ->pluck('count', 'event_id');
 
            $userTickets = [];
            if ($userId) {
                $userTickets = \Illuminate\Support\Facades\DB::table('user_tickets')
                    ->where('user_id', $userId)
                    ->whereIn('event_id', $eventIds)
                    ->pluck('event_id')
                    ->toArray();
            }
 
            $events->transform(function ($event) use ($ticketCounts, $userTickets) {
                $event->attendees_count = $ticketCounts[$event->id] ?? 0;
                $event->user_chipped_in = in_array($event->id, $userTickets);
                return $event;
            });
 
            return response()->json($events);
        }
 
        return view('welcome', compact('events'));
    }
 
    // 2. RÉSZLETEK OLDAL (Show) - ✅ JAVÍTVA
    public function show($id)
    {
        // Betöltjük az eseményt a várossal és a kommentekkel
        $event = Event::with(['location.city', 'comments.user'])->findOrFail($id);
 
        // Átadjuk a nézetnek az $event változót!
        return view('events.show', compact('event'));
    }
 
    // 3. SAJÁT ESEMÉNYEK (My Events) - ✅ JAVÍTVA
    // 3. SAJÁT ESEMÉNYEK (My Events) - ✅ JAVÍTVA (Jegyek + Érdeklődések)
    public function myEvents(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
 
        $user = Auth::user();
 
        // 1. Lekérjük azokat az ID-kat, amikre a user vett jegyet (user_tickets / chip in)
        $ticketEventIds = \Illuminate\Support\Facades\DB::table('user_tickets')
            ->where('user_id', $user->id)
            ->pluck('event_id')
            ->toArray();
       
        // 2. Lekérjük azokat az ID-kat, amiket a user bejelölt (event_reactions -> interested)
        $interestedEventIds = \Illuminate\Support\Facades\DB::table('event_reactions')
            ->where('user_id', $user->id)
            ->where('type', 'interested')
            ->pluck('event_id')
            ->toArray();
 
        // 3. Összevonjuk a két listát, és kiszűrjük a duplikációkat (ha véletlen mindkettő megvan)
        $allMyEventIds = array_unique(array_merge($ticketEventIds, $interestedEventIds));
 
        // 4. Lekérdezzük magukat az eseményeket
        $events = Event::whereIn('id', $allMyEventIds)
                       ->with('location')
                       ->orderBy('start_time', 'asc')
                       ->get();
 
        // 5. Ha a mobil app kéri (API)
        if ($request->wantsJson() || $request->is('api/*')) {
           
            // Kiszámoljuk a résztvevők számát, pont ahogy a Főoldalon is teszed!
            $ticketCounts = \Illuminate\Support\Facades\DB::table('user_tickets')
                ->whereIn('event_id', $allMyEventIds)
                ->selectRaw('event_id, count(*) as count')
                ->groupBy('event_id')
                ->pluck('count', 'event_id');
 
            // Hozzáadjuk a plusz infókat a kártyákhoz
            $events->transform(function ($event) use ($ticketCounts, $ticketEventIds) {
                $event->attendees_count = $ticketCounts[$event->id] ?? 0;
                $event->user_chipped_in = in_array($event->id, $ticketEventIds);
                return $event;
            });
 
            // Visszaküldjük a mobilappnak a frissített listát
            // Megj.: Ha a React kódod `{ my_events: [...] }` formátumot vár,
            // akkor írd át erre: return response()->json(['my_events' => $events]);
            return response()->json($events);
        }
 
        // Webes nézet
        return view('events.my_events', compact('events'));
    }
 
    // --- API VÉGPONTOK (JS-hez) ---
 
    // 4. SZŰRŐ API
    public function filter(Request $request)
    {
        // Betöltjük a kapcsolatokat, hogy később országra és városra is lehessen szűrni
        $query = \App\Models\Event::with(['location.city.country']);
 
        // 🟢 ÚJ: CSAK AZ ELFOGADOTT (ÉLES) BULIKAT MUTASSA!
        $query->where('status', 'approved');
 
        // Keresőmező
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%' . $request->keyword . '%');
        }
       
        // Ország szűrő
        if ($request->filled('country_id')) {
            $query->whereHas('location.city', function($q) use ($request) {
                $q->where('country_id', $request->country_id);
            });
        }
 
        // Város szűrő
        if ($request->filled('city_id')) {
            $query->whereHas('location', function($q) use ($request) {
                $q->where('city_id', $request->city_id);
            });
        }
 
        // Klub / Helyszín szűrő
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
 
        // 🔴 ITT A VARÁZSLAT: Csak akkor szűrünk a stílusra, ha a szó NEM az, hogy "all"
        if ($request->filled('genre') && $request->genre !== 'all') {
            $query->where('genre', $request->genre);
        }
 
        // 🔴 ITT IS: Csak akkor szűrünk korhatárra, ha a szó NEM az, hogy "all"
        if ($request->filled('age_limit') && $request->age_limit !== 'all') {
             $query->where('age_limit', '>=', (int)$request->age_limit);
        }
       
        // Dátum (Nap) szűrő
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }
 
        // Alapból csak a jövőbeli vagy mai bulikat mutassa, a régieket ne!
        $query->whereDate('start_time', '>=', now()->toDateString());
 
        // Időrendbe tesszük
        // Időrendbe tesszük és lapozzuk (alapból 12 db/oldal, ha a frontend mást nem kér)
        $perPage = $request->input('per_page', 24);
        $events = $query->orderBy('start_time', 'asc')->paginate($perPage);
 
        // Fetch attendees count and user chip-in status
        $userId = auth('sanctum')->id();
        $eventIds = $events->pluck('id')->toArray();
 
        $ticketCounts = \Illuminate\Support\Facades\DB::table('user_tickets')
            ->whereIn('event_id', $eventIds)
            ->selectRaw('event_id, count(*) as count')
            ->groupBy('event_id')
            ->pluck('count', 'event_id');
 
        $userTickets = [];
        if ($userId) {
            $userTickets = \Illuminate\Support\Facades\DB::table('user_tickets')
                ->where('user_id', $userId)
                ->whereIn('event_id', $eventIds)
                ->pluck('event_id')
                ->toArray();
        }
 
        $events->getCollection()->transform(function ($event) use ($ticketCounts, $userTickets) {
            $event->attendees_count = $ticketCounts[$event->id] ?? 0;
            $event->user_chipped_in = in_array($event->id, $userTickets);
            return $event;
        });
       
        return response()->json($events);
    }
 
    // 5. VISSZAJELZÉS (React)
    // ... (a többi függvény után)
 
    // ... korábbi kódok ...
 
    // ✅ REAKCIÓ GOMBOK MŰKÖDÉSE
    public function react(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = auth()->user();
        $type = $request->input('type', 'interested'); // Alapból 'interested'
 
        // Megnézzük, van-e már ilyen reakciója a felhasználónak
        // (Feltételezem, hogy van egy 'reactions' kapcsolata vagy táblája)
        $existingReaction = $event->reactions()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();
 
        if ($existingReaction) {
            // --- HA MÁR BEJELÖLTE (Kivétel) ---
       
            // 1. Töröljük a reakciót
            $existingReaction->delete();
       
            // 2. CSÖKKENTJÜK a számot 1-gyel (a meglévő számból vonunk le)
            // Csak akkor vonunk le, ha nagyobb mint 0, nehogy negatív legyen
            if ($event->interested_count > 0) {
                $event->decrement('interested_count');
            }
 
        } else {
            // --- HA MÉG NEM JELÖLTE BE (Hozzáadás) ---
 
            // 1. Létrehozzuk a reakciót
            $event->reactions()->create([
                'user_id' => $user->id,
                'type' => $type
            ]);
 
            // 2. NÖVELJÜK a számot 1-gyel (a meglévő számhoz adunk)
            $event->increment('interested_count');
        }
 
        return back();
    }
 
    // ✅ CHIP IN (RSVP) MŰKÖDÉSE
    public function toggleChipIn(Request $request, $id)
    {
        $userId = auth()->id();
 
        if (!$userId) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
 
        $existing = \Illuminate\Support\Facades\DB::table('user_tickets')
            ->where('user_id', $userId)
            ->where('event_id', $id)
            ->first();
 
        if ($existing) {
            \Illuminate\Support\Facades\DB::table('user_tickets')
                ->where('user_id', $userId)
                ->where('event_id', $id)
                ->delete();
            $chippedIn = false;
        } else {
            // Generálunk egy egyedi, 10 karakteres nagybetűs jegykódot
            $ticketCode = strtoupper(\Illuminate\Support\Str::random(10));
 
            try {
                \Illuminate\Support\Facades\DB::table('user_tickets')->insert([
                    'user_id' => $userId,
                    'event_id' => $id,
                    'ticket_code' => $ticketCode, // <-- ITT A JAVÍTÁS
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Ha az exception azért van, mert nincs created_at/updated_at a táblában:
                \Illuminate\Support\Facades\DB::table('user_tickets')->insert([
                    'user_id' => $userId,
                    'event_id' => $id,
                    'ticket_code' => $ticketCode, // <-- ITT A JAVÍTÁS
                ]);
            }
            $chippedIn = true;
        }
 
        $attendeesCount = \Illuminate\Support\Facades\DB::table('user_tickets')
            ->where('event_id', $id)
            ->count();
 
        return response()->json([
            'chipped_in' => $chippedIn,
            'attendees_count' => $attendeesCount
        ]);
    }
 
    // ✅ KOMMENT MENTÉSE (Mert a route-od ide mutat: storeComment)
    public function storeComment(Request $request, $id)
    {
        $request->validate(['content' => 'required|max:500']);
 
        \App\Models\Comment::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'event_id' => $id,
            'content' => $request->content,
        ]);
 
        return back()->with('success', 'Komment elküldve!');
    }
 
    // --- ADMIN / SZERKESZTŐ FUNKCIÓK ---
 
    // 6. ÚJ ESEMÉNY (Create)
    public function create() {
        $locations = \App\Models\Location::with('city')->orderBy('name')->get();
        $cities = \App\Models\City::orderBy('name')->get(); // A városokat is átadjuk az új helyszínhez!
       
        return view('events.create', compact('locations', 'cities'));
    }
 
    // 7. MENTÉS (Store) - 🔴 ITT VOLT A HIBA, JAVÍTVA!
    // 7. MENTÉS (Store)
    // 7. MENTÉS (Store)
    public function store(Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'start_time' => 'required|date',
            'image' => 'nullable|image|max:4096',
            'new_location_name' => 'nullable|string|max:255',
            'new_city_name' => 'nullable|string|max:100', // 🟢 Ezt is validáljuk!
            'ticket_url' => 'nullable|url',
            'facebook_event_id' => 'nullable|string',
        ]);
 
        if (!$request->location_id && !$request->new_location_name) {
            return back()->withErrors(['location_id' => __('Please confirm the location first!')])->withInput();
        }
 
        $locationId = $request->location_id;
 
        // HA ÚJ HELYSZÍNT ADTAK MEG
        if ($request->filled('new_location_name')) {
           
            // 1. Város kiderítése vagy létrehozása a beírt név alapján!
            $cityName = trim($request->new_city_name);
            $defaultCountryId = \App\Models\Country::where('name', 'Hungary')->first()->id ?? 1;
 
            $city = \App\Models\City::firstOrCreate(
                ['name' => $cityName],
                [
                    'country_id' => $defaultCountryId,
                    'slug' => \Illuminate\Support\Str::slug($cityName)
                ]
            );
 
            // 2. Új helyszín mentése az okosan kiderített ID-kkal
            $location = \App\Models\Location::create([
                'name' => $request->new_location_name,
                'city_id' => $city->id,           // 🟢 Megvan az igazi város ID!
                'country_id' => $city->country_id, // 🟢 És megvan az ország ID is!
                'address' => $request->new_location_address,
                'lat' => $request->new_location_lat ?? 0,
                'lng' => $request->new_location_lng ?? 0,
            ]);
            $locationId = $location->id;
        }
 
        // Kivesszük a felesleges adatokat
        $data = $request->except(['image', '_token', 'new_location_name', 'new_city_name', 'new_location_address', 'new_location_lat', 'new_location_lng', 'location_confirmed']);
       
        $data['location_id'] = $locationId;
        $data['created_by'] = Auth::id();
        $data['status'] = Auth::user()->role === 'admin' ? 'approved' : 'pending';
        $data['genre'] = $request->genre ?? 'Egyéb';
        $data['age_limit'] = $request->age_limit ?? 0;
 
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $data['image_url'] = '/storage/' . $path;
        }
 
        Event::create($data);
 
        return redirect()->route('events.feed')->with('status', __('Event Announcement'));
    }
 
    // 8. SZERKESZTÉS (Edit)
    public function edit(Event $event) {
        if ($event->created_by !== Auth::id() && !Auth::user()->is_admin) abort(403);
        $cities = \App\Models\City::with('locations')->get();
        return view('events.edit', compact('event', 'cities'));
    }
 
    // 9. FRISSÍTÉS (Update)
    public function update(Request $request, Event $event) {
        if ($event->created_by !== Auth::id() && !Auth::user()->is_admin) abort(403);
       
        $request->validate([
            'ticket_url' => 'nullable|url',
            'facebook_event_id' => 'nullable|string',
        ]);
       
        $data = $request->except(['image', '_token', '_method']);
       
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('events', 'public');
            $data['image_url'] = '/storage/' . $path;
        }
       
        $event->update($data);
        return redirect()->route('events.show', ['id' => $event->id])->with('status', 'Frissítve!');
    }
 
    // 10. TÖRLÉS (Destroy)
    public function destroy(Event $event) {
        if ($event->created_by !== Auth::id() && !Auth::user()->is_admin) abort(403);
        $event->delete();
        return redirect()->route('events.feed')->with('status', 'Törölve!');
    }
 
    // 11. ADMIN JÓVÁHAGYÁS (Approve)
    public function approve($id) {
        $event = Event::findOrFail($id);
        $event->update(['status' => 'approved']);
        return response()->json(['message' => 'Esemény sikeresen jóváhagyva és publikálva!']);
    }
 
    // Admin felület megjelenítése a várakozó bulikkal
    public function adminDashboard()
    {
        // Csak admin mehet be
        if (auth()->user()->role !== 'admin') abort(403);
 
        $pendingEvents = Event::where('status', 'pending')->with('location')->get();
        return view('admin.dashboard', compact('pendingEvents'));
    }
 
    // Jóváhagyás gomb logikája (Webes verzió)
    public function approveWeb($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
 
        $event = Event::findOrFail($id);
        $event->update(['status' => 'approved']);
 
        return back()->with('status', 'Event approved successfully!');
    }
 
    // 12. TÉRKÉP API (Live Map)
    public function getMapEvents()
    {
        // Fetch approved, upcoming/active events and eager load the location
        $events = Event::with('location')
            ->where('status', 'approved')
            ->whereDate('start_time', '>=', now()->toDateString())
            ->get();
 
        // Note: The Event model already has an 'image_url' attribute which covers the thumbnail requirement natively.
        // We don't need a separate eager load for photos since 'image_url' is on the table itself.
 
        // Fetch attendees count and user chip-in status
        $userId = auth('sanctum')->id();
        $eventIds = $events->pluck('id')->toArray();
 
        $ticketCounts = \Illuminate\Support\Facades\DB::table('user_tickets')
            ->whereIn('event_id', $eventIds)
            ->selectRaw('event_id, count(*) as count')
            ->groupBy('event_id')
            ->pluck('count', 'event_id');
 
        $userTickets = [];
        if ($userId) {
            $userTickets = \Illuminate\Support\Facades\DB::table('user_tickets')
                ->where('user_id', $userId)
                ->whereIn('event_id', $eventIds)
                ->pluck('event_id')
                ->toArray();
        }
 
        $events->transform(function ($event) use ($ticketCounts, $userTickets) {
            $event->attendees_count = $ticketCounts[$event->id] ?? 0;
            $event->user_chipped_in = in_array($event->id, $userTickets);
            return $event;
        });
 
        return response()->json($events);
    }
}
