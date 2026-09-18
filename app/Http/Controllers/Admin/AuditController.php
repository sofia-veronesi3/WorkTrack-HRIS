<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(): View
    {
        $audits = AuditLog::with('user')
            ->latest()
            ->paginate(10);
         

        return view('admin.audits.index', compact('audits'));
    }
}