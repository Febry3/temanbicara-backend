<?php

namespace App\Http\Controllers\Payment;

use Exception;
use Throwable;
use App\Models\User;
use App\Models\Payment;
use App\Models\Schedule;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Consultations;
use App\Http\Controllers\Controller;
use App\Jobs\PaymentSuccessEmailJob;
use Illuminate\Support\Facades\Http;


class PaymentController extends Controller
{
    private string $serverKey;
    private $authentication;

    public function __construct()
    {
        $this->serverKey = config('midtrans.key');
        $this->authentication = Http::withBasicAuth($this->serverKey, '');
    }

    public function createPayment(Request $request)
    {
        try {
            $orderId = Str::uuid()->toString();

            $midTransResponse = $this->authentication
                ->post(
                    'https://api.sandbox.midtrans.com/v2/charge',
                    [
                        "payment_type" => "bank_transfer",
                        "transaction_details" => [
                            "order_id" => $orderId,
                            "gross_amount" => $request->amount,
                        ],
                        "bank_transfer" => [
                            'bank' => strtoupper($request->bank),
                        ],
                        "custom_expiry" => [
                            "expiry_duration" => 20,
                            "unit" => "minute"
                        ]
                    ]
                );
            return [
                'amount' => $request->amount,
                'expired_date' => date('Y-m-d H:i:s', strtotime($midTransResponse['transaction_time'] . '+20 minutes')),
                'bank' => $request->bank,
                'va_number' => $midTransResponse['va_numbers'][0]['va_number'],
                'payment_method' => 'Bank Transfer',
                'transaction_id' => $midTransResponse['transaction_id']
            ];
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err->getMessage(),
                ],
                500
            );
        }
    }

    public function checkPaymentStatus(string $uuid)
    {
        $midTransResponse = $this->authentication
            ->get(
                "https://api.sandbox.midtrans.com/v2/$uuid/status",
            );
        return $midTransResponse->json();
    }

    public function handlePaymentNotification(Request $request)
    {
        try {
            if ($request->transaction_status == 'pending') {
                return response()->json(
                    [
                        'status' => true,
                        'message' => 'Pembayaran masih tertunda',
                    ],
                    200
                );
            }
            $payment = Payment::where('transaction_id', $request->transaction_id)->first();
            $payment->completePayment();
            $consultation = Consultations::where("payment_id", $payment->payment_id)->first();
            $customer = User::findOrFail($consultation->patient_id);

            PaymentSuccessEmailJob::dispatch($customer->name, $payment->transaction_id, $payment->amount, $customer->email);

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Pembayaran berhasil',
                ],
                200
            );
        } catch (Throwable $err) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $err,
                ],
                500
            );
        }
    }
}
