<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::where('username', 'test')->first();
        $user2 = User::where('username', 'test2')->first();

        Contact::create([
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@example.com',
            'phone' => '123456789',
            'user_id' => $user1->id,
        ]);

        Contact::create([
            'first_name' => 'test',
            'last_name' => 'test',
            'email' => 'test@example.com',
            'phone' => '123456789',
            'user_id' => $user2->id,
        ]);
    }
}
