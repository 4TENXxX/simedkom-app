<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsClipping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsClippingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = NewsClipping::with('contract');
        if ($request->user()->role === 'media') {
            $profile = $request->user()->mediaProfile;

            if (! $profile) {
                return response()->json(['success' => true, 'message' => 'Data kliping.', 'data' => [], 'meta' => []]);
            }

            $query->whereBelongsTo($profile, 'mediaProfile');
        }

        return response()->json(['success' => true, 'message' => 'Data kliping.', 'data' => $query->latest()->paginate(15), 'meta' => []]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->role === 'media', 403);
        $data = $request->validate(['contract_id' => ['required', 'exists:contracts,id'], 'type' => ['required', 'in:online,cetak,tv,radio'], 'url' => ['nullable', 'url'], 'title' => ['required', 'string', 'max:255'], 'published_at' => ['required', 'date'], 'notes' => ['nullable', 'string']]);
        $profile = $request->user()->mediaProfile;
        $contract = $profile?->contracts()->whereKey($data['contract_id'])->where('status', 'aktif')->firstOrFail();
        $clipping = NewsClipping::create([...$data, 'media_profile_id' => $profile->id, 'submitted_by' => $request->user()->id, 'duplicate_status' => isset($data['url']) && NewsClipping::where('url', $data['url'])->exists() ? 'duplicate_url' : 'no_duplicate']);

        return response()->json(['success' => true, 'message' => 'Kliping berhasil diajukan.', 'data' => $clipping->load('contract'), 'meta' => []], 201);
    }
}
