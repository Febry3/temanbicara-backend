<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Expertise;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Expertise API', function () {

    describe('GET /api/v1/Expertise', function () {

        test('successfully gets all expertise from counselors only', function () {
            $counselor1 = User::create(['name' => 'c1', 'email' => 'counselor1@example.com', 'password' => 'secret', 'role' => 'Counselor']);
            $counselor2 = User::create(['name' => 'c2', 'email' => 'counselor2@example.com', 'password' => 'secret', 'role' => 'Counselor']);
            $regularUser = User::create(['name' => 'u1', 'email' => 'user@example.com', 'password' => 'secret', 'role' => 'General']);

            Expertise::create(['user_id' => $counselor1->id, 'type' => 'Kecemasan']);
            Expertise::create(['user_id' => $counselor2->id, 'type' => 'Keluarga']);
            Expertise::create(['user_id' => $regularUser->id, 'type' => 'Karir']);
            Sanctum::actingAs($counselor1);
            $this->getJson('/api/v1/Expertise')
                ->assertStatus(200)
                ->assertJson(
                    fn(AssertableJson $json) =>
                    $json->where('status', true)
                        ->where('message', 'Data keahlian berhasil diambil')
                        ->has('data', 2)
                        ->etc()
                );
        });

        test('returns an empty data array if no counselors have expertise', function () {
            $regularUser = User::create(['name' => 'u1', 'email' => 'user@example.com', 'password' => 'secret', 'role' => 'General']);
            Expertise::create(['user_id' => $regularUser->id, 'type' => 'Karir']);
            Sanctum::actingAs($regularUser);
            $this->getJson('/api/v1/Expertise')
                ->assertStatus(200)
                ->assertJson(
                    fn(AssertableJson $json) =>
                    $json->where('status', true)
                        ->has('data', 0)
                        ->etc()
                );
        });
    });

    describe('POST /api/v1/Expertise', function () {

        test('an authenticated user can add an expertise', function () {
            $user = User::create(['name' => 'u_add', 'email' => 'user.add@example.com', 'password' => 'secret', 'role' => 'Counselor']);
            Sanctum::actingAs($user);

            $this->postJson('/api/v1/Expertise', ['type' => 'Depresi'])
                ->assertStatus(200)
                ->assertJson(fn(AssertableJson $json) => $json->where('status', true)->etc());

            $this->assertDatabaseHas('expertises', ['user_id' => $user->id, 'type' => 'Depresi']);
        });
    });

});