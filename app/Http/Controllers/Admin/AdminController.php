<?php

namespace App\Http\Controllers\Admin;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public static function loginAdmin(Request $request)
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
                return response()->json([
                    'status' => false,
                    'message' => 'Email dan password tidak boleh kosong',
                    'error' => $validateData->errors(),
                ], 200);
            };

            $user = User::where('email', $requestedData['email'])->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email tidak sesuai',
                ], 401);
            }

            if ($user->role != 'Admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Bukan admin',
                ], 401);
            }

            if (!Hash::check($requestedData['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password tidak sesuai',
                ], 401);
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Login berhasil',
                    'token' => $user->createToken('RevanGay', ['Admin'])->plainTextToken,
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
                500
            );
        }
    }

    public static function createCounselor(Request $request)
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
                [
                    'email' => 'required|unique:users,email|email',
                    'phone_number' => 'required|unique:users,phone_number',
                    'password' => 'required',
                    'name' => 'required',
                    'nickname' => 'required',
                    'gender' => 'required',
                    'birthdate' => 'required',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validateData->errors(),
                ], 200);
            };

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
                500
            );
        }
    }

    public static function getAllCounselor(Request $request)
    {
        try {
            if ($request->user()->role != 'Admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Bukan admin',
                ], 401);
            }

            $counselors = User::where('role', 'Counselor')->get();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Login berhasil',
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
                500
            );
        }
    }

    public static function createUser(Request $request)
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
                [
                    'email' => 'required|unique:users,email|email',
                    'phone_number' => 'required|unique:users,phone_number',
                    'password' => 'required',
                    'name' => 'required',
                    'nickname' => 'required',
                    'gender' => 'required',
                    'birthdate' => 'required',
                    'role' => 'required',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan pada validasi',
                    'error' => $validateData->errors(),
                ], 200);
            };

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
                500
            );
        }
    }

    public static function verifyPassword(Request $request)
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
                return response()->json([
                    'status' => false,
                    'message' => 'Email dan password tidak boleh kosong',
                    'error' => $validateData->errors(),
                ], 200);
            };

            $user = User::where('email', $requestedData['email'])->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email tidak sesuai',
                ], 200);
            }


            if (!Hash::check($requestedData['password'], $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password tidak sesuai',
                ], 401);
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Login berhasil',
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
                500
            );
        }
    }

    public static function createAdmin(Request $request)
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
                [
                    'email' => 'required|unique:users,email|email',
                    'phone_number' => 'required|unique:users,phone_number',
                    'password' => 'required',
                    'name' => 'required',
                    'nickname' => 'required',
                    'gender' => 'required',
                    'birthdate' => 'required',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email dan password tidak boleh kosong',
                    'error' => $validateData->errors(),
                ], 200);
            };

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
                500
            );
        }
    }
}
