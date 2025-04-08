<?php

namespace App\Http\Controllers\Auth;


use Throwable;
use App\Models\User;
use App\Models\OTPRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Helper\ImageRequestHelper;
use App\Mail\PasswordResetEmail;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public static function register(Request $request)
    {
        try {
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
            };

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
                'new_password',
                'confirm_password',
                'otp'
            ]);

            Validator::validate(
                $requestedData,
                [
                    'new_password' => 'required',
                    'confirm_password' => 'required',
                    'otp' => 'required'
                ]
            );

            if ($requestedData['new_password'] != $requestedData['confirm_password']) {
                return response()->json([
                    'status' => false,
                    'message' => 'New password and confirm password cant be difference',
                ], 400);
            }

            $otp = OTPRequest::where('user_id', Auth::user()->id)->first();

            if ($otp->otp != $requestedData['otp']) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP',
                ], 400);
            }

            if (Carbon::parse($otp->expired_at)->lessThan(Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s'))) {
                return response()->json([
                    'status' => false,
                    'message' => 'Expired OTP',
                ], 410);
            }

            User::where('id', $request->user()->id)->update([
                'password' => Hash::make($requestedData['new_password'])
            ]);

            $otp->delete();

            return response()->json([
                'status' => true,
                'message' => 'Password changed successfully',
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
            };

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

    public static function editProfileData(Request $request)
    {
        try {
            $requestedData = $request->only([
                'name',
                'email',
                'birthdate',
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'name' => 'required|string|max:255',
                    'email' => 'required|string|max:255|',
                    'birthdate' => 'required|date',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validateData->errors()
                ], 400);
            }

            User::where('id', Auth::user()->id)->update([
                'name' => $requestedData['name'],
                'email' => $requestedData['email'],
                'birthdate' => $requestedData['birthdate']
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Profile data updated',
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

    public static function editProfileImage(Request $request)
    {
        try {
            $response = ImageRequestHelper::postImageToSupabase($request, 'profile');

            User::where('id', Auth::user()->id)->update([
                'profile_url' => config('supabase.url') . '/' . $response->json()['Key']
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Profile image updated successfully',
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

    public static function sendResetPasswordOTP(Request $request)
    {
        try {
            $user = Auth::user();

            $otp = rand(100000, 999999);

            Mail::to($user->email)->send(new PasswordResetEmail(explode('@', $user->email)[0], $otp));

            $otpRequest = OTPRequest::where('user_id', $user->id)->first();

            $expired_at = Carbon::now(new CarbonTimeZone('Asia/Bangkok'))->addMinutes(5)->format('Y-m-d H:i:s');

            if (!$otpRequest) {
                $otpRequest = OTPRequest::create([
                    'user_id' => $user->id,
                    'otp' => $otp,
                    'expired_at' => $expired_at
                ]);
            } else {
                $otpRequest->otp = $otp;
                $otpRequest->expired_at = $expired_at;
                $otpRequest->save();
            }


            return response()->json([
                'status' => true,
                'message' => 'OTP email sended',
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
