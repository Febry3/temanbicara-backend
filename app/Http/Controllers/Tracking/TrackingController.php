<?php

namespace App\Http\Controllers\Tracking;

use Throwable;
use Carbon\Carbon;
use App\Models\Journal;
use App\Models\Tracking;
use UnhandledMatchError;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Report\ReportController;

class TrackingController extends Controller
{
    public static function doTracking(Request $request)
    {
        $responseData = [];
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
                $responseData = [
                    'status' => false,
                    'message' => 'Ada bagian yang tidak diisi',
                    'error' => $validateData->errors(),
                ];
                return response()->json($responseData, 500);
            }

            $tracking = Tracking::where('user_id', $request->user()->id)
                ->whereDate('created_at', Carbon::today())
                ->latest()
                ->first();

            if ($tracking) {
                $responseData =
                    [
                        'status' => true,
                        'message' => 'Anda sudah melakukan tracking hari ini',
                    ];
                return response()->json($responseData, 500);
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


            $responseData =
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $tracking,
                    'response_ai' => $responseAi
                ];
        } catch (\Throwable $err) {
            $responseData =
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ];
        }
        return response()->json($responseData);
    }

    public static function getAllTracking(Request $request)
    {
        try {

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

    public function getLastSevenDaysTracking()
    {
        $responseData = [];
        $statusCode = 200;
        try {
            $lastSevenData = Auth::user()->lastSevenDaysTracking;

            if ($lastSevenData->isEmpty()) {
                $responseData = [
                    'status' => false,
                    'message' => '',
                    'data' => null
                ];
                $statusCode = 204;
                return response()->json($responseData, $statusCode);
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
                    ">7hours" => 7.5,
                    "5-6hours" => 5.5,
                    "4-5hours" => 4.5,
                    "3-4hours" => 3.5,
                    "<3hours" => 2.5,
                };

                $sum['screen_time'] += match (strtolower(implode('', explode(' ', $data['screen_time'])))) {
                    "<1hours" => 0.5,
                    "1-2hours" => 1.5,
                    "2-3hours" => 2.5,
                    "3-4hours" => 3.5,
                    ">5hours" => 5.5,
                };

                $sum['activity'] += match (strtolower(implode('', explode(' ', $data['activity'])))) {
                    "<500steps" => 0.25,
                    "500-1ksteps" => 0.75,
                    "1k-3ksteps" => 2,
                    "3k-5ksteps" => 4,
                    ">6ksteps" => 6.5,
                };
            }

            $responseData =
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => [
                        'tracking_data' => $lastSevenData,
                        'average_mood' => match ((int) round($sum['mood'] / count($lastSevenData))) {
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
                ];
        } catch (UnhandledMatchError $err) {
            $responseData =
                [
                    'status' => false,
                    'message' => 'Wrong data format'
                ];
            $statusCode = 500;
        } catch (Throwable $err) {
            $responseData =
                [
                    'status' => false,
                    'message' => $err->getMessage()
                ];
            $statusCode = 500;
        }
        return response()->json($responseData, $statusCode);
    }
}
