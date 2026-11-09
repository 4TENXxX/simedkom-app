<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::with('contract');
        if ($request->user()->role === 'media') {
            $profile = $request->user()->mediaProfile;

            if (! $profile) {
                return response()->json(['success' => true, 'message' => 'Data invoice.', 'data' => [], 'meta' => []]);
            }

            $query->whereBelongsTo($profile, 'mediaProfile');
        }

        return response()->json(['success' => true, 'message' => 'Data invoice.', 'data' => $query->latest()->paginate(15), 'meta' => []]);
    }
}
