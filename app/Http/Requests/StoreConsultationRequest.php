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
