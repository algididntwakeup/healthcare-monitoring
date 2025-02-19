<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReportGenerator;
use Carbon\Carbon;

class GenerateDailyReport extends Command
{
    protected $signature = 'reports:generate-daily {date? : The date to generate report for}';
    protected $description = 'Generate daily performance report';

    public function handle()
    {
        $date = $this->argument('date') 
            ? Carbon::parse($this->argument('date')) 
            : now()->subDay();
            
        $reportGenerator = new ReportGenerator();
        $report = $reportGenerator->generateDailyReport($date);

        // Save to database or storage
        \Log::info("Daily report generated for {$date->format('Y-m-d')}");
        $this->info("Daily report generated successfully for {$date->format('Y-m-d')}");
        
        return Command::SUCCESS;
    }
}