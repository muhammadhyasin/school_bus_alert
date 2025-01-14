<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use App\Models\FeeSchedule;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }
    public function processPayment(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:fee_schedules,id',
            'payment_method' => 'required|in:upi,card,netbanking',
            'amount' => 'required|numeric'
        ]);

        $schedule = FeeSchedule::findOrFail($request->schedule_id);
        $student = auth()->user()->students->first();

        // Here you would integrate with your payment gateway
        // For this example, we'll assume payment is successful

        $payment = FeePayment::create([
            'student_id' => $student->id,
            'fee_schedule_id' => $schedule->id,
            'amount_paid' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => 'TXN' . time(),
            'status' => 'completed',
            'paid_at' => now()
        ]);

        // Send payment confirmation notification
        $this->notificationController->notifyPaymentSuccess($payment);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully'
        ]);
    }

    public function downloadReceipt(FeePayment $payment)
    {
        // Generate PDF receipt
        $pdf = PDF::loadView('receipts.payment', compact('payment'));
        
        return $pdf->download("receipt-{$payment->id}.pdf");
    }
}
