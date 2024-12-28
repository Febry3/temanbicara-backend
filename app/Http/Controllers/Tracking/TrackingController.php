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

    public static function getAllTracking(Request $request)
    {
        try {
            $sleep_quality = ["Insomnia", "Kurang", "Cukup", "Baik", "Nyenyak"];
            $mood_level = ["Depresi", "Sedih", "Netral", "Senang", "Bahagia"];

            $trackings = Tracking::where('user_id', $request->user()->id)->get();
            $averageStressLevel = 0;
            $averageSleepQuality = 0;
            $averageMoodLevel = 0;
            foreach ($trackings as $tracking) {
                $averageStressLevel += $tracking->stress_level;

                if ($tracking->sleep_quality === "Nyenyak" || $tracking->mood_level === "Bahagia") {
                    $averageSleepQuality += 5;
                    $averageMoodLevel += 5;
                } else if ($tracking->sleep_quality === "Baik" || $tracking->mood_level === "Senang") {
                    $averageSleepQuality += 4;
                    $averageMoodLevel += 4;
                } else if ($tracking->sleep_quality === "Cukup" || $tracking->mood_level === "Netral") {
                    $averageSleepQuality += 3;
                    $averageMoodLevel += 3;
                } else if ($tracking->sleep_quality === "Kurang" || $tracking->mood_level === "Sedih") {
                    $averageSleepQuality += 2;
                    $averageMoodLevel += 2;
                } else if ($tracking->sleep_quality === "Insomnia" || $tracking->mood_level === "Depresi") {
                    $averageSleepQuality += 1;
                    $averageMoodLevel += 1;
                }
            }


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
                    'average_sleep_quality' =>  $sleep_quality[floor($averageSleepQuality / count($trackings)) - 1],
                    'average_mood' =>  $mood_level[floor($averageMoodLevel / count($trackings)) - 1],
                    'average_stress' => $averageStressLevel / count($trackings),
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
