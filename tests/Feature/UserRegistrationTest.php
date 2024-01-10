<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
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
    public function testLoginSuccess()
    {
        // Membuat user untuk diuji
        $user = User::factory()->create([
            'email' => 'adit@gmail.com',
            'password' => bcrypt('rahasia'),
            'name' => 'aditya bayu',
        ]);

        // Mengirimkan permintaan login
        $response = $this->post('/api/login', [
            'email' => 'adit@gmail.com',
            'password' => 'rahasia',
        ]);

        // Memeriksa status respons dan struktur JSON yang diharapkan
        $response->assertStatus(200)
            ->assertJsonStructure(['user', 'token']);

        // Memeriksa apakah respons mencakup informasi pengguna yang benar
        $response->assertJson([
            'user' => [
                'email' => 'adit@gmail.com',
                'name' => 'aditya bayu',
            ],
        ]);
    }

    public function testLoginFailure()
    {
        // Mengirimkan permintaan login dengan informasi login yang salah
        $response = $this->post('/api/login', [
            'email' => 'adit@gmail.com',
            'password' => 'password_salah',
        ]);

        // Memeriksa status respons dan pesan kesalahan yang diharapkan
        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid login details']);
    }
}
