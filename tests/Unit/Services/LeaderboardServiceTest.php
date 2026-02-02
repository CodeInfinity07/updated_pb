<?php

namespace Tests\Unit\Services;

use App\Models\CryptoWallet;
use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Models\User;
use App\Models\UserInvestment;
use App\Services\LeaderboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LeaderboardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaderboardService();
    }

    public function test_calculate_positions_for_competitive_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'prize_structure' => [
                ['position' => 1, 'amount' => 500],
                ['position' => 2, 'amount' => 300],
                ['position' => 3, 'amount' => 200],
            ],
        ]);

        $sponsor1 = User::factory()->create();
        $sponsor2 = User::factory()->create();
        $sponsor3 = User::factory()->create();

        $this->createReferralsWithInvestments($sponsor1, 10, $leaderboard);
        $this->createReferralsWithInvestments($sponsor2, 5, $leaderboard);
        $this->createReferralsWithInvestments($sponsor3, 3, $leaderboard);

        $count = $this->service->calculatePositions($leaderboard);

        $this->assertEquals(3, $count);

        $positions = $leaderboard->positions()->orderBy('position')->get();

        $this->assertEquals($sponsor1->id, $positions[0]->user_id);
        $this->assertEquals(1, $positions[0]->position);
        $this->assertEquals(10, $positions[0]->referral_count);
        $this->assertEquals(500, $positions[0]->prize_amount);

        $this->assertEquals($sponsor2->id, $positions[1]->user_id);
        $this->assertEquals(2, $positions[1]->position);
        $this->assertEquals(5, $positions[1]->referral_count);
        $this->assertEquals(300, $positions[1]->prize_amount);

        $this->assertEquals($sponsor3->id, $positions[2]->user_id);
        $this->assertEquals(3, $positions[2]->position);
        $this->assertEquals(3, $positions[2]->referral_count);
        $this->assertEquals(200, $positions[2]->prize_amount);
    }

    public function test_calculate_positions_for_target_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'target_referrals' => 5,
            'target_prize_amount' => 100,
        ]);

        $qualified = User::factory()->create();
        $notQualified = User::factory()->create();

        $this->createReferralsWithInvestments($qualified, 7, $leaderboard);
        $this->createReferralsWithInvestments($notQualified, 3, $leaderboard);

        $this->service->calculatePositions($leaderboard);

        $qualifiedPosition = $leaderboard->positions()->where('user_id', $qualified->id)->first();
        $notQualifiedPosition = $leaderboard->positions()->where('user_id', $notQualified->id)->first();

        $this->assertEquals(100, $qualifiedPosition->prize_amount);
        $this->assertEquals(0, $notQualifiedPosition->prize_amount);
    }

    public function test_calculate_positions_for_multi_tier_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
            'target_tiers' => [
                ['target' => 5, 'amount' => 50],
                ['target' => 10, 'amount' => 100],
                ['target' => 20, 'amount' => 250],
            ],
        ]);

        $tier1User = User::factory()->create();
        $tier2User = User::factory()->create();
        $tier3User = User::factory()->create();
        $noTierUser = User::factory()->create();

        $this->createReferralsWithInvestments($tier1User, 7, $leaderboard);
        $this->createReferralsWithInvestments($tier2User, 15, $leaderboard);
        $this->createReferralsWithInvestments($tier3User, 25, $leaderboard);
        $this->createReferralsWithInvestments($noTierUser, 3, $leaderboard);

        $this->service->calculatePositions($leaderboard);

        $this->assertEquals(50, $leaderboard->positions()->where('user_id', $tier1User->id)->first()->prize_amount);
        $this->assertEquals(100, $leaderboard->positions()->where('user_id', $tier2User->id)->first()->prize_amount);
        $this->assertEquals(250, $leaderboard->positions()->where('user_id', $tier3User->id)->first()->prize_amount);
        $this->assertEquals(0, $leaderboard->positions()->where('user_id', $noTierUser->id)->first()->prize_amount);
    }

    public function test_calculate_positions_handles_ties(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $sponsor1 = User::factory()->create();
        $sponsor2 = User::factory()->create();

        $this->createReferralsWithInvestments($sponsor1, 5, $leaderboard);

        Carbon::setTestNow(Carbon::now()->addHour());

        $this->createReferralsWithInvestments($sponsor2, 5, $leaderboard);

        Carbon::setTestNow();

        $this->service->calculatePositions($leaderboard);

        $positions = $leaderboard->positions()->orderBy('position')->get();

        $this->assertEquals(1, $positions[0]->position);
        $this->assertEquals(1, $positions[1]->position);
    }

    public function test_calculate_positions_only_counts_referrals_with_investments(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $sponsor = User::factory()->create();

        $referralWithInvestment = User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(5),
        ]);

        UserInvestment::factory()
            ->forUser($referralWithInvestment)
            ->active()
            ->createdAt(Carbon::now()->subDays(4))
            ->create();

        User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $this->service->calculatePositions($leaderboard);

        $position = $leaderboard->positions()->where('user_id', $sponsor->id)->first();

        $this->assertNotNull($position);
        $this->assertEquals(1, $position->referral_count);
    }

    public function test_calculate_positions_respects_min_investment_amount(): void
    {
        $leaderboard = Leaderboard::factory()
            ->competitive()
            ->withMinInvestment(100)
            ->create([
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(10),
            ]);

        $sponsor = User::factory()->create();

        $referral1 = User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(5),
        ]);
        UserInvestment::factory()
            ->forUser($referral1)
            ->withAmount(150)
            ->active()
            ->createdAt(Carbon::now()->subDays(4))
            ->create();

        $referral2 = User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(5),
        ]);
        UserInvestment::factory()
            ->forUser($referral2)
            ->withAmount(50)
            ->active()
            ->createdAt(Carbon::now()->subDays(4))
            ->create();

        $this->service->calculatePositions($leaderboard);

        $position = $leaderboard->positions()->where('user_id', $sponsor->id)->first();

        $this->assertEquals(1, $position->referral_count);
    }

    public function test_calculate_positions_respects_date_range(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $sponsor = User::factory()->create();

        $withinRange = User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(3),
        ]);
        UserInvestment::factory()
            ->forUser($withinRange)
            ->active()
            ->createdAt(Carbon::now()->subDays(2))
            ->create();

        $beforeRange = User::factory()->withSponsor($sponsor)->create([
            'created_at' => Carbon::now()->subDays(10),
        ]);
        UserInvestment::factory()
            ->forUser($beforeRange)
            ->active()
            ->createdAt(Carbon::now()->subDays(9))
            ->create();

        $this->service->calculatePositions($leaderboard);

        $position = $leaderboard->positions()->where('user_id', $sponsor->id)->first();

        $this->assertEquals(1, $position->referral_count);
    }

    public function test_calculate_positions_respects_max_winners_limit(): void
    {
        $leaderboard = Leaderboard::factory()
            ->target()
            ->withMaxWinners(2)
            ->create([
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(10),
                'target_referrals' => 5,
                'target_prize_amount' => 100,
            ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $this->createReferralsWithInvestments($user1, 10, $leaderboard);
        $this->createReferralsWithInvestments($user2, 8, $leaderboard);
        $this->createReferralsWithInvestments($user3, 6, $leaderboard);

        $this->service->calculatePositions($leaderboard);

        $positions = $leaderboard->positions()->orderByDesc('referral_count')->get();

        $winnersWithPrize = $positions->filter(fn($p) => $p->prize_amount > 0);

        $this->assertEquals(2, $winnersWithPrize->count());
    }

    public function test_calculate_positions_clears_existing_positions(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'start_date' => Carbon::now()->subDays(10),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->count(5)->create();

        $this->assertEquals(5, $leaderboard->positions()->count());

        $sponsor = User::factory()->create();
        $this->createReferralsWithInvestments($sponsor, 3, $leaderboard);

        $this->service->calculatePositions($leaderboard);

        $this->assertEquals(1, $leaderboard->positions()->count());
    }

    public function test_distribute_prizes_credits_user_wallets(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'prizes_distributed' => false,
        ]);

        $winner = User::factory()->create();
        CryptoWallet::factory()->forUser($winner)->usdt()->withBalance(100)->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($winner)
            ->atPosition(1)
            ->withPrize(500)
            ->create(['prize_awarded' => false]);

        $result = $this->service->distributePrizes($leaderboard);

        $this->assertTrue($result);

        $leaderboard->refresh();
        $this->assertTrue($leaderboard->prizes_distributed);

        $position = $leaderboard->positions()->first();
        $this->assertTrue($position->prize_awarded);
        $this->assertNotNull($position->prize_awarded_at);
    }

    public function test_distribute_prizes_fails_for_active_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'prizes_distributed' => false,
        ]);

        $result = $this->service->distributePrizes($leaderboard);

        $this->assertFalse($result);
    }

    public function test_distribute_prizes_fails_when_already_distributed(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->prizesDistributed()->create();

        $result = $this->service->distributePrizes($leaderboard);

        $this->assertFalse($result);
    }

    public function test_distribute_prizes_creates_transaction_records(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'prizes_distributed' => false,
            'title' => 'Test Leaderboard',
        ]);

        $winner = User::factory()->create();
        CryptoWallet::factory()->forUser($winner)->usdt()->create();

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($winner)
            ->atPosition(1)
            ->withPrize(500)
            ->create(['prize_awarded' => false]);

        $this->service->distributePrizes($leaderboard);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $winner->id,
            'type' => 'leaderboard_prize',
            'status' => 'completed',
        ]);
    }

    public function test_get_active_leaderboards(): void
    {
        Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        Leaderboard::factory()->inactive()->create();
        Leaderboard::factory()->completed()->create();
        Leaderboard::factory()->hidden()->create(['status' => 'active']);

        $active = $this->service->getActiveLeaderboards();

        $this->assertCount(1, $active);
    }

    public function test_get_completed_leaderboards(): void
    {
        Leaderboard::factory()->completed()->create(['show_to_users' => true]);
        Leaderboard::factory()->completed()->create(['show_to_users' => true]);
        Leaderboard::factory()->create(['status' => 'active', 'show_to_users' => true]);

        $completed = $this->service->getCompletedLeaderboards();

        $this->assertCount(2, $completed);
    }

    public function test_get_user_leaderboard_history(): void
    {
        $user = User::factory()->create();

        $leaderboard1 = Leaderboard::factory()->completed()->create(['show_to_users' => true]);
        $leaderboard2 = Leaderboard::factory()->create(['status' => 'active', 'show_to_users' => true]);

        LeaderboardPosition::factory()->forLeaderboard($leaderboard1)->forUser($user)->create();
        LeaderboardPosition::factory()->forLeaderboard($leaderboard2)->forUser($user)->create();

        $history = $this->service->getUserLeaderboardHistory($user);

        $this->assertCount(2, $history);
    }

    public function test_get_statistics(): void
    {
        Leaderboard::factory()->create(['status' => 'active']);
        Leaderboard::factory()->completed()->create();
        Leaderboard::factory()->upcoming()->create();
        Leaderboard::factory()->competitive()->create();
        Leaderboard::factory()->target()->create();

        $stats = $this->service->getStatistics();

        $this->assertArrayHasKey('total_leaderboards', $stats);
        $this->assertArrayHasKey('active_leaderboards', $stats);
        $this->assertArrayHasKey('completed_leaderboards', $stats);
        $this->assertArrayHasKey('competitive_leaderboards', $stats);
        $this->assertArrayHasKey('target_leaderboards', $stats);
    }

    protected function createReferralsWithInvestments(User $sponsor, int $count, Leaderboard $leaderboard): void
    {
        for ($i = 0; $i < $count; $i++) {
            $referral = User::factory()->withSponsor($sponsor)->create([
                'created_at' => $leaderboard->start_date->copy()->addDays(rand(1, 5)),
            ]);

            UserInvestment::factory()
                ->forUser($referral)
                ->active()
                ->createdAt($leaderboard->start_date->copy()->addDays(rand(1, 5)))
                ->create();
        }
    }
}
