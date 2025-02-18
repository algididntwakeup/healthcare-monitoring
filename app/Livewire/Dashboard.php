<?php

namespace App\Livewire;

use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $data = [
            'daily_patients' => Consultation::whereDate('created_at', today())->count(),
            'performance' => PerformanceIndicator::where('user_id', auth()->id())
                ->latest()
                ->take(7)
                ->get(),
            'waiting_patients' => Patient::whereHas('consultations', function ($query) {
                $query->whereNull('end_time');
            })->count()
        ];
        
        return view('livewire.dashboard', $data);
    }
}
