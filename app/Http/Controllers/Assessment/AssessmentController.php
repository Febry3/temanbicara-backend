<?php

namespace App\Http\Controllers\Assessment;

use App\Models\User;
use App\Models\Assessment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class AssessmentController extends Controller
{
    public static function doAssessment(Request $request)
    {
        $requestedData = $request->only([
            'name',
            'nickname',
            'gender',
            'birthdate',
            'mbti',
            'topics',
            'goal',
            'sleep_quality',
            'have_consulted',
            'consumed_medicine',
            'stress_level',
        ]);

        $validateData = Validator::make(
            $requestedData,
            [
                'name' => 'required',
                'nickname' => 'required',
                'gender' => 'required',
                'birthdate' => 'required',
                'mbti' => 'required',
                'topics' => 'required',
                'goal' => 'required',
                'sleep_quality' => 'required',
                'have_consulted' => 'required',
                'consumed_medicine' => 'required',
                'stress_level' => 'required',
            ]
        );

        if ($validateData->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Ada bagian yang tidak diisi',
                'error' => $validateData->errors(),
            ], 200);
        };

        $user = User::where('id', $request->user()->id)->update([
            'name' => $requestedData['name'],
            'nickname' => $requestedData['nickname'],
            'gender' => $requestedData['gender'],
            'birthdate' => $requestedData['birthdate'],
        ]);

        Assessment::create([
            'mbti' => $requestedData['mbti'],
            'topics' => $requestedData['topics'],
            'goal' => $requestedData['goal'],
            'sleep_quality' => $requestedData['sleep_quality'],
            'have_consulted' => $requestedData['have_consulted'],
            'consumed_medicine' => $requestedData['consumed_medicine'],
            'stress_level' => $requestedData['stress_level'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json(
            [
                'status' => true,
                'message' => 'Akun berhasil dibuat',
                'data' => $request->user(),
            ],
            200
        );
    }
}
