<?php

namespace App\Http\Controllers\Dashboard;

use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;

class PaymentController extends Controller
{


    /**
     * Initialize Midtrans Config
     *
     * Set Midtrans Server Key, isProduction, isSanitized, and is3ds
     *
     * @return void
     */
    protected function initMidtrans()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create Snap Token Midtrans
     *
     * This function will generate a Snap Token for a given amount and customer name
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createMidtransTransaction(Request $request)
    {
        if ($request->expectsJson()) {
            $this->initMidtrans();

            $orderId = 'ORDER-' . Str::random(5) . time();
            $grossAmount = $request->amount;

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $grossAmount,
                ],

                'payment_type' => 'qris',
                'qris' => [],

                'customer_details' => [
                    'first_name' => $request->customer_name ?? 'Customer POS',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            return response()->json(['snap_token' => $snapToken]);
        }

        return abort(403, 'Invalid request.');
    }


    /**
     * Midtrans Notification
     *
     * Handle Midtrans notification request and update payment status
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function midtransNotification(Request $request)
    {

        $this->initMidtrans();

        $rawResponse = $request->getContent();
        $notification = json_decode($rawResponse);
        $validSignatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . config('midtrans.server_key'));

        if ($notification->signature_key != $validSignatureKey) {
            return response(['message' => 'Invalid signature'], 403);
        }

        $paymentNotification = new \Midtrans\Notification();

        $paymentTransaction = PaymentTransaction::where('external_id', $notification->order_id)->first();

        if (!$paymentTransaction) {
            return response(['message' => 'Transaksi tidak ditemukan'], 404);
        }

        if ($paymentTransaction->isPaid()) {
            return response(['message' => 'Orderan kamu sudah dibayar'], 422);
        }

        $transaction = $paymentNotification->transaction_status;
        $fraud  = $paymentNotification->fraud_status;
        $type = $paymentNotification->payment_type;

        $paymentStatus = null;

        if ($transaction == 'capture') {
            // For credit card transaction, we need to check whether transaction is challenge by FDS or not
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    // TODO set payment status in merchant's database to 'Challenge by FDS'
                    // TODO merchant should decide whether this transaction is authorized or not in MAP
                    $paymentStatus = 'challenge';
                } else {
                    // TODO set payment status in merchant's database to 'Success'
                    $paymentStatus = 'success';
                }
            }
        } else if ($transaction == 'settlement') {
            // TODO set payment status in merchant's database to 'Settlement'
            $paymentStatus = 'settlement';
        } else if ($transaction == 'pending') {
            // TODO set payment status in merchant's database to 'Pending'
            $paymentStatus = 'pending';
        } else if ($transaction == 'deny') {
            // TODO set payment status in merchant's database to 'Denied'
            $paymentStatus = 'deny';
        } else if ($transaction == 'expire') {
            // TODO set payment status in merchant's database to 'expire'
            $paymentStatus = 'expired';
        } else if ($transaction == 'cancel') {
            // TODO set payment status in merchant's database to 'Denied'
            $paymentStatus = 'cancel';
        }

        $paymentTransaction->update([
            'payment_type'  => $type,
            'status'        => $paymentStatus,
            'raw_response'  => $rawResponse
        ]);

        return response(['message' => 'Success'], 200);
    }
}
