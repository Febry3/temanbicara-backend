<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Article;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Helper\ImageRequestHelper;
use App\Http\Requests\CreateUserRequest;
use App\Models\Consultations;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function loginAsAdmin(Request $request)
    {
        try {
            $requestedData = $request->only([
                'email',
                'password'
            ]);

            $validateData = Validator::make(
                $requestedData,
                [
                    'email' => 'required',
                    'password' => 'required',
                ]
            );

            if ($validateData->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email dan password tidak boleh kosong',
                    'error' => $validateData->errors(),
                ], 200);
            };

            $user = User::where('email', $requestedData['email'])->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email tidak sesuai',
                ], 200);
            }

            if ($user->role !== "Admin") {
                return response()->json([
                    'status' => false,
                    'message' => 'Role tidak sesuai',
                ], 200);
            }

            if (!Auth::attempt(["email" => $requestedData['email'], "password" => $requestedData['password']])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password tidak sesuai',
                ], 401);
            }

            $request->session()->regenerate();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Login berhasil',
                    'token' => $user->createToken('RevanGay', [$user->role])->plainTextToken,
                    'data' => $user,
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

    public function logoutAsAdmin(Request $request)
    {
        $request->user()->tokens()->delete();
        Auth::guard('web')->logout();
        $request->session()->invalidate();

        return response()->json(
            [
                'status' => true,
                'message' => 'Logged Out',
            ],
            200
        )->withCookie(
            cookie()->forget('laravel_session')
        );;
    }

    public function createUser(CreateUserRequest $request)
    {
        try {
            $role = $request->input("role");
            $imageUrl = "";

            $request->validated();

            if ($request->hasFile('image')) {
                $response = ImageRequestHelper::postImageToSupabase($request, 'profile');
                $imageUrl = config('supabase.url') . '/' . $response->json()['Key'];

                if ($response->failed()) {
                    return response()->json(
                        [
                            'status' => false,
                            'message' => 'Kesalahan pada mengupload gambar',
                        ],
                        404
                    );
                }
            } else {
                $imageUrl = config('supabase.url') . '/profile/default.png';
            }


            $user = User::create([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'password' => $request->password,
                'role' => trim(Str::title($role), '"'),
                'name' => $request->name,
                'nickname' => $request->nickname,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
                'profile_url' => $imageUrl
            ]);
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil disimpan',
                    'data' => $user
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

    public function getAdminData(Request $request)
    {
        try {
            $admin = $request->user();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil didapat',
                    'data' => $admin
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

    public function getCounselorData()
    {
        try {
            $admins = User::with(['schedules' => function ($scheduleQuery) {
                $scheduleQuery->where('status', 'Done')
                    ->whereHas('consultation', function ($consultationQuery) {
                        $consultationQuery->whereHas('payment', function ($paymentQuery) {
                            $paymentQuery->where('payment_status', 'Success');
                        });
                    })
                    ->with('consultation.payment');
            }])
                ->get()
                ->map(function ($user) {
                    $totalConsultations = count($user->schedules);
                    $revenue = 0;

                    foreach ($user->schedules as $schedule) {
                        if ($schedule->consultation && $schedule->consultation->payment) {
                            $revenue += $schedule->consultation->payment->amount;
                        }
                    }

                    return [
                        "id" => $user->id,
                        "email" => $user->email,
                        "phone_number" => $user->phone_number,
                        "name" => $user->name,
                        "nickname" => $user->nickname,
                        "gender" => $user->gender,
                        "birthdate" => $user->birthdate,
                        "role" => $user->role,
                        "totalConsultations" => $totalConsultations,
                        "revenue" => $revenue,
                        "created_at" => $user->created_at,
                    ];
                });

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil didapat',
                    'data' => $admins
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

    public function getUserById(int $id)
    {
        try {
            $user = User::find($id);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil didapat',
                    'data' => $user
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

    public function updateUser(Request $request, int $id)
    {
        try {
            User::find($id)->update([
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'name' => $request->name,
                'nickname' => $request->nickname,
                'gender' => $request->gender,
                'birthdate' => $request->birthdate,
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diupdate',
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

    public function deleteUser(int $id)
    {
        try {
            User::find($id)->delete();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil dihapus',
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

    //payment page
    public function processTransactions(Collection $transactions): Collection
    {
        $collection = collect($transactions);

        $todaysTransactions = $collection->filter(function ($transaction) {
            return Carbon::parse($transaction['transactionDate'])->isToday();
        });

        $groupedByInterval = $todaysTransactions->groupBy(function ($transaction) {
            return floor(Carbon::parse($transaction['transactionDate'])->hour / 3);
        });

        $summaryTemplate = collect(range(0, 7))->mapWithKeys(function ($key) {
            $startHour = $key * 3;
            $timeRange = sprintf('%02d:00', $startHour);

            return [
                $timeRange => [
                    'time' => $timeRange,
                    'totalTransactions' => 0,
                ]
            ];
        });

        $finalSummary = $groupedByInterval->reduce(function ($summary, $group, $key) {
            $startHour = $key * 3;
            $timeRange = sprintf('%02d:00', $startHour);
            $totalTransactions = $group->count();

            $summary[$timeRange] = [
                'time' => $timeRange,
                'totalTransactions' => $totalTransactions,
            ];

            return $summary;
        }, $summaryTemplate);

        return $finalSummary->values();
    }

    public function getAllPayment()
    {
        try {
            $payments = Payment::whereHas('consultation', function ($consultationQuery) {
                $consultationQuery->whereHas('user');
            })
                ->with('consultation.user')
                ->get()
                ->map(function ($data) {
                    return [
                        "name" => $data->consultation->user->name,
                        "bank" => $data->bank,
                        "status" => $data->payment_status,
                        "transactionId" => $data->transaction_id,
                        "method" => $data->payment_method,
                        "amount" => $data->amount,
                        "transactionDate" => $data->created_at
                    ];
                });
            $groupedData = $this->processTransactions($payments);
            $countSuccess = 0;
            $countPending = 0;
            $countFailed = 0;

            foreach ($payments as $payment) {
                switch ($payment['status']) {
                    case 'Success':
                        $countSuccess++;
                        break;
                    case 'Pending':
                        $countPending++;
                        break;
                    case 'Failed':
                        $countFailed++;
                        break;
                }
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => [
                        "payments" => $payments,
                        "countSuccess" => $countSuccess,
                        "countPending" => $countPending,
                        "countFailed" => $countFailed,
                        "groupedData" => $groupedData
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

    //article
    public function getAllArticle()
    {
        try {
            $articles = Article::all();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Data berhasil diambil',
                    'data' => $articles
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

    public function updateArticleStatus(Request $request, string $id)
    {
        try {
            Article::where("article_id", $id)->update([
                "status" => $request->status
            ]);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Status berhasil diubah',
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

    public function getDashboardData()
    {
        try {
            $users = User::all();
            $counselorCount = 0;
            $userCount = count($users);
            $male = 0;
            $female = 0;

            foreach ($users as $user) {
                if ($user->role === "Counselor") {
                    $counselorCount++;
                }

                if ($user->gender === "male") {
                    $male++;
                }

                if ($user->gender === "female") {
                    $female++;
                }
            }

            $consultations = Consultations::all();
            $consultationCount = count($consultations);

            $payments = Payment::where("payment_status", "Success")->get();
            $totalAllRevenue = 0;

            foreach ($payments as $payment) {
                $totalAllRevenue += $payment->amount;
            }

            $startDate = Carbon::now()->subMonths(11)->startOfMonth();

            $rawData = User::selectRaw("
                    DATE_FORMAT(created_at, '%M') as month,
                    MONTH(created_at) as month_num,
                    gender,
                    COUNT(*) as count
                ")
                ->where('created_at', '>=', $startDate)
                ->groupByRaw("MONTH(created_at), DATE_FORMAT(created_at, '%M'), gender")
                ->orderBy('month_num')
                ->get();

            $months = collect();
            for ($i = 0; $i < 12; $i++) {
                $date = Carbon::now()->subMonths(11 - $i);
                $monthName = $date->format('F');
                $months->put($monthName, ['male' => 0, 'female' => 0]);
            }

            $final = $months->map(function ($counts, $monthName) use ($rawData) {
                $group = $rawData->where('month', $monthName);
                return [
                    'male' => $group->where('gender', 'male')->sum('count'),
                    'female' => $group->where('gender', 'female')->sum('count'),
                ];
            })->map(function ($counts, $month) {
                return [
                    'month' => $month,
                    'male' => $counts['male'],
                    'female' => $counts['female'],
                ];
            })->values();

            $startDate = Carbon::now()->subDays(29)->startOfDay();

            $rawData = Payment::selectRaw("
                    DATE(created_at) as date,
                    SUM(amount) as total
                ")
                ->where('payment_status', 'Success')
                ->where('created_at', '>=', $startDate)
                ->groupByRaw('DATE(created_at)')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $revenueData = collect();
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays(29 - $i)->toDateString();
                $revenueData->push([
                    'date' => $date,
                    'total' => $rawData->get($date)->total ?? 0,
                ]);
            }


            $rawData = Consultations::selectRaw("
                DATE(created_at) as date,
                COUNT(*) as total
            ")
                ->where('created_at', '>=', $startDate)
                ->groupByRaw('DATE(created_at)')
                ->orderBy('date')
                ->get()
                ->keyBy('date');

            $consultionData = collect();
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays(29 - $i)->toDateString();
                $consultionData->push([
                    'date' => $date,
                    'total' => $rawData->get($date)->total ?? 0,
                ]);
            }

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Status berhasil diubah',
                    'data' => [
                        'counselorCount' => $counselorCount,
                        'userCount' => $userCount,
                        'consultationCount' => $consultationCount,
                        'totalRevenue' => $totalAllRevenue,
                        'maleUser' => $male,
                        'femaleUser' => $female,
                        'userChart' => $final,
                        'revenueChart' => $revenueData,
                        'consultationChart' => $consultionData
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
