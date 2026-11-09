<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePressReleaseRequest;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\PressRelease;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PressReleaseController extends Controller
{
    public function adminIndex(): View
    {
        return view('admin.press-releases.index', ['pressReleases' => PressRelease::latest()->paginate(10)]);
    }

    public function create(): View
    {
        return view('admin.press-releases.create');
    }

    public function store(StorePressReleaseRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('press-releases', 'private');
        }

        unset($validated['attachment']);
        $validated['author_id'] = $request->user()->id;
        $pressRelease = PressRelease::create($validated);

        if ($pressRelease->status === 'dipublikasikan') {
            $this->notifyMediaPartners($pressRelease);
        }

        AuditLog::record($request->user(), 'press_release.created', 'Siaran pers dibuat.', $pressRelease, ['status' => $pressRelease->status]);

        return redirect()->route('admin.press-releases.index')->with('success', 'Siaran pers berhasil disimpan.');
    }

    public function publish(Request $request, PressRelease $pressRelease): RedirectResponse
    {
        DB::transaction(function () use ($request, $pressRelease): void {
            $pressRelease->update(['status' => 'dipublikasikan', 'published_at' => now()]);
            $this->notifyMediaPartners($pressRelease);
            AuditLog::record($request->user(), 'press_release.published', 'Siaran pers dipublikasikan.', $pressRelease);
        });

        return back()->with('success', 'Siaran pers dipublikasikan dan notifikasi dikirim.');
    }

    public function mediaIndex(Request $request): View
    {
        return view('media.press-releases.index', [
            'pressReleases' => PressRelease::where('status', 'dipublikasikan')->latest('published_at')->paginate(10),
            'notifications' => AppNotification::where('user_id', $request->user()->id)->latest()->limit(5)->get(),
        ]);
    }

    private function notifyMediaPartners(PressRelease $pressRelease): void
    {
        User::whereIn('role', ['media', 'jurnalis'])->pluck('id')->each(function (int $userId) use ($pressRelease): void {
            AppNotification::firstOrCreate([
                'user_id' => $userId,
                'press_release_id' => $pressRelease->id,
            ], [
                'title' => 'Siaran pers baru',
                'message' => $pressRelease->title,
            ]);
        });
    }
}
