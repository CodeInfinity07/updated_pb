<?php

namespace Tests\Unit\Models;

use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_can_be_created(): void
    {
        $leaderboard = Leaderboard::factory()->create();

        $this->assertDatabaseHas('leaderboards', [
            'id' => $leaderboard->id,
            'title' => $leaderboard->title,
        ]);
    }

    public function test_leaderboard_has_positions_relationship(): void
    {
        $leaderboard = Leaderboard::factory()->create();
        $user = User::factory()->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($user)
            ->create();

        $this->assertCount(1, $leaderboard->positions);
        $this->assertInstanceOf(LeaderboardPosition::class, $leaderboard->positions->first());
    }

    public function test_leaderboard_has_creator_relationship(): void
    {
        $admin = User::factory()->admin()->create();
        $leaderboard = Leaderboard::factory()->createdBy($admin)->create();

        $this->assertInstanceOf(User::class, $leaderboard->creator);
        $this->assertEquals($admin->id, $leaderboard->creator->id);
    }

    public function test_is_active_returns_true_for_active_leaderboard_within_date_range(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $this->assertTrue($leaderboard->isActive());
    }

    public function test_is_active_returns_false_for_inactive_status(): void
    {
        $leaderboard = Leaderboard::factory()->inactive()->create([
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $this->assertFalse($leaderboard->isActive());
    }

    public function test_is_active_returns_false_when_outside_date_range(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $this->assertFalse($leaderboard->isActive());
    }

    public function test_is_completed_returns_true_for_completed_status(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create();

        $this->assertTrue($leaderboard->isCompleted());
    }

    public function test_is_completed_returns_true_when_end_date_passed(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'end_date' => Carbon::now()->subDays(1),
        ]);

        $this->assertTrue($leaderboard->isCompleted());
    }

    public function test_is_upcoming_returns_true_for_future_start_date(): void
    {
        $leaderboard = Leaderboard::factory()->upcoming()->create();

        $this->assertTrue($leaderboard->isUpcoming());
    }

    public function test_is_competitive_returns_true_for_competitive_type(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create();

        $this->assertTrue($leaderboard->isCompetitive());
        $this->assertFalse($leaderboard->isTarget());
    }

    public function test_is_target_returns_true_for_target_type(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create();

        $this->assertTrue($leaderboard->isTarget());
        $this->assertFalse($leaderboard->isCompetitive());
    }

    public function test_can_distribute_prizes_returns_true_for_completed_undistributed(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'prizes_distributed' => false,
        ]);

        $user = User::factory()->create();
        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($user)
            ->withPrize(100)
            ->create();

        $this->assertTrue($leaderboard->canDistributePrizes());
    }

    public function test_can_distribute_prizes_returns_false_when_already_distributed(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->prizesDistributed()->create();

        $this->assertFalse($leaderboard->canDistributePrizes());
    }

    public function test_can_distribute_prizes_returns_false_for_active_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'prizes_distributed' => false,
        ]);

        $this->assertFalse($leaderboard->canDistributePrizes());
    }

    public function test_has_ended_returns_true_when_end_date_passed(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'end_date' => Carbon::now()->subDays(1),
        ]);

        $this->assertTrue($leaderboard->hasEnded());
    }

    public function test_has_ended_returns_false_when_end_date_in_future(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $this->assertFalse($leaderboard->hasEnded());
    }

    public function test_days_remaining_calculation(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $this->assertGreaterThanOrEqual(4, $leaderboard->days_remaining);
        $this->assertLessThanOrEqual(6, $leaderboard->days_remaining);
    }

    public function test_days_remaining_returns_zero_when_ended(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'end_date' => Carbon::now()->subDays(1),
        ]);

        $this->assertEquals(0, $leaderboard->days_remaining);
    }

    public function test_get_progress_returns_zero_for_upcoming(): void
    {
        $leaderboard = Leaderboard::factory()->upcoming()->create();

        $this->assertEquals(0, $leaderboard->getProgress());
    }

    public function test_get_progress_returns_100_when_ended(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->subDays(1),
        ]);

        $this->assertEquals(100, $leaderboard->getProgress());
    }

    public function test_total_prize_amount_for_competitive(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'prize_structure' => [
                ['position' => 1, 'amount' => 500],
                ['position' => 2, 'amount' => 300],
                ['position' => 3, 'amount' => 200],
            ],
        ]);

        $this->assertEquals(1000, $leaderboard->total_prize_amount);
    }

    public function test_total_prize_amount_for_target_with_single_tier(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create([
            'target_referrals' => 10,
            'target_prize_amount' => 100,
        ]);

        $this->assertEquals(100, $leaderboard->total_prize_amount);
    }

    public function test_total_prize_amount_for_multi_tier(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $this->assertEquals(400, $leaderboard->total_prize_amount);
    }

    public function test_has_multiple_tiers_returns_true_for_multi_tier(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $this->assertTrue($leaderboard->hasMultipleTiers());
    }

    public function test_has_multiple_tiers_returns_false_for_single_tier(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create();

        $this->assertFalse($leaderboard->hasMultipleTiers());
    }

    public function test_get_sorted_tiers_returns_ascending_order(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'type' => 'target',
            'target_tiers' => [
                ['target' => 20, 'amount' => 250],
                ['target' => 5, 'amount' => 50],
                ['target' => 10, 'amount' => 100],
            ],
        ]);

        $tiers = $leaderboard->getSortedTiers();

        $this->assertEquals(5, $tiers[0]['target']);
        $this->assertEquals(10, $tiers[1]['target']);
        $this->assertEquals(20, $tiers[2]['target']);
    }

    public function test_get_minimum_target_referrals(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $this->assertEquals(5, $leaderboard->getMinimumTargetReferrals());
    }

    public function test_get_tier_for_referral_count_returns_highest_achieved(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $tier = $leaderboard->getTierForReferralCount(15);

        $this->assertEquals(10, $tier['target']);
        $this->assertEquals(100, $tier['amount']);
    }

    public function test_get_tier_for_referral_count_returns_null_below_minimum(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $tier = $leaderboard->getTierForReferralCount(3);

        $this->assertNull($tier);
    }

    public function test_get_next_tier_for_referral_count(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $nextTier = $leaderboard->getNextTierForReferralCount(7);

        $this->assertEquals(10, $nextTier['target']);
        $this->assertEquals(100, $nextTier['amount']);
    }

    public function test_get_next_tier_returns_null_when_all_achieved(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $nextTier = $leaderboard->getNextTierForReferralCount(25);

        $this->assertNull($nextTier);
    }

    public function test_get_prize_amount_for_referral_count(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create();

        $this->assertEquals(0, $leaderboard->getPrizeAmountForReferralCount(3));
        $this->assertEquals(50, $leaderboard->getPrizeAmountForReferralCount(5));
        $this->assertEquals(50, $leaderboard->getPrizeAmountForReferralCount(7));
        $this->assertEquals(100, $leaderboard->getPrizeAmountForReferralCount(10));
        $this->assertEquals(100, $leaderboard->getPrizeAmountForReferralCount(15));
        $this->assertEquals(250, $leaderboard->getPrizeAmountForReferralCount(20));
        $this->assertEquals(250, $leaderboard->getPrizeAmountForReferralCount(30));
    }

    public function test_get_user_position(): void
    {
        $leaderboard = Leaderboard::factory()->create();
        $user = User::factory()->create();

        $position = LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($user)
            ->atPosition(5)
            ->withReferralCount(15)
            ->create();

        $retrievedPosition = $leaderboard->getUserPosition($user);

        $this->assertNotNull($retrievedPosition);
        $this->assertEquals(5, $retrievedPosition->position);
        $this->assertEquals(15, $retrievedPosition->referral_count);
    }

    public function test_get_user_position_returns_null_for_non_participant(): void
    {
        $leaderboard = Leaderboard::factory()->create();
        $user = User::factory()->create();

        $this->assertNull($leaderboard->getUserPosition($user));
    }

    public function test_get_user_rank(): void
    {
        $leaderboard = Leaderboard::factory()->create();
        $user = User::factory()->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($user)
            ->atPosition(3)
            ->create();

        $this->assertEquals(3, $leaderboard->getUserRank($user));
    }

    public function test_get_participants_count(): void
    {
        $leaderboard = Leaderboard::factory()->create();

        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->count(5)->create();

        $this->assertEquals(5, $leaderboard->getParticipantsCount());
    }

    public function test_get_winners_count_for_competitive(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create();

        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->withPrize(100)->count(3)->create();
        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->withPrize(0)->count(2)->create();

        $this->assertEquals(3, $leaderboard->getWinnersCount());
    }

    public function test_scope_active(): void
    {
        Leaderboard::factory()->create(['status' => 'active']);
        Leaderboard::factory()->inactive()->create();
        Leaderboard::factory()->completed()->create();

        $active = Leaderboard::active()->get();

        $this->assertCount(1, $active);
    }

    public function test_scope_completed(): void
    {
        Leaderboard::factory()->create(['status' => 'active']);
        Leaderboard::factory()->completed()->create();

        $completed = Leaderboard::completed()->get();

        $this->assertCount(1, $completed);
    }

    public function test_scope_visible(): void
    {
        Leaderboard::factory()->create(['show_to_users' => true]);
        Leaderboard::factory()->hidden()->create();

        $visible = Leaderboard::visible()->get();

        $this->assertCount(1, $visible);
    }

    public function test_scope_competitive(): void
    {
        Leaderboard::factory()->competitive()->create();
        Leaderboard::factory()->target()->create();

        $competitive = Leaderboard::competitive()->get();

        $this->assertCount(1, $competitive);
    }

    public function test_scope_target(): void
    {
        Leaderboard::factory()->competitive()->create();
        Leaderboard::factory()->target()->create();

        $target = Leaderboard::target()->get();

        $this->assertCount(1, $target);
    }

    public function test_status_badge_class_attribute(): void
    {
        $active = Leaderboard::factory()->create(['status' => 'active']);
        $completed = Leaderboard::factory()->completed()->create();
        $inactive = Leaderboard::factory()->inactive()->create();

        $this->assertEquals('bg-success', $active->status_badge_class);
        $this->assertEquals('bg-primary', $completed->status_badge_class);
        $this->assertEquals('bg-secondary', $inactive->status_badge_class);
    }

    public function test_type_display_attribute(): void
    {
        $competitive = Leaderboard::factory()->competitive()->create();
        $target = Leaderboard::factory()->target()->create();

        $this->assertEquals('Competitive Ranking', $competitive->type_display);
        $this->assertEquals('Target Achievement', $target->type_display);
    }

    public function test_referral_type_display_attribute(): void
    {
        $direct = Leaderboard::factory()->create(['referral_type' => 'direct']);
        $multiLevel = Leaderboard::factory()->multiLevel(5)->create();

        $this->assertEquals('Direct Referrals Only', $direct->referral_type_display);
        $this->assertStringContainsString('Multi-Level Referrals', $multiLevel->referral_type_display);
        $this->assertStringContainsString('Level 5', $multiLevel->referral_type_display);
    }

    public function test_user_qualifies_for_target_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create([
            'target_referrals' => 10,
            'target_prize_amount' => 100,
        ]);

        $qualifiedUser = User::factory()->create();
        $unqualifiedUser = User::factory()->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($qualifiedUser)
            ->withReferralCount(15)
            ->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($unqualifiedUser)
            ->withReferralCount(5)
            ->create();

        $this->assertTrue($leaderboard->userQualifies($qualifiedUser));
        $this->assertFalse($leaderboard->userQualifies($unqualifiedUser));
    }

    public function test_get_target_progress(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create([
            'target_referrals' => 10,
            'target_prize_amount' => 100,
        ]);

        $user = User::factory()->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($user)
            ->withReferralCount(7)
            ->create();

        $progress = $leaderboard->getTargetProgress($user);

        $this->assertEquals(70, $progress);
    }
}
