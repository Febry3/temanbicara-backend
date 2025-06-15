<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journal;
use App\Models\Tracking;
use App\Http\Controllers\ReportController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Mockery;

uses(RefreshDatabase::class);

describe('Tracking API', function () {

    describe('doTracking', function () {
        test('successfully creates a tracking record for the first time today', function () {
            $user = User::create(["email" => "tracking@example.com", "password" => "secret"]);
            Sanctum::actingAs($user);

            $this->mock(ReportController::class, function (Mockery\MockInterface $mock) use ($user) {
                $mock->shouldReceive('doReport')->once()->with($user->id)->andReturn(null);
            });

            $trackingData = [
                'bed_time' => '5-6 hours',
                'mood_level' => 'Neutral',
                'stress_level' => 3,
                'screen_time' => '3-4 hours',
                'activity' => '1k-3k steps',
            ];

            $this->postJson('/api/v1/do-tracking', $trackingData)
                ->assertStatus(200)
                ->assertJson(
                    fn(AssertableJson $json) =>
                    $json->where('status', true)
                        ->where('message', 'Data berhasil disimpan')
                        ->where('data.user_id', $user->id)
                        ->where('data.mood_level', 'Neutral')
                        ->etc()
                );

            $this->assertDatabaseHas('trackings', [
                'user_id' => $user->id,
                'stress_level' => 3,
            ]);
        });

        test('updates existing journal with new tracking_id', function () {
            $user = User::create(["email" => "tracking.journal@example.com", "password" => "secret"]);
            Sanctum::actingAs($user);

            $journal = Journal::create([
                'user_id' => $user->id,
                'title' => 'My Untracked Journal',
                'body' => '...',
                'tracking_id' => null,
            ]);

            $this->mock(ReportController::class, fn($mock) => $mock->shouldReceive('doReport')->andReturn(null));

            $trackingData = ['bed_time' => '5-6 hours', 'mood_level' => 'Neutral', 'stress_level' => 3, 'screen_time' => '3-4 hours', 'activity' => '1k-3k steps'];

            $this->postJson('/api/v1/do-tracking', $trackingData);

            $updatedJournal = Journal::find($journal->journal_id);
            expect($updatedJournal->tracking_id)->not->toBeNull();
        });

        test('fails if user already tracked today', function () {
            $user = User::create(["email" => "tracking.duplicate@example.com", "password" => "secret"]);
            Sanctum::actingAs($user);

            Tracking::create(['user_id' => $user->id, 'bed_time' => 'a', 'mood_level' => 'b', 'stress_level' => 1, 'screen_time' => 'c', 'activity' => 'd']);

            $trackingData = ['bed_time' => '5-6 hours', 'mood_level' => 'Neutral', 'stress_level' => 3, 'screen_time' => '3-4 hours', 'activity' => '1k-3k steps'];

            $this->postJson('/api/v1/do-tracking', $trackingData)
                ->assertStatus(500)
                ->assertJsonPath('message', 'Anda sudah melakukan tracking hari ini');
        });

        test('fails if validation fails (missing data)', function () {
            $user = User::create(["email" => "tracking.validation@example.com", "password" => "secret"]);
            Sanctum::actingAs($user);

            $trackingData = ['bed_time' => '5-6 hours', 'mood_level' => 'Neutral'];

            $this->postJson('/api/v1/do-tracking', $trackingData)
                ->assertStatus(500)
                ->assertJson(
                    fn(AssertableJson $json) =>
                    $json->where('message', 'Ada bagian yang tidak diisi')
                        ->has('error.stress_level')
                        ->etc()
                );
        });
    });

    describe('getAllTracking', function () {
        test('successfully gets all tracking records for a user', function () {
            $user = User::create(["email" => "getalltracking@example.com", "password" => "secret"]);
            Sanctum::actingAs($user);

            Tracking::create(['user_id' => $user->id, 'created_at' => now()->subDays(2), 'bed_time' => 'a', 'mood_level' => 'b', 'stress_level' => 1, 'screen_time' => 'c', 'activity' => 'd']);
            Tracking::create(['user_id' => $user->id, 'created_at' => now()->subDays(1), 'bed_time' => 'a', 'mood_level' => 'b', 'stress_level' => 1, 'screen_time' => 'c', 'activity' => 'd']);
            Tracking::create(['user_id' => $user->id, 'created_at' => now(), 'bed_time' => 'a', 'mood_level' => 'b', 'stress_level' => 1, 'screen_time' => 'c', 'activity' => 'd']);

            $this->getJson('/api/v1/tracking')
                ->assertStatus(200)
                ->assertJson(
                    fn(AssertableJson $json) =>
                    $json->where('status', true)
                        ->where('user_id', $user->id)
                        ->has('data', 3)
                        ->etc()
                );
        });

        test('returns "not found" if user has no tracking records', function () {
            $user = User::create(["email" => "getalltracking.empty@example.com", "password" => "secret"]);
            Sanctum::actingAs($user);

            $this->getJson('/api/v1/tracking')
                ->assertStatus(200)
                ->assertJsonPath('status', false)
                ->assertJsonPath('message', 'Tracking tidak ditemukan');
        });
    });
});