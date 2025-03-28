<?php

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

// test('Register Test', function () {
//     $response = $this->postJson(
//         '/api/v1/register',
//         [
//             "email" => fake()->email(),
//             "password" => fake()->password(7),
//             "phone_number" => fake()->phoneNumber()
//         ]
//     );

//     $response
//         ->assertStatus(200)
//         ->assertJson(
//             fn(AssertableJson $json) =>
//             $json->where('status', true)
//                 ->where('message', 'Akun berhasil dibuat')
//                 ->has('token')
//                 ->has('data')
//         );
// });

// test('Login Test', function () {
//     $response = $this->postJson(
//         '/api/v1/login',
//         [
//             "email" => "asep@gmail.com",
//             "password" => "test",
//         ]
//     );

//     $response
//         ->assertStatus(200)
//         ->assertJson(
//             fn(AssertableJson $json) =>
//             $json->where('status', true)
//                 ->where('message', 'Login berhasil')
//                 ->has('token')
//                 ->has('data')
//         );
// });

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
