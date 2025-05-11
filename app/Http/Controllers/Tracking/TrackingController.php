<?php

namespace App\Http\Controllers\Tracking;

use Carbon\Carbon;
use Throwable;
use App\Models\Journal;
use App\Models\Tracking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Report\ReportController;
use Illuminate\Support\Facades\Auth;

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
            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Ada bagian yang tidak diisi',
                    'error' => $validateData->errors(),
                ], 200);
            };

            $tracking = Tracking::where('user_id', $request->user()->id)
                ->whereDate('created_at', Carbon::today())
                ->latest()
                ->first();

            if ($tracking) {
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Anda sudah melakukan tracking hari ini',
                    ],
                    200
                );
            }

            $tracking = Tracking::create([
                'bed_time' => $requestedData['bed_time'],
                'mood_level' => $requestedData['mood_level'],
                'stress_level' => $requestedData['stress_level'],
                'screen_time' => $requestedData['screen_time'],
                'activity' => $requestedData['activity'],
                'user_id' => $request->user()->id
            ]);
            $today = now()->toDateString();
            Journal::where('user_id', $request->user()->id)
                ->whereNull('tracking_id')
                ->whereDate('created_at', $today)
                ->update([
                    'tracking_id' => $tracking->tracking_id,
                ]);

            $responseAi = null;
            if ($tracking) {
                $responseAi = app(ReportController::class)->doReport($request->user()->id);
            }


            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $tracking,
                    'response_ai' => $responseAi
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

            if ($trackings->isEmpty()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Tracking tidak ditemukan',
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

    public function getLastSevenDaysTracking(Request $request)
    {
        try {
            $lastSevenData = Auth::user()->lastSevenDaysTracking;

            if ($lastSevenData->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'No tracking data available',
                    'data' => null
                ], 200);
            }

            $sum = [
                "mood" => 0,
                "stress_level" => 0,
                "bed_time" => 0,
                "screen_time" => 0,
                "activity" => 0,
            ];

            foreach ($lastSevenData as $data) {
                $sum['mood'] += match (strtolower(implode('', explode(' ', $data['mood_level'])))) {
                    "depressed" => 1,
                    "sad" => 2,
                    "neutral" => 3,
                    "happy" => 4,
                    "cheerful" => 5,
                };

                $sum['stress_level'] += $data['stress_level'];

                $sum['bed_time'] += match (strtolower(implode('', explode(' ', $data['bed_time'])))) {
                    ">8hours" => 9,
                    "7-8hours" => 8,
                    "6hours" => 6,
                    "4-5hours" => 5,
                    "<4hours" => 3,
                };

                $sum['screen_time'] += match (strtolower(implode('', explode(' ', $data['screen_time'])))) {
                    "<1hours" => 1,
                    "1-3hours" => 3,
                    "3-5hours" => 5,
                    "5-8hours" => 8,
                    ">8hours" => 9,
                };

                $sum['activity'] += match (strtolower(implode('', explode(' ', $data['activity'])))) {
                    "<2steps" => 1,
                    "2k-5ksteps" => 5,
                    "5k-7.5ksteps" => 7.5,
                    "7.5k-10ksteps" => 10,
                    ">10ksteps" => 11,
                };
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => [
                        'tracking_data' => $lastSevenData,
                        'average_mood' => match ((int)round($sum['mood'] / count($lastSevenData))) {
                            1 => 'Depressed',
                            2 => 'Sad',
                            3 => 'Neutral',
                            4 => 'Happy',
                            5 => 'Cheerful'
                        },
                        'average_stress_level' => round($sum['stress_level'] / count($lastSevenData)),
                        'average_bed_time' => round($sum['bed_time'] / count($lastSevenData)) . " hours",
                        'average_screen_time' => round($sum['screen_time'] / count($lastSevenData)) . " hours",
                        'average_activity' => round($sum['activity'] / count($lastSevenData)) . "k steps",
                    ]
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
