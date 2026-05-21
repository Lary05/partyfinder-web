<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class CreateTestMatcherSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Megkeressük a TE fő fiókodat
        $me = User::where('email', 'fishacejunior@gmail.com')->first();

        if (!$me) {
            $this->command->error("A fishacejunior@gmail.com fiók nem található az adatbázisban!");
            return;
        }

        // 2. Létrehozunk egy teljesen új, fiktív teszt felhasználót
        $testUser = User::updateOrCreate(
            ['email' => 'teszt.match@partify.hu'], // Ezzel az emaillal fog létrejönni
            [
                'name' => 'Kovács Anna (Teszt)',
                'password' => Hash::make('password123'),
                // Pontosan oda tesszük a térképen, ahol te is vagy, hogy tuti feldobja a gép!
                'latitude' => $me->latitude ?? 47.4979,
                'longitude' => $me->longitude ?? 19.0402,
                'age' => 20,
                'birth_date' => '2006-01-01',
                'discovery_distance' => 50,
                'discovery_min_age' => 18,
                'discovery_max_age' => 99,
                'is_admin' => false
            ]
        );

        // 3. A Teszt felhasználó JOBBRA húz TÉGED az adatbázisban
        DB::table('user_swipes')->updateOrInsert(
            [
                'swiper_id' => $testUser->id,   // Aki húz (A teszt lány)
                'swiped_id' => $me->id,         // Akit húznak (Te)
            ],
            [
                'is_right_swipe' => true,       // Jobbra húzás (Like)
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info("Sikeresen létrehozva! 'Kovács Anna (Teszt)' létrejött, és a háttérben már lájkolt téged.");
    }
}