<?php

namespace App\Http\Controllers\Payment;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Throwable;

class PaymentController extends Controller
{
    private String $serverKey;

    public function __construct()
    {
        $this->serverKey = config('midtrans.key');
    }

    public function createPayment(Request $request)
    {
        try {
            $orderId = Str::uuid()->toString();

            $midTransResponse = Http::withBasicAuth($this->serverKey, '')
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
                    ]
                );

            return [
                'amount' => $request->amount,
                'expired_date' => date('Y-m-d H:i:s', strtotime($midTransResponse['transaction_time'] . '+24 hours')),
                'bank' => $request->bank,
                'va_number' => $midTransResponse['va_numbers'][0]['va_number'],
                'payment_method' => 'Bank Transfer',
                'transaction_id' => $midTransResponse['transaction_id']
            ];
        } catch (Throwable $err) {
            throw new Exception($err->getMessage());
        }
    }

    public function checkPaymentStatus(String $uuid)
    {
        $midTransResponse = Http::withBasicAuth($this->serverKey, '')
            ->get(
                "https://api.sandbox.midtrans.com/v2/$uuid/status",
            );

        return $midTransResponse;
    }
}
