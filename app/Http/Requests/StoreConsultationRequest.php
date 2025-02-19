<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'consultation_type' => 'required|in:regular,emergency,follow-up',
            'notes' => 'nullable|string|max:1000'
        ];
    }
}

// app/Http/Controllers/ConsultationController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Models\Consultation;
use App\Jobs\ProcessPerformanceMetrics;
use Illuminate\Http\RedirectResponse;

class ConsultationController extends Controller
{
    public function store(StoreConsultationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $consultation = Consultation::create($validated);

        // Dispatch the job to process performance metrics
        ProcessPerformanceMetrics::dispatch();

        return redirect()->route('consultations.index')
            ->with('success', 'Consultation recorded successfully');
    }
}
