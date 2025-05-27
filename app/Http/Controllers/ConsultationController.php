<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Models\Consultations;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PaymentController;
use App\Jobs\PaymentEmailJob;
use App\Jobs\UpdateExpiredPaymentJob;
use Carbon\Carbon;


class ConsultationController extends Controller
{
    private const REQUIRED = 'required|string';
    public static function getConsultation()
    {
        try {
            $consultations = Consultations::with([
                'user:id,name,birthdate',
                'schedule:schedule_id,status,available_date,start_time,end_time,counselor_id',
                'schedule.user:id,name'
            ])
                ->where('status', "!=", "Cancelled")
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
        } catch (Throwable $e) {
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
                'description' => self::REQUIRED,
                'problem' => self::REQUIRED,
                'summary' => self::REQUIRED,
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
            $request->validate([
                'description' => 'nullable',
                'problem' => 'nullable',
                'summary' => 'nullable',
                'schedule_id' => 'required|exists:schedules,schedule_id',
                'amount' => 'required|integer',
                'bank' => self::REQUIRED,
            ]);

            DB::beginTransaction();

            $schedule = Schedule::find($request->schedule_id);

            if ($schedule->status === 'Booked') {
                return response()->json([
                    'status' => false,
                    'message' => 'Schedule already booked',
                ], 422);
            }

            $schedule->status = 'Booked';
            $schedule->save();

            $payment = app(PaymentController::class)->createPayment($request);

            $paymentAfterCreated = Payment::create($payment);

            $consultation = Consultations::create([
                'description' => $request->description,
                'problem' => $request->problem,
                'summary' => $request->summary,
                'schedule_id' => $request->schedule_id,
                'patient_id' => Auth::user()->id,
                'payment_id' => $paymentAfterCreated->payment_id
            ]);

            UpdateExpiredPaymentJob::dispatch($paymentAfterCreated->payment_id)->delay(Carbon::now('Asia/Jakarta')->diffInSeconds(Carbon::parse($paymentAfterCreated->expired_date)));
            PaymentEmailJob::dispatch(Auth::user()->name, $paymentAfterCreated->bank, $paymentAfterCreated->expired_date, $paymentAfterCreated->amount, $paymentAfterCreated->va_number, $paymentAfterCreated->payment_method, Auth::user()->email);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Consultation created successfully',
                'data' => [
                    "description" => $consultation->description,
                    "problem" => $consultation->problem,
                    "summary" => $consultation->summary,
                    "amount" => $paymentAfterCreated->amount,
                    "expired_date" => $paymentAfterCreated->expired_date,
                    "bank" => $paymentAfterCreated->bank,
                    "va_number" => $paymentAfterCreated->va_number,
                    "payment_method" => $paymentAfterCreated->payment_method,
                    "transaction_id" => $paymentAfterCreated->transaction_id,
                    "status" => $paymentAfterCreated->payment_status,

                ],
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
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
                'schedule.user:id,name',
                'payment'
            ])
                ->where("patient_id",$userId)
                ->get()
                ->map(function ($consultation) {
                    return [
                        'consultation_id' => $consultation->consultation_id,
                        'status' => $consultation->status,
                        'status_payment'=> $consultation->payment->payment_status,
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
        } catch (Throwable $e) {
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
            $consultations = Consultations::with([
                'schedule',
                'user'
            ])
                ->where('status', "!=", "Cancelled")
                ->whereHas('schedule', function ($query) use ($userId) {
                    $query->where('counselor_id', $userId);
                })->get();


            return response()->json([
                'status' => true,
                'message' => 'Data Consultations for the logged-in user',
                'data' => $consultations,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function getConsultationAndPaymentInfo($id)
    {
        try {
            $data = Consultations::where('consultation_id', $id)
                ->join('payments', 'consultations.payment_id', 'payments.payment_id')
                ->select('description', 'problem', 'summary', 'schedule_id', 'patient_id', 'consultations.payment_id', 'consultation_id', 'amount', 'expired_date', 'bank', 'va_number', 'payment_method', 'transaction_id')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Payment status',
                'data' => $data
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public static function checkConsulationPaymentStatus($id)
    {
        try {

            $paymentStatus = app(PaymentController::class)->checkPaymentStatus($id);

            if (!in_array($paymentStatus['status_code'], ['200', '201'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error occured',
                ], 500);
            }

            if ($paymentStatus['transaction_status'] === 'settlement') {
                Payment::where('transaction_id', $id)->update(['payment_status' => 'Success']);
            }

            if ($paymentStatus['transaction_status'] === 'expired') {
                DB::beginTransaction();
                Payment::where('transaction_id', $id)->update(['payment_status' => 'Expired']);
                Consultations::where('consultation_id', $id)->update(['status' => 'Cancelled']);
                Schedule::where('schedule_id', Consultations::findOrFail($id)->schedule_id)->update(['status' => 'Available']);
                DB::commit();
            }

            return response()->json([
                'status' => true,
                'message' => 'Payment status',
                'data' => $paymentStatus['transaction_status'] === 'settlement' ? 'Success' : $paymentStatus['transaction_status'],
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function cancelConsultation($id)
    {
        try {
            DB::beginTransaction();
            $consultation = Consultations::with('payment')
                ->where('consultation_id', $id)
                ->whereNotIn('status', ['Done', 'Cancelled'])
                ->update(['status' => 'Cancelled']);
            if (!$consultation) {
                return response()->json([
                    'status' => true,
                    'message' => 'Consultation cant be cancelled either done or already cancelled',
                ], 200);
            }

            Schedule::where('schedule_id', Consultations::findOrFail($id)->schedule_id)->update(['status' => "Available"]);
            Payment::where('payment_id', Consultations::findOrFail($id)->payment_id)->update(['payment_status' => "Expired"]);
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Consultation cancelled',
            ], 201);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public static function bookingHistoryConsultation(Request $request)
    {
        try {
            $status = $request['payment_status'];
            $userId = $request->user()->id;

            $consultations = Consultations::with(['payment', 'schedule.user.expertises'])
                ->whereHas('payment', function ($query) use ($status) {
                    $query->where('payment_status', $status);
                })->where('patient_id', $userId)
                ->get();
            return response()->json([
                'status' => true,
                'message' => 'History Consultation',
                'data' => $consultations,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
