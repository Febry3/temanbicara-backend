<?php

namespace App\Http\Controllers\Schedule;

use Illuminate\Http\Request;
use Throwable;
use App\Models\Schedule;
use App\Models\User;
use App\Models\Expertise;
use App\Http\Controllers\Controller;

class ScheduleController extends Controller
{
    public static function getSchedule(Request $request)
    {
        try {
            //menggunakan eager loaging mengurai jumlah execute query
            $users = User::where('role', 'Counselor')->with([
                'expertises',
                'schedules'
            ])->select('id', 'name')->get();

            $schedules = $users->map(function ($user) {
                return [
                    'name' => $user ? $user->name : 'Unknown',
                    'expertise' => optional($user->expertises)->type ?? 'None',
                    'schedules' => $user->schedules->groupBy(function ($schedule) {
                        return $schedule->available_date->format('Y-m-d');
                    })->map(function ($dateSchedules, $date) {
                        return [
                            'date' => $date,
                            'schedulesByDate' => $dateSchedules->map(function ($schedule) {
                                return [
                                    'schedule_id' => $schedule->schedule_id,
                                    'start_time' => $schedule->start_time,
                                    'end_time' => $schedule->end_time,
                                    'status' => $schedule->status,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Data Schedule grouped by counselor ID',
                'data' => $schedules,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function getAvailableSchedule(Request $request)
    {
        try {
            $users = User::where('role', 'Counselor')->with([
                'expertises',
                'schedules' => function ($schedule) {
                    $schedule->whereDate('available_date', '>=', now())->where('status', '=', 'Available')->orderBy('available_date');
                }
            ])->select('id', 'name')->get();

            // dd($users);

            $availableSchedules = $users->map(function ($user) {
                return [
                    'name' => $user ? $user->name : 'Unknown',
                    'expertise' => $user->expertises->isNotEmpty()
                    ? $user->expertises->pluck('type')->toArray()
                    : ['None'],
                    'schedules' => $user->schedules->groupBy(function ($schedule) {
                        return $schedule->available_date->format('Y-m-d');
                    })->map(function ($dateSchedules, $date) {
                        return [
                            'date' => $date,
                            'schedulesByDate' => $dateSchedules->map(function ($schedule) {
                                return [
                                    'schedule_id' => $schedule->schedule_id,
                                    'start_time' => $schedule->start_time,
                                    'end_time' => $schedule->end_time,
                                    'status' => $schedule->status,
                                ];
                            })->values(),
                        ];
                    })->values(),
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Available schedules grouped by counselor ID',
                'data' => $availableSchedules,
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

            $user = User::where('role', 'Counselor')->where('id', $id)->with([
                'expertises',
                'schedules'
            ])->select('id', 'name')->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found or does not have Counselor role',
                ], 404);
            }

            $schedules = [
                'name' => $user->name ?? 'Unknown',
                'expertise' => $user->expertises->isNotEmpty()
                    ? $user->expertises->pluck('type')->toArray()
                    : ['None'],
                'schedules' => $user->relationLoaded('schedules') && $user->schedules->isNotEmpty()
                    ? $user->schedules->groupBy(fn($schedule) => $schedule->available_date->format('Y-m-d'))
                        ->map(fn($dateSchedules, $date) => [
                            'date' => $date,
                            'schedulesByDate' => $dateSchedules->map(fn($schedule) => [
                                'schedule_id' => $schedule->schedule_id,
                                'start_time' => $schedule->start_time,
                                'end_time' => $schedule->end_time,
                                'status' => $schedule->status,
                            ])->values(),
                        ])->values()
                    : [],
            ];
            return response()->json([
                'status' => true,
                'message' => 'Data Schedule for user',
                'data' => $schedules
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function getAvailableScheduleByID(Request $request, $id)
    {
        try {

            $user = User::where('role', 'Counselor')->where('id', $id)->with([
                'expertises',
                'schedules' => function ($schedule) {
                    $schedule->whereDate('available_date', '>=', now())->where('status', '=', 'Available')->orderBy('available_date');
                }
            ])->select('id', 'name')->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found or does not have Counselor role',
                ], 404);
            }

            $schedules = [
                'name' => $user->name ?? 'Unknown',
                'expertise' => $user->expertises->isNotEmpty()
                    ? $user->expertises->pluck('type')->toArray()
                    : ['None'],
                'schedules' => $user->relationLoaded('schedules') && $user->schedules->isNotEmpty()
                    ? $user->schedules->groupBy(fn($schedule) => $schedule->available_date->format('Y-m-d'))
                        ->map(fn($dateSchedules, $date) => [
                            'date' => $date,
                            'schedulesByDate' => $dateSchedules->map(fn($schedule) => [
                                'schedule_id' => $schedule->schedule_id,
                                'start_time' => $schedule->start_time,
                                'end_time' => $schedule->end_time,
                                'status' => $schedule->status,
                            ])->values(),
                        ])->values()
                    : [],
            ];
            return response()->json([
                'status' => true,
                'message' => 'Data Schedule for user',
                'data' => $schedules
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function createSchedule(Request $request)
    {
        try {
            $userId = $request->input('counselor_id');
            $user = User::where('role', 'Counselor')->find($userId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found or does not have Counselor role',
                ], 404);
            }
            $validated = $request->validate([
                'available_date' => 'required|date',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
                'status' => 'required|in:Available,Booked,Done',
                'counselor_id' => 'required|exists:users,id',
            ]);
            $schedule = Schedule::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Schedule created successfully',
                'data' => $schedule,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function updateScheduleStatus(Request $request, $id)
    {
        try {
            $schedule = Schedule::find($id);
            if (!$schedule) {
                return response()->json([
                    'status' => false,
                    'message' => 'Schedule not found',
                ], 404);
            }
            $schedule->status = 'Booked';
            $schedule->save();

            return response()->json([
                'status' => true,
                'message' => 'Schedule status updated successfully to booked',
                'data' => [
                    'schedule_id' => $schedule->schedule_id,
                    'status' => $schedule->status,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }



}
