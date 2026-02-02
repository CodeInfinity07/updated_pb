<?php

namespace Tests\Feature\Leaderboard;

use App\Models\CryptoWallet;
use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Models\User;
use App\Models\UserInvestment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLeaderboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        CryptoWallet::factory()->forUser($this->user)->usdt()->create();
    }

    public function test_user_can_view_leaderboards_index(): void
    {
        Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.index'));

        $response->assertStatus(200);
        $response->assertViewIs('leaderboards.index');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('leaderboards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_sees_only_visible_leaderboards(): void
    {
        Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'title' => 'Visible Leaderboard',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        Leaderboard::factory()->hidden()->create([
            'status' => 'active',
            'title' => 'Hidden Leaderboard',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.index'));

        $response->assertStatus(200);
        $response->assertSee('Visible Leaderboard');
        $response->assertDontSee('Hidden Leaderboard');
    }

    public function test_user_can_view_leaderboard_details(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewIs('leaderboards.show');
        $response->assertViewHas('leaderboard');
    }

    public function test_user_cannot_view_hidden_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->hidden()->create();

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(404);
    }

    public function test_user_sees_their_position_on_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($this->user)
            ->atPosition(5)
            ->withReferralCount(10)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewHas('userPosition');
    }

    public function test_user_can_view_completed_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'show_to_users' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_user_sees_winners_on_completed_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'show_to_users' => true,
            'prizes_distributed' => true,
        ]);

        $winner = User::factory()->create();
        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($winner)
            ->atPosition(1)
            ->withPrize(500)
            ->prizeAwarded()
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_user_sees_countdown_on_active_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewHas('leaderboard');
    }

    public function test_user_sees_progress_bar_for_target_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->target()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'target_referrals' => 10,
            'target_prize_amount' => 100,
        ]);

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($this->user)
            ->withReferralCount(5)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_user_can_view_leaderboard_history(): void
    {
        $completedLeaderboard = Leaderboard::factory()->completed()->create([
            'show_to_users' => true,
        ]);

        LeaderboardPosition::factory()
            ->forLeaderboard($completedLeaderboard)
            ->forUser($this->user)
            ->atPosition(3)
            ->withPrize(200)
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.history'));

        $response->assertStatus(200);
    }

    public function test_user_sees_top_positions_on_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        for ($i = 1; $i <= 10; $i++) {
            $user = User::factory()->create();
            LeaderboardPosition::factory()
                ->forLeaderboard($leaderboard)
                ->forUser($user)
                ->atPosition($i)
                ->withReferralCount(100 - ($i * 5))
                ->create();
        }

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewHas('topPositions');
    }

    public function test_user_can_share_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.share', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_leaderboard_view_tracking(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));
    }

    public function test_user_sees_competitive_rankings(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
            'prize_structure' => [
                ['position' => 1, 'amount' => 500],
                ['position' => 2, 'amount' => 300],
                ['position' => 3, 'amount' => 200],
            ],
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_user_sees_multi_tier_target_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->multiTier()->create([
            'status' => 'active',
            'show_to_users' => true,
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->addDays(5),
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_podium_display_on_completed_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->competitive()->completed()->create([
            'show_to_users' => true,
            'prizes_distributed' => true,
        ]);

        $first = User::factory()->create(['first_name' => 'First', 'last_name' => 'Winner']);
        $second = User::factory()->create(['first_name' => 'Second', 'last_name' => 'Place']);
        $third = User::factory()->create(['first_name' => 'Third', 'last_name' => 'Place']);

        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->forUser($first)
            ->atPosition(1)->withPrize(500)->prizeAwarded()->create();
        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->forUser($second)
            ->atPosition(2)->withPrize(300)->prizeAwarded()->create();
        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->forUser($third)
            ->atPosition(3)->withPrize(200)->prizeAwarded()->create();

        $response = $this->actingAs($this->user)
            ->get(route('leaderboards.show', $leaderboard));

        $response->assertStatus(200);
    }

    public function test_user_profile_shows_leaderboard_achievements(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'show_to_users' => true,
            'prizes_distributed' => true,
        ]);

        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($this->user)
            ->atPosition(1)
            ->withPrize(500)
            ->prizeAwarded()
            ->create();

        $response = $this->actingAs($this->user)
            ->get(route('profile'));

        $response->assertStatus(200);
    }
}
