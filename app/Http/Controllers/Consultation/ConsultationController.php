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
    public static function getConsultation()
    {
        try {
            $consultations = Consultations::with([
                'user:id,name,birthdate',
                'schedule:schedule_id,available_date,start_time,end_time,counselor_id',
                'schedule.user:id,name'
            ])
            ->get()
            ->map(function ($consultation) {
                return [
                    'consultation_id' => $consultation->consultation_id,
                    'status' => $consultation->status,
                    'description' => $consultation->description,
                    'problem' => $consultation->problem,
                    'summary' => $consultation->summary,
                    'patient_id' => $consultation->patient_id,
                    'general_user_name' => $consultation->user->name ?? null,
                    'birthdate' => $consultation->user->birthdate ?? null,
                    'schedule_id' => $consultation->schedule->schedule_id ?? null,
                    'date' => $consultation->schedule->available_date ?? null,
                    'start_time' => $consultation->schedule->start_time ?? null,
                    'end_time' => $consultation->schedule->end_time ?? null,
                    'counselor_name' => $consultation->schedule->user->name ?? null,
                    'counselor_id' => $consultation->schedule->counselor_id ?? null,
                ];
            });

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


    public static function createConsultation(Request $request)
    {
        try {
            
            $validated = $request->validate([
                'description' => 'nullable',
                'problem' => 'nullable',
                'summary' => 'nullable',
                'schedule_id' => 'required|exists:schedules,schedule_id',
                'patient_id' => 'required|exists:users,id',
            ]);

            $consultation = Consultations::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Consultation created successfully.',
                'data' => $consultation,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function getConsultationByUserId(Request $request)
    {
        try {

            $userId = $request->user()->id;
            $consultations = Consultations::with([
                'user:id,name,birthdate',
                'schedule:schedule_id,available_date,start_time,end_time,counselor_id',
                'schedule.user:id,name'
            ])
            ->where('consultations.patient_id', $userId)
            ->get()
            ->map(function ($consultation) {
                return [
                    'consultation_id' => $consultation->consultation_id,
                    'status' => $consultation->status,
                    'description' => $consultation->description,
                    'problem' => $consultation->problem,
                    'summary' => $consultation->summary,
                    'patient_id' => $consultation->patient_id,
                    'general_user_name' => $consultation->user->name ?? null,
                    'birthdate' => $consultation->user->birthdate ?? null,
                    'schedule_id' => $consultation->schedule->schedule_id ?? null,
                    'date' => $consultation->schedule->available_date ?? null,
                    'start_time' => $consultation->schedule->start_time ?? null,
                    'end_time' => $consultation->schedule->end_time ?? null,
                    'counselor_name' => $consultation->schedule->user->name ?? null,
                    'counselor_id' => $consultation->schedule->counselor_id ?? null,
                ];
            });
            return response()->json([
                'status' => true,
                'message' => 'Data consultations for the logged-in user',
                'data' => $consultations,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function getConsultationByCounselorId(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $consultations = DB::table('consultations')
                ->join('users as general_users', 'consultations.patient_id', '=', 'general_users.id')
                ->leftJoin('schedules', 'consultations.schedule_id', '=', 'schedules.schedule_id')
                ->leftJoin('users as counselors', 'schedules.counselor_id', '=', 'counselors.id')
                ->select(
                    'consultations.consultation_id',
                    'consultations.status',
                    'consultations.description',
                    'consultations.problem',
                    'consultations.summary',
                    'consultations.patient_id',
                    'general_users.name as general_user_name',
                    'general_users.birthdate',
                    'general_users.gender',
                    'general_users.nickname',
                    'consultations.schedule_id',
                    'schedules.available_date as date',
                    'schedules.start_time',
                    'schedules.end_time',
                    'counselors.name as counselor_name',
                    'counselors.id as counselor_id',
                )
                ->where('schedules.counselor_id', $userId)
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'Data consultloations for the logged-in user',
                'data' => $consultations,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


}
