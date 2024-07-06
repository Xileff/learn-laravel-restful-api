<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::firstWhere('username', 'test');
        $contactUser1 = Contact::firstWhere('user_id', $user1->id);

        Address::create([
            'street' => 'street test',
            'city' => 'city test',
            'province' => 'province test',
            'country' => 'country test',
            'postal_code' => '112233',
            'contact_id' => $contactUser1->id
        ]);

        $user2 = User::firstWhere('username', 'test2');
        $contactUser2 = Contact::firstWhere('user_id', $user2->id);

        Address::create([
            'street' => 'street test 2',
            'city' => 'city test 2',
            'province' => 'province test 2',
            'country' => 'country test 2',
            'postal_code' => '112233',
            'contact_id' => $contactUser2->id
        ]);
    }
}
