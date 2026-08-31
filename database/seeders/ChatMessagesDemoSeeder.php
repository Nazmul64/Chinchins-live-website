<?php

namespace Database\Seeders;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ChatMessagesDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find or create primary viewer user (e.g. Nazmul / Current User)
        $primaryUser = User::where('name', 'LIKE', '%Nazmul%')->orWhere('id', 1)->first();
        if (!$primaryUser) {
            $primaryUser = User::first();
        }

        // Demo contacts matching Screenshot #1
        $demoContacts = [
            [
                'name'         => 'sohan',
                'nickname'     => 'sohan',
                'gender'       => 'male',
                'phone'        => '01711000001',
                'msg_type'     => 'text',
                'msg_text'     => 'Hi Nazmul! I saw you visited my profile. Call me now?',
                'unread'       => 1,
                'time_offset'  => 59, // 59 mins ago
                'is_online'    => true,
            ],
            [
                'name'         => 'Gulabi',
                'nickname'     => 'Gulabi ❤️',
                'gender'       => 'female',
                'phone'        => '01711000002',
                'msg_type'     => 'video_call',
                'msg_text'     => '[Video Call]',
                'unread'       => 0,
                'time_offset'  => 65,
                'is_online'    => true,
            ],
            [
                'name'         => 'SimranGlow_',
                'nickname'     => 'SimranGlow_ ⭐',
                'gender'       => 'female',
                'phone'        => '01711000003',
                'msg_type'     => 'image',
                'msg_text'     => '[Image]',
                'unread'       => 0,
                'time_offset'  => 70,
                'is_online'    => true,
            ],
            [
                'name'         => 'DanielleRose',
                'nickname'     => 'DanielleRose 🦋',
                'gender'       => 'female',
                'phone'        => '01711000004',
                'msg_type'     => 'video_call',
                'msg_text'     => '[Video Call]',
                'unread'       => 0,
                'time_offset'  => 75,
                'is_online'    => true,
            ],
            [
                'name'         => 'Sumaiya jannat',
                'nickname'     => 'Sumaiya jannat',
                'gender'       => 'female',
                'phone'        => '01711000005',
                'msg_type'     => 'image',
                'msg_text'     => '[Image]',
                'unread'       => 2,
                'time_offset'  => 80,
                'is_online'    => true,
            ],
            [
                'name'         => 'Ameena',
                'nickname'     => 'Ameena',
                'gender'       => 'female',
                'phone'        => '01711000006',
                'msg_type'     => 'text',
                'msg_text'     => 'আমাকে দেখতে চাও?',
                'unread'       => 1,
                'time_offset'  => 1440, // Yesterday
                'is_online'    => true,
            ],
            [
                'name'         => 'Ashna',
                'nickname'     => 'Ashna',
                'gender'       => 'female',
                'phone'        => '01711000007',
                'msg_type'     => 'text',
                'msg_text'     => 'আমি খুব হট, এখন তোমার শরীরের ...',
                'unread'       => 2,
                'time_offset'  => 1500,
                'is_online'    => true,
            ],
            [
                'name'         => 'Airiss',
                'nickname'     => 'Airiss ❤️',
                'gender'       => 'female',
                'phone'        => '01711000008',
                'msg_type'     => 'text',
                'msg_text'     => 'love u babe',
                'unread'       => 1,
                'time_offset'  => 1600,
                'is_online'    => true,
            ],
        ];

        foreach ($demoContacts as $idx => $contactData) {
            $user = User::firstOrCreate(
                ['phone' => $contactData['phone']],
                [
                    'email'           => strtolower(preg_replace('/[^a-z0-9]/', '', $contactData['name'])) . ($idx + 1) . '@chinchins.live',
                    'account_id'      => '100008900' . ($idx + 1),
                    'name'            => $contactData['name'],
                    'nickname'        => $contactData['nickname'],
                    'password'        => Hash::make('password123'),
                    'gender'          => $contactData['gender'],
                    'is_active'       => true,
                    'online_status'   => 'online',
                    'video_call_rate' => 100,
                    'level'           => rand(3, 8),
                ]
            );

            // Create message to primary user
            $createdAt = now()->subMinutes($contactData['time_offset']);
            ChatMessage::create([
                'sender_id'   => $user->id,
                'receiver_id' => $primaryUser->id,
                'type'        => $contactData['msg_type'],
                'message'     => $contactData['msg_text'],
                'media_url'   => $contactData['msg_type'] === 'image' ? asset('uploads/sms_profile/sample_photo.jpg') : null,
                'is_read'     => $contactData['unread'] === 0,
                'is_free'     => true,
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ]);

            if ($contactData['unread'] > 1) {
                ChatMessage::create([
                    'sender_id'   => $user->id,
                    'receiver_id' => $primaryUser->id,
                    'type'        => 'text',
                    'message'     => 'Hey check this out!',
                    'is_read'     => false,
                    'is_free'     => true,
                    'created_at'  => $createdAt->addMinute(),
                    'updated_at'  => $createdAt->addMinute(),
                ]);
            }
        }
    }
}
