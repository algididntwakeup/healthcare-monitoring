<?php

namespace App\Services;

use App\Models\PerformanceIndicator;
use Carbon\Carbon;

// app/Services/ReportGenerator.php
class ReportGenerator
{
    public function generateDailyReport($date)
    {
        return PerformanceIndicator::whereDate('date', $date)
            ->with('user')
            ->get()
            ->groupBy('user.role')
            ->map(function ($indicators) {
                return [
                    'total_patients' => $indicators->sum('patients_served'),
                    'avg_wait_time' => $indicators->avg('average_wait_time'),
                    'avg_satisfaction' => $indicators->avg('patient_satisfaction'),
                    'details' => $indicators->map(function ($indicator) {
                        return [
                            'name' => $indicator->user->name,
                            'patients' => $indicator->patients_served,
                            'wait_time' => $indicator->average_wait_time,
                            'satisfaction' => $indicator->patient_satisfaction
                        ];
                    })
                ];
            });
    }
    
    public function generateWeeklyReport($startDate)
    {
        $endDate = Carbon::parse($startDate)->addDays(6);
        
        return PerformanceIndicator::whereBetween('date', [$startDate, $endDate])
            ->with('user')
            ->get()
            ->groupBy([
                function ($item) {
                    return $item->user->role;
                },
                function ($item) {
                    return $item->date->format('Y-m-d');
                }
            ])
            ->map(function ($roleData) {
                return [
                    'daily_stats' => $roleData,
                    'weekly_summary' => [
                        'total_patients' => $roleData->flatten(1)->sum('patients_served'),
                        'avg_wait_time' => $roleData->flatten(1)->avg('average_wait_time'),
                        'performance_trend' => $this->calculateTrend($roleData->flatten(1))
                    ]
                ];
            });
    }
    
    public function generateMonthlyReport($month, $year)
    {
        $startDate = Carbon::createFromDate($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        $data = PerformanceIndicator::whereBetween('date', [$startDate, $endDate])
            ->with('user')
            ->get();
            
        return [
            'summary' => [
                'total_patients' => $data->sum('patients_served'),
                'avg_wait_time' => $data->avg('average_wait_time'),
                'avg_satisfaction' => $data->avg('patient_satisfaction')
            ],
            'by_role' => $data->groupBy('user.role')
                ->map(function ($indicators) {
                    return [
                        'total_patients' => $indicators->sum('patients_served'),
                        'avg_metrics' => [
                            'wait_time' => $indicators->avg('average_wait_time'),
                            'satisfaction' => $indicators->avg('patient_satisfaction'),
                            'consultation_duration' => $indicators->avg('consultation_duration')
                        ],
                        'top_performers' => $this->getTopPerformers($indicators)
                    ];
                }),
            'charts_data' => $this->generateChartsData($data)
        ];
    }

    private function calculateTrend($data)
    {
        $values = $data->pluck('patients_served')->toArray();
        $count = count($values);
        
        if ($count < 2) return 0;
        
        $x = range(1, $count);
        $x_mean = array_sum($x) / $count;
        $y_mean = array_sum($values) / $count;
        
        $numerator = 0;
        $denominator = 0;
        
        for ($i = 0; $i < $count; $i++) {
            $numerator += ($x[$i] - $x_mean) * ($values[$i] - $y_mean);
            $denominator += pow($x[$i] - $x_mean, 2);
        }
        
        return $denominator != 0 ? $numerator / $denominator : 0;
    }

    private function getTopPerformers($indicators)
    {
        return $indicators->sortByDesc('patients_served')
            ->take(5)
            ->map(function ($indicator) {
                return [
                    'name' => $indicator->user->name,
                    'patients_served' => $indicator->patients_served,
                    'satisfaction_score' => $indicator->patient_satisfaction
                ];
            });
    }

    private function generateChartsData($data)
    {
        return [
            'daily_patients' => $data->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            })->map->sum('patients_served'),
            
            'wait_times' => $data->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            })->map->avg('average_wait_time'),
            
            'satisfaction_trend' => $data->groupBy(function ($item) {
                return $item->date->format('Y-m-d');
            })->map->avg('patient_satisfaction')
        ];
    }
}
