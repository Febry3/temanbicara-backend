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
                $sum['mood'] += match ($data['mood_level']) {
                    "Depressed" => 1,
                    "Sad" => 2,
                    "Neutral" => 3,
                    "Happy" => 4,
                    "Cheerful" => 5,
                };

                $sum['stress_level'] += $data['stress_level'];

                $sum['bed_time'] += match ($data['bed_time']) {
                    "> 8 hours" => 9,
                    "7-8 hours" => 8,
                    "6 hours" => 6,
                    "4-5 hours" => 5,
                    "< 4 hours" => 3,
                };

                $sum['screen_time'] += match ($data['screen_time']) {
                    "< 1 hours" => 1,
                    "1-3 hours" => 3,
                    "3-5 hours" => 5,
                    "5-8 hours" => 8,
                    "> 8 hours" => 9,
                };

                $sum['activity'] += match ($data['activity']) {
                    "< 2 steps" => 1,
                    "2k-5k steps" => 5,
                    "5k-7.5k steps" => 7.5,
                    "7.5k-10k steps" => 10,
                    "> 10k steps" => 11,
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
