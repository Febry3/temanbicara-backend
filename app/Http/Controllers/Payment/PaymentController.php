<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Consultations;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;


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
            throw new Exception($err->getMessage());
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

    public function handleCompletedPayment(Request $request)
    {
        if (!$request['transaction_status'] == 'settlement') return;

        $payment = Payment::where('transaction_id', $request['transaction_id'])->first();
        $payment->completePayment();
        return response()->json(
            [
                'status' => true,
                'message' => 'Data berhasil diambil',
                'data' => $payment
            ],
            200
        );
    }
}
