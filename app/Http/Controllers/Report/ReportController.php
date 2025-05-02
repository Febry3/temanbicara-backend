<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\TrackJournalResponse;
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
                ->get();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
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
