<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tracking;
use App\Models\TrackJournalResponse;
use App\Models\Observation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

describe('Report API', function () {

    function setupReportDataForTest($user)
    {
        $tracking = Tracking::create([
            'user_id' => $user->id,
            'bed_time' => 'N/A',
            'mood_level' => 'N/A',
            'stress_level' => 0,
            'screen_time' => 'N/A',
            'activity' => 'N/A',
        ]);

        $response = TrackJournalResponse::create([
            'tracking_id' => $tracking->tracking_id,
            'assesment' => 'Test Assessment',
            'metrix' => 10,
            'short_term' => '[]',
            'long_term' => '[]',
            'closing' => 'Test closing',
        ]);

        $observation = Observation::create([
            'response_id' => $response->response_id,
            'mood' => 'happy',
            'sleep' => '5-6hours',
            'stress' => 2,
            'screen_time' => '1-2hours',
            'activity' => '3k-5ksteps',
        ]);

        return $observation;
    }

    test('successfully gets a report for a specific date', function () {
        $user = User::create(["email" => "report@example.com", "password" => "secret"]);
        Sanctum::actingAs($user);

        $this->travelTo(now());
        $todayReport = setupReportDataForTest($user);
        $this->travelBack();

        $this->postJson('/api/v1/report', ['date_request' => now()->toDateString()])
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', true)
                    ->has('data', 1)
                    ->where('data.0.observation_id', $todayReport->observation_id)
                    ->etc()
            );
    });

    test('returns "not found" when no report on specific date', function () {
        $user = User::create(["email" => "report.empty@example.com", "password" => "secret"]);
        Sanctum::actingAs($user);

        $this->travelTo(now()->subDay());
        setupReportDataForTest($user);
        $this->travelBack();

        $this->postJson('/api/v1/report', ['date_request' => now()->toDateString()])
            ->assertStatus(200)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'Data report tidak tersedia atau format tanggal salah');
    });

    test('fails to get report belonging to another user', function () {
        $userA = User::create(["email" => "userA@example.com", "password" => "secret"]);
        $userB = User::create(["email" => "userB@example.com", "password" => "secret"]);
        setupReportDataForTest($userA);

        Sanctum::actingAs($userB);
        $this->postJson('/api/v1/report', ['date_request' => now()->toDateString()])
            ->assertStatus(200)
            ->assertJsonPath('status', false);
    });

    test('handles missing date_request by returning not found response', function () {
        $user = User::create(["email" => "report.validation@example.com", "password" => "secret"]);
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/report')
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', false)
                    ->where('message', 'Data report tidak tersedia atau format tanggal salah')
                    ->etc()
            );
    });
});