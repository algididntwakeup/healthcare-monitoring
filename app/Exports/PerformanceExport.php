<?php
// app/Exports/PerformanceExport.php
class PerformanceExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return PerformanceIndicator::whereBetween('date', [$this->startDate, $this->endDate])
            ->with('user')
            ->get()
            ->map(function ($indicator) {
                return [
                    'Date' => $indicator->date,
                    'Staff Name' => $indicator->user->name,
                    'Role' => $indicator->user->role,
                    'Patients Served' => $indicator->patients_served,
                    'Avg Wait Time' => $indicator->average_wait_time,
                    'Satisfaction Score' => $indicator->patient_satisfaction
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Staff Name',
            'Role',
            'Patients Served',
            'Avg Wait Time (min)',
            'Satisfaction Score'
        ];
    }
}