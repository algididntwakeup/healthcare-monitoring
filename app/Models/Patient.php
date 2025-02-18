<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'birth_date',
        'medical_record_number'
    ];

    protected $casts = [
        'birth_date' => 'date'
    ];

    public function consultations()
    {
        return $this->hasMany(Consultation::class);
    }
}
