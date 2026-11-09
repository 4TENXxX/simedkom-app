<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->status === 'approved_for_payment', 422);
        $validated = $request->validated();

        DB::transaction(function () use ($request, $invoice, $validated): void {
            $payment = Payment::create([...$validated, 'invoice_id' => $invoice->id, 'recorded_by' => $request->user()->id, 'proof_path' => $request->file('proof')->store('payments', 'private')]);
            $invoice->update(['status' => 'paid']);
            AuditLog::record($request->user(), 'payment.recorded', 'Pembayaran invoice dicatat.', $payment, ['invoice_id' => $invoice->id]);
        });

        return back()->with('success', 'Pembayaran berhasil dicatat.');
    }
}
