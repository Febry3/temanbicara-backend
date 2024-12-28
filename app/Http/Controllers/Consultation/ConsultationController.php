<?php

namespace App\Http\Controllers\Consultation;

use Illuminate\Http\Request;
use \Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Consultations;
use App\Models\User;
use App\Models\Schedule;


class ConsultationController extends Controller
{
    public static function getConsultation(Request $request)
{
    try {
        $userId = $request->input('user_id');

        $generalUsers = User::where('role', 'General')->get();
        $counselorUsers = User::where('role', 'Counselor')->get();
        $schedules = Schedule::all();

        $consultations = Consultations::all()
            ->map(function ($consultation) use ($generalUsers, $counselorUsers, $schedules) {
                $generalUser = $generalUsers->firstWhere('id', $consultation->user_id);
                $schedule = $schedules->firstWhere('schedule_id', $consultation->schedule_id);
                $counselor = $counselorUsers->firstWhere('id', $schedule->user_id ?? null);

                return [
                    'general_user_name' => $generalUser->name ?? 'Unknown',
                    'birthdate' => $generalUser->birthdate ?? null,
                    'consultations' => [
                        'consultation_id' => $consultation->consultation_id,
                        'status' => $consultation->status,
                        'description' => $consultation->description,
                        'problem' => $consultation->problem,
                        'summary' => $consultation->summary,
                        'schedule' => $schedule ? [
                            'schedule_id' => $schedule->schedule_id,
                            'date' => $schedule->available_date->format('Y-m-d'),
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                        ] : null,
                        'counselor_name' => $counselor ? $counselor->name : 'Unknown',
                    ],
                ];
            })->values();

        return response()->json([
            'status' => true,
            'message' => 'Data Consultations grouped by user_id',
            'data' => $consultations,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}


    public static function updateConsultation(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'description' => 'required|string',
                'problem' => 'required|string',
                'summary' => 'required|string',
                'status' => 'required',
            ]);

            $consultation = Consultations::findOrFail($id);

            $consultation->update([
                'description' => $validatedData['description'],
                'problem' => $validatedData['problem'],
                'summary' => $validatedData['summary'],
                'status' => $validatedData['status'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Consultation updated successfully',
                'data' => $consultation,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
