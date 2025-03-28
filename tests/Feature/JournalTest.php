<?php

use Illuminate\Testing\Fluent\AssertableJson;

test('Create Journal Test', function () {
    $token = "Sm0Oh8nKqtMEns6dFZDopvSNsyn3JUBe1rOt1Mxya30bee25";

    $response = $this
        ->withHeaders([
            'Authorization' => "Bearer $token"
        ])
        ->postJson(
            '/api/v1/journal',
            [
                "title" => fake()->text(),
                "body" => fake()->text(),
                "stress_level" => fake()->randomDigit(5),
                "mood_level" => "good"
            ]
        );

    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', true)
                ->where('message', 'Data berhasil disimpan')
                ->has('data')
        );
});

test('Delete Journal Test', function () {
    $token = "Sm0Oh8nKqtMEns6dFZDopvSNsyn3JUBe1rOt1Mxya30bee25";

    $response = $this
        ->withHeaders([
            'Authorization' => "Bearer $token"
        ])
        ->deleteJson(
            '/api/v1/journal/1',
            [
                "title" => fake()->text(),
                "body" => fake()->text(),
                "stress_level" => fake()->randomDigit(5),
                "mood_level" => "good"
            ]
        );

    dump($response);
    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json
                ->where('status', true)
                ->where('message', 'Data berhasil dihapus')
        );
});
