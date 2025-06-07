<?php

namespace App\Http\Controllers;

use Throwable;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;
use App\Http\Helper\ImageRequestHelper;
use App\Http\Requests\CreateUserRequest;

class AdminController extends Controller
{
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

    public function getAdminData()
    {
        try {
            $admins = User::where('role', 'Admin')->get();

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

    public function getCounselorData()
    {
        try {
            $admins = User::whereHas('schedules', function ($scheduleQuery) {
                $scheduleQuery->where('status', 'Done')
                    ->whereHas('consultation', function ($consultationQuery) {
                        $consultationQuery->whereHas('payment', function ($paymentQuery) {
                            $paymentQuery->where('payment_status', 'Success');
                        });
                    });
            })
                ->with('schedules.consultation.payment')
                ->get()
                ->map(function ($data) {
                    $totalConsultations = count($data->schedules);
                    $revenue = 0;
                    foreach ($data->schedules as $temp) {
                        $revenue += $temp->consultation->payment->amount;
                    }
                    return [
                        "id" => $data->id,
                        "email" => $data->email,
                        "phone_number" => $data->phone_number,
                        "name" => $data->name,
                        "nickname" => $data->nickname,
                        "gender" => $data->gender,
                        "birthdate" => $data->birthdate,
                        "role" => $data->role,
                        "totalConsultations" => $totalConsultations,
                        "revenue" => $revenue,
                        "created_at" => $data->created_at
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
}
