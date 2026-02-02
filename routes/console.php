<?php
// routes/console.php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule investment returns processing every minute
Schedule::command('investments:process-returns')
    ->everyMinute()
    ->withoutOverlapping() // Prevent multiple instances running simultaneously
    ->runInBackground()    // Run in background to avoid blocking
    ->onOneServer()        // Only run on one server if you have multiple servers
    ->appendOutputTo(storage_path('logs/investment-returns.log')) // Log output
    ->onSuccess(function () {
        // Optional: Log successful completion
        \Illuminate\Support\Facades\Log::info('Investment returns processing completed successfully', [
            'timestamp' => now()->toDateTimeString()
        ]);
    })
    ->onFailure(function () {
        // Optional: Log failures
        \Illuminate\Support\Facades\Log::error('Investment returns processing failed', [
            'timestamp' => now()->toDateTimeString()
        ]);
    });

// Optional: Clean up old logs weekly on Sunday at 2 AM
Schedule::call(function () {
    $logFile = storage_path('logs/investment-returns.log');
    if (file_exists($logFile) && filesize($logFile) > 10 * 1024 * 1024) { // 10MB
        // Archive old log and start fresh
        $archiveName = 'investment-returns-' . now()->format('Y-m-d') . '.log';
        rename($logFile, storage_path('logs/' . $archiveName));
        touch($logFile);
        
        \Illuminate\Support\Facades\Log::info('Investment returns log archived', [
            'archived_file' => $archiveName,
            'timestamp' => now()->toDateTimeString()
        ]);
    }
})->weekly()->sundays()->at('02:00')->name('cleanup-investment-logs');

// Schedule leaderboard position calculations every 15 minutes
Schedule::command('leaderboards:calculate-positions')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/leaderboard-positions.log'))
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('Leaderboard positions calculated successfully', [
            'timestamp' => now()->toDateTimeString()
        ]);
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('Leaderboard position calculation failed', [
            'timestamp' => now()->toDateTimeString()
        ]);
    });

// Auto-complete ended leaderboards and mark for prize distribution (runs hourly)
Schedule::call(function () {
    $endedLeaderboards = \App\Models\Leaderboard::where('status', 'active')
        ->where('end_date', '<', now())
        ->get();

    $leaderboardService = app(\App\Services\LeaderboardService::class);

    foreach ($endedLeaderboards as $leaderboard) {
        try {
            // Calculate final positions before completing
            $leaderboardService->calculatePositions($leaderboard);
            
            \Illuminate\Support\Facades\Log::info('Leaderboard final positions calculated', [
                'leaderboard_id' => $leaderboard->id,
                'title' => $leaderboard->title,
                'participants' => $leaderboard->getParticipantsCount(),
                'timestamp' => now()->toDateTimeString()
            ]);

            // Only mark as completed after successful calculation
            $leaderboard->update(['status' => 'completed']);
            
            \Illuminate\Support\Facades\Log::info('Leaderboard auto-completed', [
                'leaderboard_id' => $leaderboard->id,
                'title' => $leaderboard->title,
                'end_date' => $leaderboard->end_date->toDateTimeString(),
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            // Do NOT mark as completed if calculation fails - leave for retry
            \Illuminate\Support\Facades\Log::error('Failed to auto-complete leaderboard - will retry next hour', [
                'leaderboard_id' => $leaderboard->id,
                'title' => $leaderboard->title,
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }
})->hourly()->name('auto-complete-leaderboards');