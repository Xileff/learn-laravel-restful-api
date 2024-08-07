<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Database\Seeders\ContactSeeder;
use Database\Seeders\SearchSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ContactTest extends TestCase
{
    public function testCreateSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->withHeaders(['Authorization' => 'test'])
            ->post('/api/contacts', [
                'first_name' => 'test',
                'last_name' => 'test',
                'email' => 'test@example.com',
                'phone' => '123456789',
            ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'first_name' => 'test',
                    'last_name' => 'test',
                    'email' => 'test@example.com',
                    'phone' => '123456789',
                ]
            ]);
    }

    public function testCreateFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->withHeaders(['Authorization' => 'test'])
            ->post('/api/contacts', [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
            ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => [
                        'The first name field is required.'
                    ],
                ]
            ]);
    }

    public function testCreateUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this->withHeaders(['Authorization' => 'salah'])
            ->post('/api/contacts', [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
            ])
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized'
                    ],
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();

        $this->withHeaders(['Authorization' => $user->token])
            ->get("/api/contacts/$contact->id")
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $contact->id,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                ]
            ]);
    }

    public function testGetNotFound()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();

        $this->withHeaders(['Authorization' => $user->token])
            ->get("/api/contacts/" . ($contact->id + 1))
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ]);
    }

    public function testGetDifferentUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);
        $user1 = User::where('username', 'test')->first();
        $user2 = User::where('username', 'test2')->first();
        $contact = Contact::where('user_id', $user1->id)->first();

        $this->withHeaders(['Authorization' => $user2->token])
            ->get("/api/contacts/$contact->id")
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
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();

        $this->withHeaders(['Authorization' => $user->token])
            ->put("/api/contacts/$contact->id", [
                'first_name' => 'updated',
                'last_name' => 'updated',
                'email' => 'updated@example.com',
                'phone' => '123456789',
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $contact->id,
                    'first_name' => 'updated',
                    'last_name' => 'updated',
                    'email' => 'updated@example.com',
                    'phone' => '123456789',
                ]
            ]);
    }

    public function testUpdateValidationError()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();

        $this->withHeaders(['Authorization' => $user->token])
            ->put("/api/contacts/$contact->id", [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
            ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => [
                        'The first name field is required.'
                    ]
                ]
            ]);
    }

    public function testUpdateDifferentUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user1 = User::where('username', 'test')->first();
        $user2 = User::where('username', 'test2')->first();
        $contactUser1 = Contact::where('user_id', $user1->id)->first();

        $this->withHeaders(['Authorization' => $user2->token])
            ->put("/api/contacts/$contactUser1->id", [
                'first_name' => 'updated',
                'last_name' => 'updated',
                'email' => 'updated@example.com',
                'phone' => '123456789',
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

    public function testDeleteSuccess()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user = User::where('username', 'test')->first();
        $contact = Contact::where('user_id', $user->id)->first();

        $this->withHeaders(['Authorization' => $user->token])
            ->delete("/api/contacts/$contact->id")
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);
    }

    public function testDeleteDifferentUser()
    {
        $this->seed([UserSeeder::class, ContactSeeder::class]);

        $user1 = User::where('username', 'test')->first();
        $user2 = User::where('username', 'test2')->first();
        $contactUser1 = Contact::where('user_id', $user1->id)->first();

        $this->withHeaders(['Authorization' => $user2->token])
            ->delete("/api/contacts/$contactUser1->id")
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'not found'
                    ]
                ]
            ]);
    }

    public function testSearchByFirstName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $user = User::where('username', 'test')->first();

        $response = $this->withHeaders(['Authorization' => $user->token])
            ->get('/api/contacts?name=first')
            ->assertStatus(200)
            ->json();

        $this->assertCount(10, $response['data']);
        $this->assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByLastName()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $user = User::where('username', 'test')->first();

        $response = $this->withHeaders(['Authorization' => $user->token])
            ->get('/api/contacts?name=last')
            ->assertStatus(200)
            ->json();

        $this->assertCount(10, $response['data']);
        $this->assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByEmail()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $user = User::where('username', 'test')->first();

        $response = $this->withHeaders(['Authorization' => $user->token])
            ->get('/api/contacts?email=test')
            ->assertStatus(200)
            ->json();

        $this->assertCount(10, $response['data']);
        $this->assertEquals(20, $response['meta']['total']);
    }

    public function testSearchByPhone()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $user = User::where('username', 'test')->first();

        $response = $this->withHeaders(['Authorization' => $user->token])
            ->get('/api/contacts?phone=1111')
            ->assertStatus(200)
            ->json();

        $this->assertCount(10, $response['data']);
        $this->assertEquals(20, $response['meta']['total']);
    }

    public function testSearchNotFound()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $user = User::where('username', 'test')->first();

        $response = $this->withHeaders(['Authorization' => $user->token])
            ->get('/api/contacts?name=notfound')
            ->assertStatus(200)
            ->json();

        $this->assertCount(0, $response['data']);
        $this->assertEquals(0, $response['meta']['total']);
    }

    public function testSearchWithPage()
    {
        $this->seed([UserSeeder::class, SearchSeeder::class]);
        $user = User::where('username', 'test')->first();

        $response = $this->withHeaders(['Authorization' => $user->token])
            ->get('/api/contacts?size=5&page=2')
            ->assertStatus(200)
            ->json();

        $this->assertCount(5, $response['data']);
        $this->assertEquals(20, $response['meta']['total']);
        $this->assertEquals(2, $response['meta']['current_page']);
    }
}
