<?php

namespace App\Http\Controllers\Tracking;

use Throwable;
use App\Models\Journal;
use App\Models\Tracking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TrackingController extends Controller
{
    public static function doTracking(Request $request)
    {
        try {
            $requestedData = $request->only([
                'bed_time',
                'mood_level',
                'stress_level',
                'screen_time',
                'activity'
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'bed_time' => 'required',
                    'mood_level' => 'required',
                    'stress_level' => 'required',
                    'screen_time' => 'required',
                    'activity' => 'required',
                ]
            );
            // dd($request->all());
            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ada bagian yang tidak diisi',
                    'error' => $validateData->errors(),
                ], 200);
            };

            $tracking = Tracking::create([
                'bed_time' => $requestedData['bed_time'],
                'mood_level' => $requestedData['mood_level'],
                'stress_level' => $requestedData['stress_level'],
                'screen_time' => $requestedData['screen_time'],
                'activity' => $requestedData['activity'],
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

    public static function getAllTracking(Request $request)
    {
        try {

            $mood_level = ["Depresi", "Sedih", "Netral", "Senang", "Bahagia"];

            $trackings = Tracking::where('user_id', $request->user()->id)->get();



            if (!$trackings) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Jurnal tidak ditemukan',
                    ],
                    200
                );
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'user_id' => $request->user()->id,
                    'data' => $trackings
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
