<?php

namespace Tests\Unit\Models;

use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardPositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_position_can_be_created(): void
    {
        $leaderboard = Leaderboard::factory()->create();
        $user = User::factory()->create();

        $position = LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($user)
            ->atPosition(1)
            ->withReferralCount(50)
            ->withPrize(500)
            ->create();

        $this->assertDatabaseHas('leaderboard_positions', [
            'id' => $position->id,
            'leaderboard_id' => $leaderboard->id,
            'user_id' => $user->id,
            'position' => 1,
            'referral_count' => 50,
        ]);
    }

    public function test_position_has_leaderboard_relationship(): void
    {
        $position = LeaderboardPosition::factory()->create();

        $this->assertInstanceOf(Leaderboard::class, $position->leaderboard);
    }

    public function test_position_has_user_relationship(): void
    {
        $position = LeaderboardPosition::factory()->create();

        $this->assertInstanceOf(User::class, $position->user);
    }

    public function test_is_winner_returns_true_when_prize_greater_than_zero(): void
    {
        $position = LeaderboardPosition::factory()->withPrize(100)->create();

        $this->assertTrue($position->isWinner());
    }

    public function test_is_winner_returns_false_when_no_prize(): void
    {
        $position = LeaderboardPosition::factory()->withPrize(0)->create();

        $this->assertFalse($position->isWinner());
    }

    public function test_is_prize_awarded_returns_correct_value(): void
    {
        $awarded = LeaderboardPosition::factory()->prizeAwarded()->create();
        $notAwarded = LeaderboardPosition::factory()->create(['prize_awarded' => false]);

        $this->assertTrue($awarded->isPrizeAwarded());
        $this->assertFalse($notAwarded->isPrizeAwarded());
    }

    public function test_is_prize_pending_returns_true_for_pending_prize(): void
    {
        $pending = LeaderboardPosition::factory()
            ->withPrize(100)
            ->create(['prize_awarded' => false]);

        $this->assertTrue($pending->isPrizePending());
    }

    public function test_is_prize_pending_returns_false_when_already_awarded(): void
    {
        $awarded = LeaderboardPosition::factory()
            ->withPrize(100)
            ->prizeAwarded()
            ->create();

        $this->assertFalse($awarded->isPrizePending());
    }

    public function test_is_prize_pending_returns_false_when_no_prize(): void
    {
        $noPrize = LeaderboardPosition::factory()->withPrize(0)->create();

        $this->assertFalse($noPrize->isPrizePending());
    }

    public function test_is_top_three_returns_true_for_positions_1_to_3(): void
    {
        $first = LeaderboardPosition::factory()->atPosition(1)->create();
        $second = LeaderboardPosition::factory()->atPosition(2)->create();
        $third = LeaderboardPosition::factory()->atPosition(3)->create();
        $fourth = LeaderboardPosition::factory()->atPosition(4)->create();

        $this->assertTrue($first->isTopThree());
        $this->assertTrue($second->isTopThree());
        $this->assertTrue($third->isTopThree());
        $this->assertFalse($fourth->isTopThree());
    }

    public function test_position_display_attribute(): void
    {
        $first = LeaderboardPosition::factory()->atPosition(1)->create();
        $second = LeaderboardPosition::factory()->atPosition(2)->create();
        $third = LeaderboardPosition::factory()->atPosition(3)->create();
        $fifth = LeaderboardPosition::factory()->atPosition(5)->create();

        $this->assertStringContainsString('1st', $first->position_display);
        $this->assertStringContainsString('2nd', $second->position_display);
        $this->assertStringContainsString('3rd', $third->position_display);
        $this->assertEquals('#5', $fifth->position_display);
    }

    public function test_position_badge_class_attribute(): void
    {
        $first = LeaderboardPosition::factory()->atPosition(1)->create();
        $second = LeaderboardPosition::factory()->atPosition(2)->create();
        $fifth = LeaderboardPosition::factory()->atPosition(5)->create();

        $this->assertStringContainsString('warning', $first->position_badge_class);
        $this->assertStringContainsString('secondary', $second->position_badge_class);
        $this->assertStringContainsString('primary', $fifth->position_badge_class);
    }

    public function test_prize_status_badge_class_attribute(): void
    {
        $noPrize = LeaderboardPosition::factory()->withPrize(0)->create();
        $pending = LeaderboardPosition::factory()->withPrize(100)->create(['prize_awarded' => false]);
        $awarded = LeaderboardPosition::factory()->withPrize(100)->prizeAwarded()->create();

        $this->assertStringContainsString('light', $noPrize->prize_status_badge_class);
        $this->assertStringContainsString('warning', $pending->prize_status_badge_class);
        $this->assertStringContainsString('success', $awarded->prize_status_badge_class);
    }

    public function test_prize_status_text_attribute(): void
    {
        $noPrize = LeaderboardPosition::factory()->withPrize(0)->create();
        $pending = LeaderboardPosition::factory()->withPrize(100)->create(['prize_awarded' => false]);
        $awarded = LeaderboardPosition::factory()->withPrize(100)->prizeAwarded()->create();

        $this->assertEquals('No Prize', $noPrize->prize_status_text);
        $this->assertEquals('Pending', $pending->prize_status_text);
        $this->assertEquals('Awarded', $awarded->prize_status_text);
    }

    public function test_get_position_icon(): void
    {
        $first = LeaderboardPosition::factory()->atPosition(1)->create();
        $second = LeaderboardPosition::factory()->atPosition(2)->create();
        $fifth = LeaderboardPosition::factory()->atPosition(5)->create();

        $this->assertStringContainsString('trophy', $first->getPositionIcon());
        $this->assertStringContainsString('medal', $second->getPositionIcon());
        $this->assertStringContainsString('hashtag', $fifth->getPositionIcon());
    }

    public function test_mark_prize_as_awarded(): void
    {
        $position = LeaderboardPosition::factory()
            ->withPrize(100)
            ->create(['prize_awarded' => false]);

        $this->assertFalse($position->prize_awarded);
        $this->assertNull($position->prize_awarded_at);

        $position->markPrizeAsAwarded();
        $position->refresh();

        $this->assertTrue($position->prize_awarded);
        $this->assertNotNull($position->prize_awarded_at);
    }

    public function test_get_formatted_prize_amount(): void
    {
        $withPrize = LeaderboardPosition::factory()->withPrize(1500.50)->create();
        $noPrize = LeaderboardPosition::factory()->withPrize(0)->create();

        $this->assertEquals('$1,500.50', $withPrize->getFormattedPrizeAmount());
        $this->assertEquals('No Prize', $noPrize->getFormattedPrizeAmount());
    }

    public function test_scope_winners(): void
    {
        $leaderboard = Leaderboard::factory()->create();

        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->withPrize(100)->count(3)->create();
        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->withPrize(0)->count(2)->create();

        $winners = LeaderboardPosition::winners()->count();

        $this->assertEquals(3, $winners);
    }

    public function test_scope_prize_awarded(): void
    {
        LeaderboardPosition::factory()->withPrize(100)->prizeAwarded()->count(2)->create();
        LeaderboardPosition::factory()->withPrize(100)->create(['prize_awarded' => false]);

        $awarded = LeaderboardPosition::prizeAwarded()->count();

        $this->assertEquals(2, $awarded);
    }

    public function test_scope_prize_pending(): void
    {
        LeaderboardPosition::factory()->withPrize(100)->prizeAwarded()->create();
        LeaderboardPosition::factory()->withPrize(100)->count(3)->create(['prize_awarded' => false]);
        LeaderboardPosition::factory()->withPrize(0)->create();

        $pending = LeaderboardPosition::prizePending()->count();

        $this->assertEquals(3, $pending);
    }

    public function test_scope_top_positions(): void
    {
        $leaderboard = Leaderboard::factory()->create();

        for ($i = 1; $i <= 15; $i++) {
            LeaderboardPosition::factory()
                ->forLeaderboard($leaderboard)
                ->atPosition($i)
                ->create();
        }

        $topFive = LeaderboardPosition::where('leaderboard_id', $leaderboard->id)
            ->topPositions(5)
            ->get();

        $this->assertCount(5, $topFive);
        $this->assertEquals(1, $topFive->first()->position);
        $this->assertEquals(5, $topFive->last()->position);
    }
}
