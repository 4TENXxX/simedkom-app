<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaSubmissionRequest;
use App\Http\Requests\UpdateMediaSubmissionRequest;
use App\Models\Submission;
use App\Models\SubmissionStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaSubmissionController extends Controller
{
    public function dashboard(): View
    {
        $user = request()->user();
        $profile = $user->mediaProfile;

        return view('media.dashboard', [
            'submission' => $user->submissions()->with('documents')->latest()->first(),
            'stats' => [
                'active_contracts' => $profile?->contracts()->where('status', 'aktif')->count() ?? 0,
                'quota_total' => $profile?->contracts()->where('status', 'aktif')->sum('kuota_berita') ?? 0,
                'valid_clippings' => $profile?->newsClippings()->where('status', 'valid')->count() ?? 0,
                'pending_clippings' => $profile?->newsClippings()->whereIn('status', ['diajukan', 'diproses'])->count() ?? 0,
                'rejected_clippings' => $profile?->newsClippings()->where('status', 'ditolak')->count() ?? 0,
                'invoice_total' => $profile?->invoices()->sum('total') ?? 0,
            ],
        ]);
    }

    public function create(): View|RedirectResponse
    {
        $submission = request()->user()->submissions()->with('documents')->first();

        if ($submission && $submission->status !== 'perbaikan') {
            return redirect()->route('media.dashboard')->with('error', 'Pengajuan tidak dapat dibuat ulang. Tunggu proses verifikasi atau perbaiki pengajuan yang diminta admin.');
        }

        return view('media.submissions.create', compact('submission'));
    }

    public function store(StoreMediaSubmissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $created = DB::transaction(function () use ($request, $validated): bool {
            $user = $request->user()->newQuery()
                ->lockForUpdate()
                ->findOrFail($request->user()->id);

            if ($user->submissions()->exists()) {
                return false;
            }

            $profile = $user->mediaProfile()->updateOrCreate([], [
                'nama_media' => $validated['nama_media'],
                'jenis_media' => $validated['jenis_media'],
                'alamat' => $validated['alamat'],
                'no_whatsapp' => $validated['no_whatsapp'],
                'link_website' => $validated['link_website'] ?? null,
            ]);

            $submission = $user->submissions()->create([
                'tahun_anggaran' => $validated['tahun_anggaran'],
                'status' => 'pending',
            ]);

            SubmissionStatusHistory::create([
                'submission_id' => $submission->id,
                'user_id' => $request->user()->id,
                'new_status' => $submission->status,
            ]);

            foreach ($validated['documents'] as $jenis => $file) {
                $submission->documents()->create([
                    'jenis_dokumen' => $jenis,
                    'file_path' => $file->store('submissions/'.$profile->id, 'public'),
                ]);
            }

            return true;
        });

        if (! $created) {
            return redirect()->route('media.dashboard')->with('error', 'Anda sudah memiliki pengajuan. Pengajuan baru tidak dapat dibuat.');
        }

        return redirect()->route('media.dashboard')->with('success', 'Pengajuan berhasil dikirim untuk diverifikasi.');
    }

    public function edit(Submission $submission): View
    {
        abort_unless(
            $submission->user_id === request()->user()->id && $submission->status === 'perbaikan',
            403
        );

        $submission->load('documents');

        return view('media.submissions.create', compact('submission'));
    }

    public function update(UpdateMediaSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $submission, $validated): void {
            $profile = $request->user()->mediaProfile()->updateOrCreate([], [
                'nama_media' => $validated['nama_media'],
                'jenis_media' => $validated['jenis_media'],
                'alamat' => $validated['alamat'],
                'no_whatsapp' => $validated['no_whatsapp'],
                'link_website' => $validated['link_website'] ?? null,
            ]);

            $oldStatus = $submission->status;
            $submission->update([
                'tahun_anggaran' => $validated['tahun_anggaran'],
                'status' => 'pending',
                'catatan_admin' => null,
            ]);

            foreach ($validated['documents'] ?? [] as $jenis => $file) {
                if (! $file) {
                    continue;
                }

                $document = $submission->documents()->where('jenis_dokumen', $jenis)->first();
                $oldPath = $document?->file_path;
                $newPath = $file->store('submissions/'.$profile->id, 'public');

                if ($document) {
                    $document->update(['file_path' => $newPath]);
                } else {
                    $submission->documents()->create([
                        'jenis_dokumen' => $jenis,
                        'file_path' => $newPath,
                    ]);
                }

                if ($oldPath) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            SubmissionStatusHistory::create([
                'submission_id' => $submission->id,
                'user_id' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $submission->status,
                'catatan_admin' => null,
            ]);
        });

        return redirect()->route('media.dashboard')->with('success', 'Perbaikan berhasil dikirim ulang untuk diverifikasi.');
    }

    public function download(Submission $submission, int $document): mixed
    {
        abort_unless($submission->user_id === request()->user()->id, 403);

        $file = $submission->documents()->findOrFail($document);

        return Storage::disk('public')->download($file->file_path, $file->jenis_dokumen.'.'.pathinfo($file->file_path, PATHINFO_EXTENSION));
    }
}
