<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceIndicator extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'patients_served',
        'average_wait_time',
        'consultation_duration',
        'patient_satisfaction'
    ];

    protected $casts = [
        'date' => 'date',
        'patient_satisfaction' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}