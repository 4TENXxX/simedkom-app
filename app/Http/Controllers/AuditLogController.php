<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.audit-logs.index', [
            'logs' => AuditLog::with('user')->latest()->paginate(25)->withQueryString(),
        ]);
    }
}
