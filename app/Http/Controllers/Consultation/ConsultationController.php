<?php

namespace App\Http\Controllers\Consultation;

use Illuminate\Http\Request;
use \Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Consultations;
use App\Models\User;
use App\Models\Schedule;


class ConsultationController extends Controller
{
    public static function getConsultation(Request $request)
    {
        try {
            $consultations = DB::table('consultations')
                ->join('users as general_users', 'consultations.user_id', '=', 'general_users.id')
                ->leftJoin('schedules', 'consultations.schedule_id', '=', 'schedules.schedule_id')
                ->leftJoin('users as counselors', 'schedules.user_id', '=', 'counselors.id')
                ->select(
                    'consultations.consultation_id',
                    'consultations.status',
                    'consultations.description',
                    'consultations.problem',
                    'consultations.summary',
                    'consultations.user_id',
                    'general_users.name as general_user_name',
                    'general_users.birthdate',
                    'consultations.schedule_id',
                    'schedules.available_date as date',
                    'schedules.start_time',
                    'schedules.end_time',
                    'counselors.name as counselor_name'
                )
                ->get();

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
