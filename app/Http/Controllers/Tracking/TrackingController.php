<?php

namespace App\Http\Controllers\Tracking;

use App\Http\Controllers\Controller;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    public static function doTracking(Request $request)
    {
        try {
            $requestedData = $request->only([
                'sleep_quality',
                'mood_level',
                'stress_level',
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'sleep_quality' => 'required',
                    'mood_level' => 'required',
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

            $tracking = Tracking::create([
                'sleep_quality' => $requestedData['sleep_quality'],
                'mood_level' => $requestedData['mood_level'],
                'stress_level' => $requestedData['stress_level'],
                'user_id' => $request->user()->id
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $tracking
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
