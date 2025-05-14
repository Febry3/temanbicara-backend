<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\Tracking;
use App\Models\TrackJournalResponse;
use GuzzleHttp\Promise\Create;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Ai\AiController;
use Throwable;

class ReportController extends Controller
{

    public function getReport(Request $request)
    {
        try {
            $requestedData = $request->only([
                'date_request'
            ]);

            Validator::make(
                $requestedData,
                [
                    'date_request' => ['required', 'date_format:YYYY-mm-dd'],
                ]
            );

            $datereq = $request['date_request'];

            $report = Observation::with('track_journal_response.tracking') // eager load
            ->whereHas('track_journal_response.tracking', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id);
            })
            ->whereDate('created_at', $datereq)
            ->get();

            if ($report->isEmpty()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Data report tidak tersedia atau format tanggal salah',
                    ],

                );
            }
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data response tersedia',
                    'data' => $report
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
    public function doReport($userId)
    {
        try {
            $result = app(AiController::class)->generate($userId)->getData(true);

            $response = TrackJournalResponse::updateOrCreate(
                ['tracking_id' => $result['tracking_id']],
                [
                    'assesment' => $result['result']['assessment'] ?? null,
                    'metrix' => $result['result']['matrix'] ?? null,
                    'short_term' => json_encode($result['result']['recommendations']['short_term'] ?? []),
                    'long_term' => json_encode($result['result']['recommendations']['long_term'] ?? []),
                    'closing' => $result['result']['closing'] ?? null,
                ]
            );
            $observations = $result['result']['observations'] ?? [];
            Observation::updateOrCreate(
                ['response_id' => $response->response_id],
                [
                    'mood' => $observations['mood'] ?? '',
                    'sleep' => $observations['sleep'] ?? '',
                    'stress' => $observations['stress'] ?? '',
                    'screen_time' => $observations['screen_time'] ?? '',
                    'activity' => $observations['activity'] ?? '',
                ]
            );
            return response()->json([
                'data' => $result
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Failed to store data',
                'message' => $e->getMessage()
            ]);
        }

    }
}
