<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PerformanceIndicator;
use Illuminate\Http\Request;

class PerformanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $date = $request->input('date', now()->toDateString());
        
        $metrics = PerformanceIndicator::with('user')
            ->when($request->filled('user_id'), function($query) use ($request) {
                return $query->where('user_id', $request->user_id);
            })
            ->when($request->filled('role'), function($query) use ($request) {
                return $query->whereHas('user', function($q) use ($request) {
                    $q->where('role', $request->role);
                });
            })
            ->whereDate('date', $date)
            ->get();
            
        return response()->json([
            'data' => $metrics,
            'meta' => [
                'date' => $date,
                'total_patients' => $metrics->sum('patients_served'),
                'avg_wait_time' => $metrics->avg('average_wait_time')
            ]
        ]);
    }
}