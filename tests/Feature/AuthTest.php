<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(
    function () {
        $this->user = User::create([
            "email" => "test@gmail.com",
            "password" => "test123",
            "phone_number" => "08123456789"
        ]);
    }
);

test('Register Test', function () {
    $response = $this->postJson(
        '/api/v1/register',
        [
            "email" => "test2@gmail.com",
            "password" => "test1234",
            "phone_number" => "0812345678"
        ]
    );

    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', true)
                ->where('message', 'Akun berhasil dibuat')
                ->has('token')
                ->has('data')
        );

    expect(User::where([['email', '=', 'test@gmail.com'], ['phone_number', '=', '08123456789']])->exists())->toBeTrue();
});

test('Login Test', function () {
    $response = $this->postJson(
        '/api/v1/login',
        [
            'email' => 'test@gmail.com',
            'password' => 'test123',
        ]
    );

    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', true)
                ->where('message', 'Login berhasil')
                ->has('token')
                ->has('data')
        );
});

// test('Logout Test', function () {
//     $user = User::factory()->create();

//     Sanctum::actingAs($user);
//     $response = $this
//         ->getJson(
//             '/api/v1/logout',
//         );

//     $response
//         ->assertStatus(200)
//         ->assertJson(
//             fn(AssertableJson $json) =>
//             $json->where('status', true)
//                 ->where('message', 'Logged Out')
//         );
// });
