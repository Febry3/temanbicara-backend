<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\TrackJournalResponse;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Throwable;

class ReportController extends Controller
{

    public function getReport(Request $request)
    {
        try {
            $report = TrackJournalResponse::with('tracking')
                ->whereHas('tracking', function ($query) use ($request) {
                    $query->where('user_id', $request->user()->id);
                })
                ->whereDate('created_at', now()->toDateString())
                ->get();


            if($report->isEmpty()){
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'Data report tidak tersedia',
                    ],
                    200
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
}
