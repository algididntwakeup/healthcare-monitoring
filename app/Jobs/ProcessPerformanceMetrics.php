// app/Jobs/ProcessPerformanceMetrics.php
class ProcessPerformanceMetrics implements ShouldQueue
{
    public $timeout = 300;
    private $date;

    public function __construct($date = null)
    {
        $this->date = $date ?? now()->toDateString();
    }

    public function handle()
    {
        $users = User::whereIn('role', ['doctor', 'nurse'])->get();
        
        foreach ($users as $user) {
            $metrics = $this->calculateMetrics($user);
            $this->storeMetrics($user, $metrics);
        }
    }

    private function calculateMetrics($user)
    {
        $consultations = Consultation::where('user_id', $user->id)
            ->whereDate('created_at', $this->date)
            ->get();

        $waitTimes = $consultations->map(function ($consultation) {
            return Carbon::parse($consultation->start_time)
                ->diffInMinutes(Carbon::parse($consultation->created_at));
        });

        $consultationDurations = $consultations->map(function ($consultation) {
            return Carbon::parse($consultation->end_time)
                ->diffInMinutes(Carbon::parse($consultation->start_time));
        });

        return [
            'patients_served' => $consultations->count(),
            'average_wait_time' => $waitTimes->average() ?? 0,
            'consultation_duration' => $consultationDurations->average() ?? 0,
            'patient_satisfaction' => $this->calculateSatisfactionScore($consultations)
        ];
    }

    private function calculateSatisfactionScore($consultations)
    {
        $ratings = $consultations->whereNotNull('patient_rating')
            ->pluck('patient_rating');
            
        return $ratings->isNotEmpty() ? $ratings->average() : null;
    }

    private function storeMetrics($user, $metrics)
    {
        PerformanceIndicator::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $this->date
            ],
            [
                'patients_served' => $metrics['patients_served'],
                'average_wait_time' => $metrics['average_wait_time'],
                'consultation_duration' => $metrics['consultation_duration'],
                'patient_satisfaction' => $metrics['patient_satisfaction']
            ]
        );
    }
}