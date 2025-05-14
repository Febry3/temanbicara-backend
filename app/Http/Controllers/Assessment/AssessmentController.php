<?php

namespace App\Http\Controllers\Assessment;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class AssessmentController extends Controller
{
    public static function doAssessment(Request $request)
    {
        try {
            $requestedData = $request->only([
                'name',
                'nickname',
                'gender',
                'birthdate',
                'phone_number'
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'name' => 'required',
                    'nickname' => 'required',
                    'gender' => 'required',
                    'birthdate' => 'required',
                    'phone_number' => 'required|unique:users,phone_number',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ada bagian yang tidak diisi',
                    'error' => $validateData->errors(),
                ], 200);
            }

           User::where('id', $request->user()->id)->update([
                'name' => $requestedData['name'],
                'nickname' => $requestedData['nickname'],
                'gender' => $requestedData['gender'],
                'birthdate' => $requestedData['birthdate'],
                'phone_number' => $requestedData['phone_number'],
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Assessment berhasil dibuat',
                    'data' => $request->user(),
                ],
                200
            );
        } catch (\Throwable $err) {
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
