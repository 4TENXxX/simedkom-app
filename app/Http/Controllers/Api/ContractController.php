<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contract::with('mediaProfile');
        if ($request->user()->role === 'media') {
            $profile = $request->user()->mediaProfile;

            if (! $profile) {
                return response()->json(['success' => true, 'message' => 'Data kontrak.', 'data' => [], 'meta' => []]);
            }

            $query->whereBelongsTo($profile, 'mediaProfile');
        }

        return response()->json(['success' => true, 'message' => 'Data kontrak.', 'data' => $query->latest()->paginate(15), 'meta' => []]);
    }
}
