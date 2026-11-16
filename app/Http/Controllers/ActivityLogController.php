<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $activityLogs = ActivityLog::query()
            ->with('user')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->module, fn ($q, $module) => $q->where('module', $module))
            ->when($request->date, fn ($q, $date) => $q->whereDate('created_at', $date))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $modules = ActivityLog::query()->distinct()->pluck('module')->filter();

        return view('activity-logs.index', compact('activityLogs', 'modules'));
    }
}
