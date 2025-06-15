<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function() {
    $this->counselor = User::create([
        'name' => 'Dr. Budi',
        'email' => 'budi.counselor@example.com',
        'password' => bcrypt('secret'),
        'role' => 'Counselor'
    ]);
});

test('successfully gets a paginated list of published articles', function () {
    Article::create(['user_id' => $this->counselor->id, 'title' => 'Published Article 1', 'content' => '...', 'image_url' => 'url', 'status' => 'Published']);
    Article::create(['user_id' => $this->counselor->id, 'title' => 'Published Article 2', 'content' => '...', 'image_url' => 'url', 'status' => 'Published']);
    Article::create(['user_id' => $this->counselor->id, 'title' => 'Pending Article', 'content' => '...', 'image_url' => 'url', 'status' => 'Pending']);

    Sanctum::actingAs($this->counselor);

    $this->getJson('/api/v1/article')
        ->assertStatus(200)
        ->assertJson(
            fn (AssertableJson $json) =>
            $json->where('status', true)
                 ->has('data.data', 2)
                 ->etc()
        );
});

describe('POST /api/v1/article (createArticle)', function () {
    test('a counselor can successfully create an article', function () {
        Sanctum::actingAs($this->counselor);
        Http::fake(['*' => Http::response(['Key' => 'article/test-image.jpg'], 200)]);

        $this->postJson('/api/v1/article', [
            'title' => 'New Awesome Article',
            'content' => 'This is the content.',
            'image' => UploadedFile::fake()->image('test-image.jpg'),
        ])->assertStatus(201);

        $this->assertDatabaseHas('articles', [
            'title' => 'New Awesome Article',
            'status' => 'Pending'
        ]);
    });

    test('fails with server error if validation fails', function () {
        Sanctum::actingAs($this->counselor);
        $this->postJson('/api/v1/article', ['content' => 'No title'])
             ->assertStatus(500);
    });
});


describe('GET /api/v1/article/{id} (getArticleById)', function () {
    test('successfully gets a single published article by its ID', function () {
        $article = Article::create(['user_id' => $this->counselor->id, 'title' => 'Specific Article', 'content' => '...', 'image_url' => 'url', 'status' => 'Published']);

        Sanctum::actingAs($this->counselor);
        $this->getJson('/api/v1/article/' . $article->article_id)
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) => 
                $json->where('status', true)
                     ->where('data.article_id', $article->article_id)
                     ->etc()
            );
    });

    test('returns 404 if article ID is not found', function () {
        Sanctum::actingAs($this->counselor);
        $this->getJson('/api/v1/article/999')
            ->assertStatus(404);
    });
});