<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\AddressSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
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

    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();
        $this->withHeaders(['Authorization' => $user->token])
            ->post("/api/contacts/$contact->id/addresses", [
                'street' => '',
                'city' => '',
                'province' => '',
                'country' => '',
                'postal_code' => '',
            ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'country' => [
                        'The country field is required.'
                    ]
                ]
            ]);
    }

    public function testCreateDifferentUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user1 = User::where('username', 'test')->first();
        $user2 = User::where('username', 'test2')->first();
        $contactUser1 = Contact::where('user_id', $user1->id)->first();
        $this->withHeaders(['Authorization' => $user2->token])
            ->post("/api/contacts/$contactUser1->id/addresses", [
                'street' => 'street test',
                'city' => 'city test',
                'province' => 'province test',
                'country' => 'country test',
                'postal_code' => '112233',
            ])
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $user = User::firstWhere('username', 'test');
        $contact = Contact::firstWhere('user_id', $user->id);
        $address = Address::firstWhere('contact_id', $contact->id);

        $this->withHeaders(['Authorization' => $user->token])
            ->get("/api/contacts/$contact->id/addresses/$address->id")
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'street' => $address->street,
                    'city' => $address->city,
                    'province' => $address->province,
                    'country' => $address->country,
                    'postal_code' => $address->postal_code
                ]
            ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $user = User::firstWhere('username', 'test');
        $contact = Contact::firstWhere('user_id', $user->id);
        $address = Address::firstWhere('contact_id', $contact->id);

        $result = $this->withHeaders(['Authorization' => $user->token])
            ->get("/api/contacts/$contact->id/addresses/" . ($address->id + 1))
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found'
                    ],
                ]
            ]);
    }

    public function testGetDifferentUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $user = User::firstWhere('username', 'test');
        $contact = Contact::firstWhere('user_id', $user->id);
        $address = Address::firstWhere('contact_id', $contact->id);

        $user2 = User::firstWhere('username', 'test2');

        $this->withHeaders(['Authorization' => $user2->token])
            ->get("/api/contacts/$contact->id/addresses/$address->id")
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ]);
    }

    public function testUpdateSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $user = User::firstWhere('username', 'test');
        $contact = Contact::firstWhere('user_id', $user->id);
        $address = Address::firstWhere('contact_id', $contact->id);

        $this->withHeaders(['Authorization' => $user->token])
            ->put("/api/contacts/$contact->id/addresses/$address->id", [
                'street' => 'street updated',
                'city' => 'city updated',
                'province' => 'province updated',
                'country' => 'country updated',
                'postal_code' => '112233'
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'street' => 'street updated',
                    'city' => 'city updated',
                    'province' => 'province updated',
                    'country' => 'country updated',
                    'postal_code' => '112233'
                ]
            ]);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $user = User::firstWhere('username', 'test');
        $contact = Contact::firstWhere('user_id', $user->id);
        $address = Address::firstWhere('contact_id', $contact->id);

        $payload = [
            'street' => '',
            'city' => '',
            'province' => '',
            'country' => '',
            'postal_code' => ''
        ];

        $this->withHeaders(['Authorization' => $user->token])
            ->put("/api/contacts/$contact->id/addresses/$address->id", $payload)
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'country' => [
                        'The country field is required.'
                    ]
                ]
            ]);
    }

    public function testUpdateDifferentUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class, AddressSeeder::class]);

        $user1 = User::firstWhere('username', 'test');
        $contactUser1 = Contact::firstWhere('user_id', $user1->id);
        $addressUser1 = Address::firstWhere('contact_id', $contactUser1->id);

        $user2 = User::firstWhere('username', 'test2');

        $this->withHeaders(['Authorization' => $user2->token])
            ->put("/api/contacts/$contactUser1->id/addresses/$addressUser1->id", [
                'street' => 'street updated',
                'city' => 'city updated',
                'province' => 'province updated',
                'country' => 'country updated',
                'postal_code' => '112233'
            ])
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ]);
    }
}
