<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ListAddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstWhere('username', 'test');
        $contact = Contact::firstWhere('user_id', $user->id);

        for ($i = 0; $i < 10; $i++) {
            $contact->addresses()->create([
                'street' => "street $i",
                'city' => "city $i",
                'province' => "province $i",
                'country' => "country $i",
                'postal_code' => '112233',
                'contact_id' => $contact->id,
            ]);
        }

        $user2 = User::firstWhere('username', 'test2');
        $contactUser2 = Contact::firstWhere('user_id', $user2->id);

        for ($i = 10; $i < 20; $i++) {
            $contactUser2->addresses()->create([
                'street' => "street $i",
                'city' => "city $i",
                'province' => "province $i",
                'country' => "country $i",
                'postal_code' => '112233',
                'contact_id' => $contactUser2->id,
            ]);
        }
    }
}
