<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\OTPRequest;
use Carbon\CarbonTimeZone;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;
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

test('Logout Test', function () {
    Sanctum::actingAs($this->user);
    $response = $this
        ->postJson(
            '/api/v1/logout',
        );


    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', true)
                ->where('message', 'Logged Out')
        );

    expect(DB::select('SELECT * FROM personal_access_tokens WHERE tokenable_id = ?', [$this->user->id]))->toBeEmpty();
});

test('Change Password Test', function () {
    Sanctum::actingAs($this->user);

    $otpRequest = OTPRequest::create([
        'user_id' => $this->user->id,
        'otp' => 123456,
        'expired_at' => Carbon::now(new CarbonTimeZone('Asia/Bangkok'))->addMinutes(5)->format('Y-m-d H:i:s')
    ]);

    $response = $this
        ->postJson(
            '/api/v1/profile/password',
            [
                'new_password' => 'alpha',
                'confirm_password' => 'alpha',
                'otp' => $otpRequest->otp
            ]
        );

    $response
        ->assertStatus(200)
        ->assertJson(
            fn(AssertableJson $json) =>
            $json->where('status', true)
                ->where('message', 'Password changed successfully')
        );

    expect(DB::select('SELECT * FROM otp_requests WHERE user_id = ?', [$this->user->id]))->toBeEmpty();
});
