<?php

namespace Tests\Feature;

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
            ->assertStatus(400)
            ->assertJson([
                'errors' => [
                    'first_name' => [
                        'The first name field is required.'
                    ],
                ]
            ]);
    }
}
