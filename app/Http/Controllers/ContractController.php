<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\MediaProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ContractController extends Controller
{
    public function adminIndex(): View
    {
        return view('admin.contracts.index', [
            'contracts' => Contract::with('mediaProfile.user')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('admin.contracts.create', [
            'mediaProfiles' => MediaProfile::with('user')->orderBy('nama_media')->get(),
        ]);
    }

    public function store(StoreContractRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('dokumen_kontrak')) {
            $validated['dokumen_kontrak'] = $request->file('dokumen_kontrak')->store('contracts', 'private');
        }

        $contract = Contract::create($validated);
        AuditLog::record($request->user(), 'contract.created', 'Kontrak kerja sama dibuat.', $contract);

        return redirect()->route('admin.contracts.index')->with('success', 'Kontrak berhasil dibuat.');
    }

    public function mediaIndex(Request $request): View
    {
        $contracts = $request->user()->mediaProfile
            ? $request->user()->mediaProfile->contracts()->latest()->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('media.contracts.index', ['contracts' => $contracts]);
    }

    public function download(Request $request, Contract $contract): mixed
    {
        abort_unless(
            $request->user()->isAdminArea()
                || $contract->mediaProfile?->user_id === $request->user()->id,
            403,
        );

        abort_unless($contract->dokumen_kontrak, 404);

        return Storage::disk('private')->download($contract->dokumen_kontrak);
    }
}
