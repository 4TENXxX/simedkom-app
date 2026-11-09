<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\AuditLog;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function mediaIndex(Request $request): View
    {
        $invoices = $request->user()->mediaProfile
            ? $request->user()->mediaProfile->invoices()->latest()->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('media.invoices.index', ['invoices' => $invoices]);
    }

    public function create(Request $request): View
    {
        $contracts = $request->user()->mediaProfile
            ? $request->user()->mediaProfile->contracts()->where('status', 'aktif')->get()
            : collect();

        return view('media.invoices.create', ['contracts' => $contracts]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profile = $request->user()->mediaProfile;
        $contract = $profile?->contracts()->whereKey($validated['contract_id'])->firstOrFail();
        $validated['media_profile_id'] = $profile->id;
        $validated['submitted_by'] = $request->user()->id;
        $validated['invoice_path'] = $request->file('invoice')->store('invoices', 'private');
        $validated['tax_document_path'] = $request->hasFile('tax_document') ? $request->file('tax_document')->store('invoices/tax', 'private') : null;
        unset($validated['invoice'], $validated['tax_document']);

        if ((float) $validated['subtotal'] !== round($contract->newsClippings()->where('status', 'valid')->count() * (float) $contract->harga_per_publikasi, 2)) {
            return back()->withErrors(['subtotal' => 'Nilai invoice tidak sesuai dengan publikasi valid pada kontrak.'])->withInput();
        }

        $invoice = Invoice::create($validated);
        AuditLog::record($request->user(), 'invoice.created', 'Invoice e-Billing diajukan.', $invoice, ['total' => $invoice->total]);

        return redirect()->route('media.invoices.index')->with('success', 'E-Billing berhasil diajukan.');
    }

    public function financeIndex(): View
    {
        return view('admin.invoices.index', ['invoices' => Invoice::with(['mediaProfile', 'contract'])->latest()->paginate(10)]);
    }

    public function review(Request $request, Invoice $invoice): RedirectResponse
    {
        $request->validate(['status' => ['required', 'in:approved_for_payment,rejected,revision_required'], 'review_notes' => ['nullable', 'string', 'max:2000']]);
        $invoice->update([...$request->only('status', 'review_notes'), 'reviewed_by' => $request->user()->id]);
        AuditLog::record($request->user(), 'invoice.reviewed', 'Status invoice ditinjau oleh petugas keuangan.', $invoice, ['status' => $invoice->status]);

        return back()->with('success', 'Status invoice berhasil diperbarui.');
    }

    public function download(Request $request, Invoice $invoice): mixed
    {
        abort_unless($request->user()->isAdminArea() || $invoice->mediaProfile?->user_id === $request->user()->id, 403);

        return Storage::disk('private')->download($invoice->invoice_path);
    }
}
