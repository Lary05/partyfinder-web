<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
        CountrySeeder::class,
        // LocationSeeder::class, // Ha van külön, ha nincs, akkor itt hozzunk létre egyet:
        ]);

        // Gyorsan gyártunk egy várost és helyszínt, hogy az EventSeeder ne szálljon el
        $country = \App\Models\Country::first();
        $city = \App\Models\City::create(['name' => 'Budapest', 'country_id' => $country->id]);
        \App\Models\Location::create([
            'name' => 'Akvárium Klub',
            'address' => 'Erzsébet tér 12.',
            'city_id' => $city->id,
            'country_id' => $country->id,
            'lat' => 47.498,
            'lng' => 19.055
        ]);

    // Most már mehet az EventSeeder
    $this->call([
        EventSeeder::class,
    ]);

        // 1. Create or fetch test user
        $testUser = \App\Models\User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password')
            ]
        );

        // 2. Define raver user seeds
        $ravers = [
            [
                'name' => 'Citra',
                'email' => 'citra@example.com',
                'password' => Hash::make('password'),
                'bio' => 'Techno raver 🎛️, let\'s explore Budapest nightlife together!',
                'age' => 23,
                'avatar_url' => 'https://images.unsplash.com/photo-1759853900346-8d1ee0af7ca8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMGFzaWFuJTIwd29tYW4lMjBwb3J0cmFpdCUyMGRhcmslMjBqYWNrZXQlMjBmYXNoaW9ufGVufDF8fHx8MTc3NjI5MjQ3NHww&ixlib=rb-4.1.0&q=80&w=1080',
                'photos' => [
                    'https://images.unsplash.com/photo-1759853900346-8d1ee0af7ca8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMGFzaWFuJTIwd29tYW4lMjBwb3J0cmFpdCUyMGRhcmslMjBqYWNrZXQlMjBmYXNoaW9ufGVufDF8fHx8MTc3NjI5MjQ3NHww&ixlib=rb-4.1.0&q=80&w=1080',
                    'https://images.unsplash.com/photo-1588072719654-9a95b5bb42d4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMHdvbWFuJTIwcG9ydHJhaXQlMjBtdXNpYyUyMGZlc3RpdmFsJTIwbmlnaHR8ZW58MXx8fHwxNzc2MjkyNTE0fDA&ixlib=rb-4.1.0&q=80&w=1080',
                    'https://images.unsplash.com/photo-1724118135600-35009a8d6a89?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMG1hbiUyMHBvcnRyYWl0JTIwdXJiYW4lMjBzdHlsZXxlbnwxfHx8fDE3NzYyOTI1MTR8MA&ixlib=rb-4.1.0&q=80&w=1080'
                ],
                'pre_swipe' => true
            ],
            [
                'name' => 'Alya',
                'email' => 'alya@example.com',
                'password' => Hash::make('password'),
                'bio' => 'Melodic house enthusiast 🎵, looking for a festival buddy!',
                'age' => 24,
                'avatar_url' => 'https://images.unsplash.com/photo-1588072719654-9a95b5bb42d4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMHdvbWFuJTIwcG9ydHJhaXQlMjBtdXNpYyUyMGZlc3RpdmFsJTIwbmlnaHR8ZW58MXx8fHwxNzc2MjkyNTE0fDA&ixlib=rb-4.1.0&q=80&w=1080',
                'photos' => [
                    'https://images.unsplash.com/photo-1588072719654-9a95b5bb42d4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMHdvbWFuJTIwcG9ydHJhaXQlMjBtdXNpYyUyMGZlc3RpdmFsJTIwbmlnaHR8ZW58MXx8fHwxNzc2MjkyNTE0fDA&ixlib=rb-4.1.0&q=80&w=1080',
                    'https://images.unsplash.com/photo-1724118135600-35009a8d6a89?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMG1hbiUyMHBvcnRyYWl0JTIwdXJiYW4lMjBzdHlsZXxlbnwxfHx8fDE3NzYyOTI1MTR8MA&ixlib=rb-4.1.0&q=80&w=1080',
                    'https://images.unsplash.com/photo-1759853900346-8d1ee0af7ca8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMGFzaWFuJTIwd29tYW4lMjBwb3J0cmFpdCUyMGRhcmslMjBqYWNrZXQlMjBmYXNoaW9ufGVufDF8fHx8MTc3NjI5MjQ3NHww&ixlib=rb-4.1.0&q=80&w=1080'
                ],
                'pre_swipe' => false
            ],
            [
                'name' => 'Dano',
                'email' => 'dano@example.com',
                'password' => Hash::make('password'),
                'bio' => 'EDM and dance lover ⚡, always down for a warehouse rave.',
                'age' => 27,
                'avatar_url' => 'https://images.unsplash.com/photo-1724118135600-35009a8d6a89?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMG1hbiUyMHBvcnRyYWl0JTIwdXJiYW4lMjBzdHlsZXxlbnwxfHx8fDE3NzYyOTI1MTR8MA&ixlib=rb-4.1.0&q=80&w=1080',
                'photos' => [
                    'https://images.unsplash.com/photo-1724118135600-35009a8d6a89?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMG1hbiUyMHBvcnRyYWl0JTIwdXJiYW4lMjBzdHlsZXxlbnwxfHx8fDE3NzYyOTI1MTR8MA&ixlib=rb-4.1.0&q=80&w=1080',
                    'https://images.unsplash.com/photo-1759853900346-8d1ee0af7ca8?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMGFzaWFuJTIwd29tYW4lMjBwb3J0cmFpdCUyMGRhcmslMjBqYWNrZXQlMjBmYXNoaW9ufGVufDF8fHx8MTc3NjI5MjQ3NHww&ixlib=rb-4.1.0&q=80&w=1080',
                    'https://images.unsplash.com/photo-1588072719654-9a95b5bb42d4?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHx5b3VuZyUyMHdvbWFuJTIwcG9ydHJhaXQlMjBtdXNpYyUyMGZlc3RpdmFsJTIwbmlnaHR8ZW58MXx8fHwxNzc2MjkyNTE0fDA&ixlib=rb-4.1.0&q=80&w=1080'
                ],
                'pre_swipe' => false
            ]
        ];

        foreach ($ravers as $r) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $r['email']],
                [
                    'name' => $r['name'],
                    'password' => $r['password'],
                    'bio' => $r['bio'],
                    'age' => $r['age'],
                    'avatar_url' => $r['avatar_url']
                ]
            );

            // Add user photos
            $user->photos()->delete(); // Clear old seeds if any
            foreach ($r['photos'] as $pUrl) {
                $user->photos()->create(['photo_url' => $pUrl]);
            }

            // Citra likes Test User
            if ($r['pre_swipe']) {
                \App\Models\UserSwipe::updateOrCreate(
                    [
                        'swiper_id' => $user->id,
                        'swiped_id' => $testUser->id
                    ],
                    [
                        'direction' => 'right'
                    ]
                );
            }
        }

        $this->call(LocationSeeder::class);
    }
}
