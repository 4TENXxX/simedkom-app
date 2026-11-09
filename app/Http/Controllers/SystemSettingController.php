<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateKeywordSettingRequest;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function keywords(): View
    {
        return view('admin.settings.keywords', [
            'keywords' => SystemSetting::where('key', 'institution_keywords')->value('value') ?? [],
        ]);
    }

    public function updateKeywords(UpdateKeywordSettingRequest $request): RedirectResponse
    {
        $keywords = array_values(array_unique(array_filter(array_map('trim', $request->validated('keywords')))));
        SystemSetting::updateOrCreate(['key' => 'institution_keywords'], [
            'value' => $keywords,
            'description' => 'Kata kunci instansi untuk validasi berita.',
        ]);
        AuditLog::record($request->user(), 'settings.keywords_updated', 'Kata kunci instansi diperbarui.');

        return back()->with('success', 'Kata kunci berhasil disimpan.');
    }
}
