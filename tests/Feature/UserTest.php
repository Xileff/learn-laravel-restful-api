<?php

namespace Tests\Feature;

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
}
