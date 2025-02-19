<?php
// tests/Feature/PerformanceMetricsTest.php
class PerformanceMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_performance_metrics_are_calculated_correctly()
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $consultations = Consultation::factory()
            ->count(5)
            ->create([
                'user_id' => $user->id,
                'created_at' => now(),
                'start_time' => now()->addMinutes(15),
                'end_time' => now()->addMinutes(45)
            ]);

        ProcessPerformanceMetrics::dispatch();

        $this->assertDatabaseHas('performance_indicators', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'patients_served' => 5,
            'average_wait_time' => 15,
            'consultation_duration' => 30
        ]);
    }
}