<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'username' => 'xilef',
            'password' => 'rahasia',
            'name' => 'Felix'
        ])
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    'username' => 'xilef',
                    'name' => 'Felix'
                ]
            ]);
    }

    public function testRegisterFailed()
    {
        $this->post('/api/users', [
            'username' => '',
            'password' => '',
            'name' => ''
        ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'The username field is required.'
                    ],
                    'password' => [
                        'The password field is required.'
                    ],
                    'name' => [
                        'The name field is required.'
                    ]
                ]
            ]);
    }

    public function testRegisterDuplicateUsername()
    {
        $this->testRegisterSuccess();

        $this->post('/api/users', [
            'username' => 'xilef',
            'password' => 'rahasia',
            'name' => 'Felix'
        ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'username' => [
                        'username already registered' // error define sendiri di controller
                    ],
                ]
            ]);
    }

    public function testLoginSuccess()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ])->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test',
                ]
            ]);

        $user = User::where('username', 'test')->first();
        $this->assertNotNull($user->token);
    }

    public function testLoginWrongUsername()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'salah',
            'password' => 'test'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'invalid username or password'
                    ]
                ]
            ]);
    }

    public function testLoginWrongPassword()
    {
        $this->seed([UserSeeder::class]);
        $this->post('/api/users/login', [
            'username' => 'test',
            'password' => 'salah'
        ])->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'invalid username or password'
                    ]
                ]
            ]);
    }

    public function testGetSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this
            ->withHeaders(['Authorization' => 'test'])
            ->get('/api/users/current')
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);
    }

    public function testGetUnauthorized()
    {
        $this->seed([UserSeeder::class]);

        $this
            ->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized',
                    ]
                ]
            ]);
    }

    public function testGetInvalidToken()
    {
        $this->seed([UserSeeder::class]);

        $this
            ->withHeaders(['Authorization' => 'salah'])
            ->get('/api/users/current')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [
                    'message' => [
                        'unauthorized',
                    ]
                ]
            ]);
    }

    public function testUpdatePassword()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->withHeaders([
            'Authorization' => 'test'
        ])
            ->patch('/api/users/current', [
                'password' => 'updated'
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $oldUser->id,
                    'username' => 'test',
                    'name' => 'test'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        $this->assertNotEquals($newUser->password, $oldUser->password);
    }

    public function testUpdateName()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->withHeaders(['Authorization' => 'test'])
            ->patch('/api/users/current', [
                'name' => 'updated'
            ])
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $oldUser->id,
                    'username' => 'test',
                    'name' => 'updated'
                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        $this->assertNotEquals($newUser->name, $oldUser->name);
    }

    public function testUpdateFailed()
    {
        $this->seed([UserSeeder::class]);
        $oldUser = User::where('username', 'test')->first();

        $this->withHeaders(['Authorization' => 'test'])
            ->patch('/api/users/current', [
                'name' => 'updatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdatedupdated'
            ])
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'name' => [ // dari dto UserUpdateRequest
                        'The name field must not be greater than 100 characters.'
                    ]

                ]
            ]);

        $newUser = User::where('username', 'test')->first();
        $this->assertEquals($newUser->name, $oldUser->name);
    }

    public function testLogoutSuccess()
    {
        $this->seed([UserSeeder::class]);

        $this->withHeaders(['Authorization' => 'test'])
            ->delete('/api/users/logout')
            ->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        $user = User::where('username', 'test')->first();
        $this->assertNull($user->token);

        $this->withHeaders(['Authorization', 'test'])
            ->get('/api/users/current')
            ->assertStatus(401);
    }

    public function testLogoutFailed()
    {
        $this->seed([UserSeeder::class]);

        $this->withHeaders(['Authorization' => 'salah'])
            ->delete('/api/users/logout')
            ->assertStatus(401)
            ->assertJson([
                'errors' => [ // dari middleware
                    'message' => [
                        'unauthorized'
                    ]
                ]
            ]);
    }
}
