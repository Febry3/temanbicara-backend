<?php

namespace App\Http\Controllers\Schedule;

use Illuminate\Http\Request;
use Throwable;
use App\Models\Schedules;
use App\Models\User;
use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public static function getSchedule(Request $request)
    {
        try {
            $users = User::where('role', 'Admin')->get();
            $schedules = Schedules::all()
                ->groupBy('user_id') 
                ->map(function ($userSchedules, $userId) use ($users) {
                    $user = $users->firstWhere('id', $userId);           
                    return [
                        'user_name' => $user ? $user->name : 'Unknown', 
                        'schedules' => $userSchedules->groupBy(function ($schedule) {
                            return $schedule->available_date->format('Y-m-d');
                        })
                        ->map(function ($dateSchedules) {
                            return $dateSchedules->map(function ($schedule) {   
                                return [
                                    'schedule_id' => $schedule->schedule_id,
                                    'start_time' => $schedule->start_time,
                                    'end_time' => $schedule->end_time,
                                    'is_available' => $schedule->is_available,
                                ];
                            });
                        })
                    ];
                });
    
            return response()->json([
                'status' => true,
                'message' => 'Data Schedule grouped by user_id',
                'data' => $schedules,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function getScheduleByID(Request $request, $id)
    {
        try {
            $user = User::where('role', 'Admin')->find($id); 
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found or does not have Admin role',
                ], 404);
            }

            $schedules = Schedules::where('user_id', $id)->get()
                ->groupBy(function ($schedule) {
                    return $schedule->available_date->format('Y-m-d');
                })
                ->map(function ($dateSchedules) {
                    return $dateSchedules->map(function ($schedule) {
                        return [
                            'schedule_id' => $schedule->schedule_id,
                            'start_time' => $schedule->start_time,
                            'end_time' => $schedule->end_time,
                            'is_available' => $schedule->is_available,
                        ];
                    });
                });

            return response()->json([
                'status' => true,
                'message' => 'Data Schedule grouped by date for the user',
                'user_name' => $user->name,
                'data' => $schedules,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
 