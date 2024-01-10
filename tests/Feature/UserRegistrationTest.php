<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRegistrationTest extends TestCase
{
    public function testRegisterSuccess()
    {
        $this->post('/api/users', [
            'email' => 'adit@gmail.com',
            'password' => 'rahasia',
            'name' => 'aditya bayu'
        ])->assertStatus(201)
            ->assertJson([
                "data" => [
                    'email' => 'adit@gmail.com',
                    'name' => 'aditya bayu'
                ]
            ]);
    }
}
