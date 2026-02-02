<?php

namespace Tests\Feature\Leaderboard;

use App\Models\Leaderboard;
use App\Models\User;
use App\Models\UserInvestment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateLeaderboardPositionsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_calculates_positions_for_active_leaderboards(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $sponsor = User::factory()->create();
        $referral = User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(5),
        ]);
        UserInvestment::factory()->forUser($referral)->active()
            ->createdAt(Carbon::now()->subDays(4))
            ->create();

        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);

        $this->assertDatabaseHas('leaderboard_positions', [
            'leaderboard_id' => $leaderboard->id,
            'user_id' => $sponsor->id,
        ]);
    }

    public function test_command_auto_completes_expired_leaderboards(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(20),
            'end_date' => Carbon::now()->subDays(1),
        ]);

        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);

        $leaderboard->refresh();
        $this->assertEquals('completed', $leaderboard->status);
    }

    public function test_command_skips_inactive_leaderboards(): void
    {
        $leaderboard = Leaderboard::factory()->inactive()->create();

        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('leaderboard_positions', [
            'leaderboard_id' => $leaderboard->id,
        ]);
    }

    public function test_command_skips_completed_leaderboards(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create();

        $initialPositionCount = $leaderboard->positions()->count();

        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);

        $this->assertEquals($initialPositionCount, $leaderboard->positions()->count());
    }

    public function test_command_can_target_specific_leaderboard(): void
    {
        $leaderboard1 = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $leaderboard2 = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $this->artisan('leaderboards:calculate-positions', ['--leaderboard' => $leaderboard1->id])
            ->assertExitCode(0);
    }

    public function test_command_handles_no_active_leaderboards(): void
    {
        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);
    }

    public function test_command_handles_leaderboard_with_no_participants(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);

        $this->assertEquals(0, $leaderboard->positions()->count());
    }

    public function test_command_processes_multiple_leaderboards(): void
    {
        Leaderboard::factory()->count(3)->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $this->artisan('leaderboards:calculate-positions')
            ->assertExitCode(0);
    }

    public function test_command_outputs_processing_info(): void
    {
        Leaderboard::factory()->create([
            'status' => 'active',
            'title' => 'Test Leaderboard',
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $this->artisan('leaderboards:calculate-positions')
            ->expectsOutputToContain('Processing')
            ->assertExitCode(0);
    }
}
