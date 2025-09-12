<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LogViewer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:view {--lines=50 : Number of lines to show} {--filter= : Filter logs by keyword} {--follow : Follow log in real-time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'View Laravel logs with filtering and real-time following';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logFile = storage_path('logs/laravel.log');
        $lines = (int) $this->option('lines');
        $filter = $this->option('filter');
        $follow = $this->option('follow');

        // Check if log file exists
        if (!File::exists($logFile)) {
            $this->error('Log file not found: ' . $logFile);
            return 1;
        }

        if ($follow) {
            $this->info('Following logs in real-time... (Press Ctrl+C to stop)');
            if ($filter) {
                $this->info("Filtering for: {$filter}");
            }
            $this->followLog($logFile, $filter);
        } else {
            $this->showLog($logFile, $lines, $filter);
        }

        return 0;
    }

    /**
     * Show log content
     */
    private function showLog($logFile, $lines, $filter = null)
    {
        $content = File::get($logFile);
        $logLines = explode("\n", $content);
        
        // Get last N lines
        $lastLines = array_slice($logLines, -$lines);
        
        // Filter if needed
        if ($filter) {
            $lastLines = array_filter($lastLines, function($line) use ($filter) {
                return stripos($line, $filter) !== false;
            });
        }

        if (empty($lastLines)) {
            $this->warn('No log entries found' . ($filter ? " matching filter: {$filter}" : ''));
            return;
        }

        $this->info('Showing last ' . count($lastLines) . ' log entries:');
        $this->line('');
        
        foreach ($lastLines as $line) {
            if (trim($line)) {
                $this->line($line);
            }
        }
    }

    /**
     * Follow log in real-time
     */
    private function followLog($logFile, $filter = null)
    {
        $handle = fopen($logFile, 'r');
        if (!$handle) {
            $this->error('Cannot open log file for reading');
            return;
        }

        // Go to end of file
        fseek($handle, 0, SEEK_END);

        while (true) {
            $line = fgets($handle);
            if ($line !== false) {
                if (!$filter || stripos($line, $filter) !== false) {
                    $this->line(trim($line));
                }
            }
            usleep(100000); // 100ms delay
        }

        fclose($handle);
    }
}