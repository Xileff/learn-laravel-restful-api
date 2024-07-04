<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
}
