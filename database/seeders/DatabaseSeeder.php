<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'name'       => 'Jhon Deo',
                'nickname'   => 'Admin',
                'phone'      => '01700000000',
                'password'   => bcrypt('admin@gmail.com'),
                'account_id' => '1000000001',
                'level'      => 'Lv10',
                'is_active'  => true,
                'email_verified_at' => now(),
            ]
        );

        // Chinchins Live Featured Mock Profile (matching screenshot Ayeena04)
        User::updateOrCreate(
            ['email' => 'ayeena@chinchins.live'],
            [
                'first_name'          => 'Ayeena',
                'last_name'           => '04',
                'name'                => 'Ayeena04',
                'nickname'            => 'Ayeena04',
                'phone'               => '01711000111',
                'password'            => bcrypt('password123'),
                'account_id'          => '602281635',
                'is_verified'         => true,
                'is_active'           => true,
                'level'               => 'Lv4',
                'country'             => 'Pakistan',
                'gender'              => 'female',
                'age'                 => 27,
                'introduction'        => 'Sweet girl looking for honest talk ❤️',
                'languages'           => ['English', 'Urdu', 'Hindi'],
                'tags'                => ['Live video', 'Music', 'Singing', 'Chat'],
                'video_call_rate'     => 1800,
                'close_friends_count' => 0,
                'gallery_images'      => [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=600&auto=format&fit=crop&q=80',
                ],
                'cover_photo'         => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&auto=format&fit=crop&q=80',
                'avatar'              => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&auto=format&fit=crop&q=80',
            ]
        );

        // Example User from user request (Nazmul Hossain)
        User::updateOrCreate(
            ['email' => 'nazmul@gmail.com'],
            [
                'first_name'      => 'Nazmul',
                'last_name'       => 'Hossain',
                'name'            => 'Nazmul Hossain',
                'nickname'        => 'Nazmul',
                'phone'           => '01706640864',
                'password'        => bcrypt('nazmul@gmail.com'),
                'account_id'      => '880170664086',
                'gender'          => 'female',
                'age'             => 27,
                'country'         => 'Pakistan',
                'level'           => 'Lv4',
                'is_active'       => true,
                'introduction'    => 'Sweet girl looking for honest talk ❤️',
                'languages'       => ['English', 'Urdu', 'Bengali'],
                'tags'            => ['Live video', 'Music', 'Singing'],
                'video_call_rate' => 1800,
                'gallery_images'  => [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=600&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=600&auto=format&fit=crop&q=80',
                ],
            ]
        );
    }
}
