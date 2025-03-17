<?php

namespace App\Http\Controllers\Auth;

use Throwable;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public static function register(Request $request)
    {
        try {
            // dd($request);
            $requestedData = $request->only([
                'email',
                'phone_number',
                'password'
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'email' => 'required|unique:users,email|email',
                    'phone_number' => 'required|unique:users,phone_number',
                    'password' => 'required',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Terjadi kesalahan pada validasi',
                    'error' => $validateData->errors(),
                ], 200);
            }
            ;

            $user = User::create([
                'email' => $requestedData['email'],
                'phone_number' => $requestedData['phone_number'],
                'password' => $requestedData['password'],

            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Akun berhasil dibuat',
                    'token' => $user->createToken('RevanGay', [$user->role])->plainTextToken,
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

    public static function login(Request $request)
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
            }
            ;

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
                    'token' => $user->createToken('RevanGay', [$user->role])->plainTextToken,
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

    public static function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(
            [
                'status' => true,
                'message' => 'Logged Out',
            ],
            200
        );
    }

    public static function changePassword(Request $request)
    {
        try {
            $requestedData = $request->only([
                'old_password',
                'new_password',
                'confirm_password',
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'old_password' => 'required',
                    'new_password' => 'required',
                    'confirm_password' => 'required',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password Baru/Password Lama tidak boleh kosong',
                    'error' => $validateData->errors(),
                ], 200);
            }
            ;

            if ($requestedData['confirm_password'] !== $requestedData['new_password']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password baru dan confirm password tidak sama',
                ], 200);
            }

            if ($requestedData['old_password'] === $requestedData['new_password']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password baru dan lama tidak boleh sama',
                ], 200);
            }

            if (!Hash::check($requestedData['old_password'], $request->user()->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password lama tidak sesuai',
                ], 200);
            }

            User::where('id', $request->user()->id)->update([
                'password' => Hash::make($requestedData['new_password'])
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diperbaharui',
            ], 200);
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

    public static function forgetPassword(Request $request)
    {
        try {
            $requestedData = $request->only([
                'email',
                'phone_number',
                'new_password',
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'email' => 'required',
                    'phone_number' => 'required',
                    'new_password' => 'required'
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email/nomor telepon/ password tidak boleh kosong',
                    'error' => $validateData->errors(),
                ], 200);
            }
            ;

            $user = User::where('email', $requestedData['email'], 'AND')->where('phone_number', $requestedData['phone_number'])->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Tidak ada email dan nomor telepon yang sesuai',
                ], 200);
            }

            $user->password = Hash::make($requestedData['new_password']);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'Password berhasil diperbaharui',
            ], 200);
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

    public static function verifySanctumToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Token salah'
                    ],
                    401
                );
            }

            if ($user->role != 'Admin') {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'User bukan admin'
                    ],
                    401
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Token benar',
                    'user' => $user,
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
    public static function editProfile(Request $request)
    {

        try {

            $requestedData = $request->only([
                'name',
                'email',
                'image',
                'birthdate',
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:users,email,' . $request->user()->id,
                    'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
                    'birthdate' => 'required|date',
                ]
            );

            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('journal', 'public');

                $imageUrl = asset('storage/' . $imagePath);
            }

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validateData->errors()
                ], 422);
            }

            User::where('id', $request->user()->id)->update([
                'name' => $requestedData['name'],
                'email' => $requestedData['email'],
                'image' => $imageUrl,
                'birthdate' => $requestedData['birthdate']
            ]);

            $user = User::find($request->user()->id);

            return response()->json([
                'status' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $user,
            ], 200);
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
