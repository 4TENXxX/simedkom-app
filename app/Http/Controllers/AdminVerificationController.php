<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSubmissionStatusRequest;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\MediaProfile;
use App\Models\NewsClipping;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminVerificationController extends Controller
{
    public function index(): View
    {
        $submissions = Submission::with(['user.mediaProfile', 'documents'])
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.dashboard', [
            'submissions' => $submissions,
            'stats' => [
                'total_media' => MediaProfile::count(),
                'legal_pending' => Submission::where('status', 'pending')->count(),
                'active_contracts' => Contract::where('status', 'aktif')->count(),
                'clippings_total' => NewsClipping::count(),
                'clippings_pending' => NewsClipping::whereIn('status', ['diajukan', 'diproses'])->count(),
                'clippings_valid' => NewsClipping::where('status', 'valid')->count(),
                'clippings_rejected' => NewsClipping::where('status', 'ditolak')->count(),
                'invoices_pending' => Invoice::where('status', 'pending_finance_review')->count(),
            ],
        ]);
    }

    public function show(Submission $submission): View
    {
        $submission->load([
            'user.mediaProfile.journalists.user',
            'documents',
        ]);

        return view('admin.submissions.show', compact('submission'));
    }

    public function update(UpdateSubmissionStatusRequest $request, Submission $submission): RedirectResponse
    {
        if ($submission->status === 'disetujui') {
            return redirect()
                ->route('admin.submissions.show', $submission)
                ->with('error', 'Pengajuan yang sudah disetujui tidak dapat diubah lagi.');
        }

        $validated = $request->validated();

        DB::transaction(function () use ($request, $submission, $validated): void {
            $oldStatus = $submission->status;
            $submission->update($validated);

            if ($oldStatus !== $submission->status) {
                $submission->statusHistories()->create([
                    'user_id' => $request->user()->id,
                    'old_status' => $oldStatus,
                    'new_status' => $submission->status,
                    'catatan_admin' => $submission->catatan_admin,
                ]);

                AuditLog::record($request->user(), 'submission.status_changed', 'Status pengajuan legalitas diubah.', $submission, [
                    'old_status' => $oldStatus,
                    'new_status' => $submission->status,
                ]);
            }
        });

        return redirect()->route('admin.dashboard')->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
