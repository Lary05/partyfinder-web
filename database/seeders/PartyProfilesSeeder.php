<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\UserPhoto;
use Carbon\Carbon;

class PartyProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = Hash::make('password123'); // Minden fiók jelszava: password123

        $profiles = [
            [
                'name' => 'Anna',
                'gender' => 'female',
                'age' => 23,
                'bio' => "Techno & House 🖤\nBootshaus a második otthonom. Keresem azt, akivel végigtolhatjuk a következő fesztiválszezont! Ha bírod az Afterlife kiadó zenéit, már jó barátok leszünk.",
                'avatar' => 'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1485872299829-c673f5194813?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Dávid',
                'gender' => 'male',
                'age' => 26,
                'bio' => "Tech-house DJ / Producer 🎧\nÁltalában a DJ pult mögött vagyok, de ha nem, akkor a küzdőtéren találod meg a legjobb ritmusokat. Let's rave!",
                'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1550927312-320e8b28a2a5?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1520483601560-389dff434fdf?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Léna',
                'gender' => 'female',
                'age' => 21,
                'bio' => "DnB head! 🚀\nSziget, Balaton Sound, Rampage... mindenhol ott vagyok. Imádok táncolni hajnalig. Jössz a következő buliba?",
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1496360166961-10a51d5f367a?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1545128485-c400e7702796?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Mark',
                'gender' => 'male',
                'age' => 28,
                'bio' => "Melodic Techno & Deep House.\nAzokat a bulikat szeretem, ahol a fények és a zene egy utazásra visznek. Keresem a megfelelő party partnert.",
                'avatar' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1514525253161-7a46d19cd819?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Szofi',
                'gender' => 'female',
                'age' => 24,
                'bio' => "EDM és pörgés! 🌟\nFesztivál lány vagyok, csillámporral az arcomon. A zene az egyetlen menekülésem. Swipe right, ha szereted Martin Garrixet vagy Fisher-t!",
                'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1526478806334-5fd488fcaabc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Bence',
                'gender' => 'male',
                'age' => 25,
                'bio' => "Hard Techno. 150 BPM alatt nem is indulok el otthonról. ⛓️\nSötét klubok, jó társaság, hajnali 6-kor is a parketten.",
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Viktória',
                'gender' => 'female',
                'age' => 27,
                'bio' => "House zene és egy jó koktél a barátokkal, majd irány a klub! 🍸✨\nOlyan srácot keresek, aki tudja, mi a jó ízlés a zenében.",
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Tomi',
                'gender' => 'male',
                'age' => 22,
                'bio' => "Good vibes only. ✌️\nDeep house és minimal. Hétvégente általában valami underground pesti buliban vagyok.",
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Niki',
                'gender' => 'female',
                'age' => 20,
                'bio' => "Új vagyok a városban! Keresem a legjobb klubokat és azokat az embereket, akikkel hajnalig lehet veretni. Hit me up! 💃",
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1516081191062-817666ee3b73?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Gergő',
                'gender' => 'male',
                'age' => 29,
                'bio' => "Old school techno, vinyl gyűjtő. 💿\nA mai modern zenék jók, de a régi klasszikusokat semmi sem veri. Ha szereted a Carl Cox szetteket, egyezni fogunk.",
                'avatar' => 'https://images.unsplash.com/photo-1488161628813-04466f872507?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1520262454473-a1a82276a574?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Eszti',
                'gender' => 'female',
                'age' => 25,
                'bio' => "A State of Trance! 🌈\nA dallamos elektronikus zene híve vagyok. Keresem azt a srácot, aki felvesz a nyakába a nagyszínpadnál.",
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Patrik',
                'gender' => 'male',
                'age' => 24,
                'bio' => "Az after party a legjobb party. 🕶️\nMinimal house, romkocsmák, hajnali napfelkelték.",
                'avatar' => 'https://images.unsplash.com/photo-1528892952291-009c663ce843?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1493225457224-eda1ee8c3103?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Lilla',
                'gender' => 'female',
                'age' => 22,
                'bio' => "Barátnőkkel indul az este, de ki tudja hol végződik? 🎉\nSzeretem a spontán bulikat és a jó fej embereket.",
                'avatar' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1495360010541-f48722b34f7d?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Alex',
                'gender' => 'male',
                'age' => 26,
                'bio' => "Tudom a jelszót a legjobb underground klubokba. 😉\nEvent promoter vagyok, írj rám, ha kell guestlist valahova!",
                'avatar' => 'https://images.unsplash.com/photo-1480455624313-e29b44bbfde1?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?auto=format&fit=crop&w=800&q=80'
                ]
            ],
            [
                'name' => 'Kata',
                'gender' => 'female',
                'age' => 23,
                'bio' => "BASS MUSIC! Headbanger lány vagyok. 😈\nDubstep, riddim, trap. Olyan párt keresek, aki bírja a moshpiteket!",
                'avatar' => 'https://images.unsplash.com/photo-1554151228-14d9def656e4?auto=format&fit=crop&w=800&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1541595913753-29495b54249a?auto=format&fit=crop&w=800&q=80'
                ]
            ]
        ];

        foreach ($profiles as $index => $data) {
            $email = strtolower(str_replace(' ', '', $data['name'])) . $index . '@example.com';

            // Fiúk és lányok geolokációja (Budapest környéki véletlenszerű pontok, hogy bedobja őket a távolságszűrő)
            $lat = 47.4979 + (mt_rand(-50, 50) / 1000);
            $lng = 19.0402 + (mt_rand(-50, 50) / 1000);

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $data['name'],
                    'password' => $password,
                    'gender' => $data['gender'],
                    'birth_date' => Carbon::now()->subYears($data['age'])->format('Y-m-d'),
                    'age' => $data['age'],
                    'bio' => $data['bio'],
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'avatar' => $data['avatar'],
                    // Discovery default beállítások
                    'discovery_distance' => 50,
                    'discovery_min_age' => 18,
                    'discovery_max_age' => 40,
                    'discovery_gender' => $data['gender'] === 'male' ? 'female' : 'male',
                    'is_admin' => 0
                ]
            );

            // Töröljük a régi képeit, ha már léteznek (hogy többszöri futtatásnál ne duplikálja)
            UserPhoto::where('user_id', $user->id)->delete();

            
            // Adjuk hozzá a kiegészítő képeket a galériához
            if (isset($data['photos'])) {
                foreach ($data['photos'] as $photoIndex => $photoUrl) {
                    UserPhoto::create([
                        'user_id' => $user->id,
                        'photo_url' => $photoUrl, 
                        'sort_order' => $photoIndex
                    ]);
                }
            }
        }

        $this->command->info('✅ 15 valósághű bulis profil (bióval és képekkel) sikeresen legenerálva!');
    }
}