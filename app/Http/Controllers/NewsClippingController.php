<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsClippingRequest;
use App\Http\Requests\UpdateNewsClippingRequest;
use App\Models\AuditLog;
use App\Models\NewsClipping;
use App\Services\ClippingAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewsClippingController extends Controller
{
    public function index(Request $request): View
    {
        $clippings = $request->user()->mediaProfile
            ? $request->user()->mediaProfile->newsClippings()->with('contract')->latest()->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('media.clippings.index', ['clippings' => $clippings]);
    }

    public function create(Request $request): View
    {
        $contracts = $request->user()->mediaProfile
            ? $request->user()->mediaProfile->contracts()->where('status', 'aktif')->latest()->get()
            : collect();

        return view('media.clippings.create', [
            'contracts' => $contracts,
        ]);
    }

    public function store(StoreNewsClippingRequest $request, ClippingAnalysisService $analysisService): RedirectResponse
    {
        $validated = $request->validated();
        $profile = $request->user()->mediaProfile;
        $contract = $profile?->contracts()->whereKey($validated['contract_id'])->where('status', 'aktif')->firstOrFail();
        $isDuplicate = $validated['url'] && NewsClipping::where('url', $validated['url'])->exists();

        $clipping = NewsClipping::create([
            ...$validated,
            'media_profile_id' => $profile->id,
            'submitted_by' => $request->user()->id,
            'status' => $isDuplicate ? 'diajukan' : 'diajukan',
            'duplicate_status' => $isDuplicate ? 'duplicate_url' : 'no_duplicate',
        ]);
        foreach ($request->file('files', []) as $file) {
            $clipping->files()->create([
                'file_path' => $file->store('clippings/'.$clipping->id, 'private'),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
        $analysisService->analyze($clipping);

        return redirect()->route('media.clippings.index')->with('success', 'Bukti tayang berhasil diajukan untuk validasi.');
    }

    public function adminIndex(): View
    {
        return view('admin.clippings.index', [
            'clippings' => NewsClipping::with(['mediaProfile', 'contract'])->latest()->paginate(10),
        ]);
    }

    public function update(UpdateNewsClippingRequest $request, NewsClipping $clipping): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($request, $clipping, $validated): void {
            if ($validated['status'] === 'valid' && $clipping->status !== 'valid') {
                $validCount = $clipping->contract->newsClippings()->where('status', 'valid')->count();

                if ($validCount >= $clipping->contract->kuota_berita) {
                    throw ValidationException::withMessages(['status' => 'Kuota kontrak sudah penuh.']);
                }
            }

            $clipping->update([
                ...$validated,
                'validated_by' => $request->user()->id,
                'validated_at' => now(),
            ]);
            AuditLog::record($request->user(), 'clipping.reviewed', 'Status kliping ditinjau.', $clipping, ['status' => $clipping->status]);
        });

        return redirect()->route('admin.clippings.index')->with('success', 'Status kliping berhasil diperbarui.');
    }
}
