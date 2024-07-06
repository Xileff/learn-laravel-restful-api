<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AddressTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();
        $this->withHeaders(['Authorization' => $user->token])
            ->post("/api/contacts/$contact->id/addresses", [
                'street' => 'street test',
                'city' => 'city test',
                'province' => 'province test',
                'country' => 'country test',
                'postal_code' => '112233',
            ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'street' => 'street test',
                    'city' => 'city test',
                    'province' => 'province test',
                    'country' => 'country test',
                    'postal_code' => '112233',
                ]
            ]);

        $address = Address::where('contact_id', $contact->id)->first();
        $this->assertNotNull($address);
    }
}
