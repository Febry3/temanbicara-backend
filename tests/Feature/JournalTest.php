<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journal;
use App\Models\TrackJournalResponse;
use App\Models\Observation;
use App\Http\Controllers\AiController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Mockery;

uses(RefreshDatabase::class);

describe('Journal API', function () {

    $getMockAiPayload = function ($trackingId = 'dummy-tracking-id-from-ai') {
        return [
            'tracking_id' => $trackingId,
            'result' => [
                'assessment' => 'Test assessment.',
                'recommendations' => [],
                'observations' => [],
            ],
        ];
    };

    test('Successfully create a journal', function () use ($getMockAiPayload) {
        $user = User::create([
            "email" => "journaltest@gmail.com",
            "password" => bcrypt("secret123"),
        ]);

        Sanctum::actingAs($user);

        $mockAiResult = $getMockAiPayload('ai-123');
        $this->mock(AiController::class, function (Mockery\MockInterface $mock) use ($user, $mockAiResult) {
            $mock->shouldReceive('generate')
                ->once()
                ->with($user->id)
                ->andReturn(new JsonResponse($mockAiResult, 200));
        });

        $journalData = [
            'title' => 'My First Manual Journal',
            'body' => 'This is created without factory.',
        ];

        $response = $this->postJson('/api/v1/journal', $journalData);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', true)
                    ->where('message', 'Data berhasil disimpan')
                    ->has('data')
                    ->has('response_ai')

            );
    });

    test('Successfully delete a journal', function () {
        $user = User::create([
            "email" => "journaltest2@gmail.com",
            "password" => bcrypt("secret123"),
        ]);

        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Journal to be deleted',
            'body' => 'Delete me.',
            'image_url' => 'Empty',
        ]);


        Http::fake(['*' => Http::response(null, 200)]);


        $response = $this
            ->actingAs($user)
            ->deleteJson('/api/v1/journal/' . $journal->journal_id);

        $response
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', true)
                    ->where('message', 'Data berhasil dihapus')
            );

        $this->assertDatabaseMissing('journals', ['journal_id' => $journal->journal_id]);
    });



    test('Successfully get a journal by its ID', function () {

        $user = User::create([
            "email" => "getbyid@example.com",
            "password" => bcrypt("secret123"),
        ]);
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'This is the one',
            'body' => 'We need to find this specific journal.',
        ]);


        $this->actingAs($user)
            ->getJson('/api/v1/journal/' . $journal->journal_id)
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', true)
                    ->where('message', 'Data berhasil diambil')
                    ->where('data.journal_id', $journal->journal_id)
                    ->where('data.title', 'This is the one')
                    ->etc()
            );
    });

    test('Fails to get journal if ID does not exist', function () {

        $user = User::create([
            "email" => "getbyid.fail@example.com",
            "password" => bcrypt("secret123"),
        ]);


        $this->actingAs($user)
            ->getJson('/api/v1/journal/non-existent-id')
            ->assertStatus(404)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', false)
                    ->where('message', 'Jurnal tidak ditemukan')
            );
    });

    test('Fails to get journal belonging to another user', function () {
        $ownerUser = User::create(["email" => "owner@example.com", "password" => "secret"]);
        $journalOwned = Journal::create([
            'user_id' => $ownerUser->id,
            'title' => 'Secret Journal',
            'body' => 'This is not for everyone.',
        ]);

        $attackerUser = User::create(["email" => "attacker@example.com", "password" => "secret"]);

        $this->actingAs($attackerUser)
            ->getJson('/api/v1/journal/' . $journalOwned->journal_id)
            ->assertStatus(404)
            ->assertJsonPath('status', false);
    });


    test('Successfully get all journals for a specific date', function () {
        $user = User::create(["email" => "getall@example.com", "password" => "secret"]);
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        Journal::create(['user_id' => $user->id, 'title' => 'Journal Today 1', 'body' => '...']);
        Journal::create(['user_id' => $user->id, 'title' => 'Journal Today 2', 'body' => '...', 'created_at' => now()->addMinute()]);

        Journal::create(['user_id' => $user->id, 'title' => 'Journal Yesterday', 'body' => '...', 'created_at' => now()]);

        $this->actingAs($user)
            ->postJson('/api/v1/journal/get', ['date_request' => $today])
            ->assertStatus(200)
            ->assertJson(
                fn(AssertableJson $json) =>
                $json->where('status', true)
                    ->where('id', $user->id)
                    ->has('data', 2)
                    ->where('data.0.title', 'Journal Today 1')
                    ->etc()
            );
    });

    // test('Returns "not found" message if no journals on specific date', function () {
    //     $user = User::create(["email" => "getall.empty@example.com", "password" => "secret"]);
    //     $today = now()->toDateString();
    //     $yesterday = now()->subDay()->toDateString();

    //     Journal::create(['user_id' => $user->id, 'title' => 'Journal Yesterday', 'body' => '...', 'created_at' => $yesterday]);

    //     $this->actingAs($user)
    //         ->postJson('/api/v1/journal/get', ['date_request' => $today])
    //         ->assertStatus(200)
    //         ->assertJson(
    //             fn(AssertableJson $json) =>
    //             $json->where('status', false)
    //                 ->where('message', 'Jurnal tidak ditemukan')
    //         );
    // });

});
