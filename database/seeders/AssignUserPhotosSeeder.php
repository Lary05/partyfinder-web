<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserPhoto;

class AssignUserPhotosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Assign a unique avatar
            $user->update([
                'avatar' => "https://picsum.photos/seed/avatar_{$user->id}/400/400"
            ]);

            // Assign a high-quality photo in user_photos
            UserPhoto::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'sort_order' => 1
                ],
                [
                    'photo_url' => "https://picsum.photos/seed/photo_{$user->id}/800/1200"
                ]
            );
        }
    }
}