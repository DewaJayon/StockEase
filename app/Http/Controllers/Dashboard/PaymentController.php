<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Create Snap Token Midtrans.
     */
    public function createMidtransTransaction(Request $request)
    {
        if ($request->expectsJson()) {
            try {
                $snapToken = $this->paymentService->createSnapToken(
                    (float) $request->amount,
                    $request->customer_name
                );

                return response()->json(['snap_token' => $snapToken]);
            } catch (\Throwable $th) {
                return response()->json(['message' => 'Gagal membuat token pembayaran: '.$th->getMessage()], 500);
            }
        }

        return abort(403, 'Invalid request.');
    }

    /**
     * Midtrans Notification handler.
     */
    public function midtransNotification(Request $request)
    {
        try {
            $rawBody = $request->getContent();
            $notificationData = json_decode($rawBody, true);

            $result = $this->paymentService->handleNotification($notificationData, $rawBody);

            return response()->json(['message' => $result['message']], $result['status']);
        } catch (\Throwable $th) {
            $code = $th->getCode() ?: 500;

            return response()->json(['message' => $th->getMessage()], is_numeric($code) && $code >= 100 && $code < 600 ? $code : 500);
        }
    }
}
