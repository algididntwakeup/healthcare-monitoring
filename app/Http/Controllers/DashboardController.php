<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $cacheKey = 'dashboard_' . auth()->id() . '_' . now()->format('Y-m-d');
        
        return view('dashboard', [
            'metrics' => Cache::remember($cacheKey, now()->addMinutes(15), function () {
                return $this->getDashboardMetrics();
            })
        ]);
    }
    private function getDashboardMetrics()
{
    return [
        'daily_stats' => PerformanceIndicator::whereDate('date', today())
            ->where('user_id', auth()->id())
            ->first(),
        'weekly_trend' => PerformanceIndicator::where('user_id', auth()->id())
            ->whereBetween('date', [now()->subDays(7), now()])
            ->get(),
        'waiting_patients' => Patient::whereHas('consultations', function ($query) {
            $query->whereNull('end_time');
        })->count()
    ];
}
}
