<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\User;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Helper\ImageRequestHelper;
use App\Http\Requests\CreateUserRequest;

class AdminController extends Controller
{
    public function createUser(CreateUserRequest $request)
    {
        try {
            $role = $request->input("role");
            $imageUrl = "";

            $request->validated();

            if ($request->hasFile('image')) {
                $response = ImageRequestHelper::postImageToSupabase($request, 'profile');
                $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];

                if ($response->failed()) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Kesalahan pada mengupload gambar',
                        ],
                        404
                    );
                }
            }


            $user = User::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => $request->password,
                'role' => trim(Str::title($role), '"'),
                'name' => $request->name,
                'nickname' => $request->nickname,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'profile_url' => $imageUrl
            ]);
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $user
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
