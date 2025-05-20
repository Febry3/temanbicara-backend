<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Exceptions;


class AdminController extends Controller
{
    private $statusCode;
    private $rules = [
        'email' => 'required|unique:users,email|email',
        'phone_number' => 'required|unique:users,phone_number',
        'password' => 'required',
        'name' => 'required',
        'nickname' => 'required',
        'gender' => 'required',
        'birthdate' => 'required',
    ];

    public function loginAdmin(Request $request)
    {
        try {
            $this->statusCode = 200;

            $requestedData = $request->only([
                'email',
                'password'
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'email' => 'required',
                    'password' => 'required',
                ]
            );

            if ($validateData->fails()) {
                $this->statusCode = 422;
                throw new Exceptions($validateData->errors());
            }

            $user = User::where('email', $requestedData['email'])->first();
            if (!$user) {
                $this->statusCode = 401;
                throw new Exceptions('Email tidak sesuai');
            }

            if ($user->role != 'Admin') {
                $this->statusCode = 401;
                throw new Exceptions('Bukan admin');
            }

            if (!Hash::check($requestedData['password'], $user->password)) {
                $this->statusCode = 401;
                throw new Exceptions('Password tidak sesuai');
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Login berhasil',
                    'token' => $user->createToken('0xFFFFFF', ['Admin'])->plainTextToken,
                    'data' => $user,
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                $this->statusCode->isEmpty() ? 500 : $this->statusCode
            );
        }
    }

    public function createCounselor(Request $request)
    {
        try {
            $this->statusCode = 200;

            $requestedData = $request->only([
                'email',
                'password',
                'name',
                'nickname',
                'gender',
                'birthdate',
                'phone_number',
            ]);

            $validateData = Validator::make(
                $requestedData,
                $this->rules
            );

            if ($validateData->fails()) {
                $this->statusCode = 422;
                throw new Exceptions($validateData->errors());
            }

            $user = User::create([
                'email' => $requestedData['email'],
                'phone_number' => $requestedData['phone_number'],
                'password' => $requestedData['password'],
                'name' => $requestedData['name'],
                'nickname' => $requestedData['nickname'],
                'gender' => $requestedData['gender'],
                'birthdate' => $requestedData['birthdate'],
                'role' => 'Counselor',
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Akun konselor berhasil dibuat',
                    'data' => $user,
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                $this->statusCode
            );
        }
    }

    public function getAllCounselor(Request $request)
    {
        try {
            if ($request->user()->role != 'Admin') {
                $this->statusCode = 401;
                throw new Exceptions('Bukan admin');
            }

            $counselors = User::where('role', 'Counselor')->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Sukses mendapatkan data',
                    'data' => $counselors,
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                $this->statusCode
            );
        }
    }

    public function createUser(Request $request)
    {
        try {
            $requestedData = $request->only([
                'email',
                'phone_number',
                'password',
                'name',
                'nickname',
                'gender',
                'birthdate',
                'role',
            ]);

            $validateData = Validator::make(
                $requestedData,
                $this->rules
            );

            if ($validateData->fails()) {
                $this->statusCode = 422;
                throw new Exceptions($validateData->errors());
            }

            $user = User::create([
                'email' => $requestedData['email'],
                'phone_number' => $requestedData['phone_number'],
                'password' => $requestedData['password'],
                'name' => $requestedData['name'],
                'nickname' => $requestedData['nickname'],
                'gender' => $requestedData['gender'],
                'birthdate' => $requestedData['birthdate'],
                'role' => $requestedData['role'],
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Akun berhasil dibuat',
                    'data' => $user,
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                $this->statusCode
            );
        }
    }

    public function verifyPassword(Request $request)
    {
        try {
            $requestedData = $request->only([
                'email',
                'password'
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'email' => 'required',
                    'password' => 'required',
                ]
            );

            if ($validateData->fails()) {
                $this->statusCode = 422;
                throw new Exceptions($validateData->errors());
            }

            $user = User::where('email', $requestedData['email'])->first();
            if (!$user) {
                $this->statusCode = 422;
                throw new Exceptions('Email tidak sesuai');
            }


            if (!Hash::check($requestedData['password'], $user->password)) {
                $this->statusCode = 422;
                throw new Exceptions('Password tidak sesuai');
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Verifikasi berhasil',
                    'data' => $user,
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                $this->statusCode
            );
        }
    }

    public function createAdmin(Request $request)
    {
        try {
            $requestedData = $request->only([
                'email',
                'password',
                'name',
                'nickname',
                'gender',
                'birthdate',
                'phone_number',
            ]);

            $validateData = Validator::make(
                $requestedData,
                $this->rules
            );

            if ($validateData->fails()) {
                $this->statusCode = 422;
                throw new Exceptions($validateData->errors());
            }

            $user = User::create([
                'email' => $requestedData['email'],
                'phone_number' => $requestedData['phone_number'],
                'password' => $requestedData['password'],
                'name' => $requestedData['name'],
                'nickname' => $requestedData['nickname'],
                'gender' => $requestedData['gender'],
                'birthdate' => $requestedData['birthdate'],
                'role' => 'Admin',
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Akun admin berhasil dibuat',
                    'data' => $user,
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ],
                $this->statusCode
            );
        }
    }
}
