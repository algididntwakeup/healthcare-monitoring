namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\ProcessPerformanceMetrics;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        // Daily performance metrics processing
        $schedule->job(new ProcessPerformanceMetrics)->dailyAt('23:00');
        
        // Generate daily reports
        $schedule->command('reports:generate-daily')->dailyAt('23:59');
        
        // Weekly reports
        $schedule->command('reports:generate-weekly')->weekly()->mondays()->at('00:01');
        
        // Monthly reports
        $schedule->command('reports:generate-monthly')->monthlyOn(1, '00:01');
        
        // Database backup
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('02:00');
        
        // Cache cleanup
        $schedule->command('cache:clear')->daily()->at('03:00');
        
        // Queue monitoring
        $schedule->command('queue:monitor')
            ->everyFiveMinutes()
            ->whenNotRunning();

            $schedule->job(new ProcessPerformanceMetrics)->daily();
        $schedule->command('reports:generate-daily')->dailyAt('23:59');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}