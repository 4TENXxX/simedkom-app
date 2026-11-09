<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJournalistRequest;
use App\Models\AuditLog;
use App\Models\Journalist;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class JournalistController extends Controller
{
    public function index(Request $request): View
    {
        $query = Journalist::with(['user', 'mediaProfile']);

        if ($request->user()->role === 'media') {
            $profile = $request->user()->mediaProfile;

            if (! $profile) {
                return view('admin.journalists.index', ['journalists' => Journalist::whereKey(0)->paginate(15)]);
            }

            $query->whereBelongsTo($profile, 'mediaProfile');
        }

        return view('admin.journalists.index', ['journalists' => $query->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.journalists.create');
    }

    public function store(StoreJournalistRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $profile = $request->user()->mediaProfile;

        if (! $profile) {
            return back()
                ->withErrors(['media_profile_id' => 'Lengkapi profil perusahaan media sebelum menambahkan jurnalis.'])
                ->withInput();
        }

        DB::transaction(function () use ($request, $validated, $profile): void {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'jurnalis',
            ]);
            $journalist = $profile->journalists()->create([
                'user_id' => $user->id,
                'phone' => $validated['phone'] ?? null,
                'position' => $validated['position'] ?? null,
                'foto_wartawan_path' => $request->file('foto_wartawan')->store('journalists/'.$profile->id, 'public'),
                'sertifikat_kompetensi_path' => $request->file('sertifikat_kompetensi')->store('journalists/'.$profile->id, 'public'),
            ]);
            AuditLog::record($request->user(), 'journalist.created', 'Jurnalis mitra dibuat.', $journalist);
        });

        return redirect()->route('media.journalists.index')
            ->with('success', 'Jurnalis berhasil ditambahkan.');
    }
}
