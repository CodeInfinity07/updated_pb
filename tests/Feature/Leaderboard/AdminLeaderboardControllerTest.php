<?php

namespace Tests\Feature\Leaderboard;

use App\Models\Leaderboard;
use App\Models\LeaderboardPosition;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeaderboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_can_view_leaderboards_index(): void
    {
        Leaderboard::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.leaderboards.index');
    }

    public function test_guest_cannot_access_admin_leaderboards(): void
    {
        $response = $this->get(route('admin.leaderboards.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_leaderboards(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('admin.leaderboards.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_view_create_leaderboard_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.leaderboards.create');
    }

    public function test_admin_can_create_competitive_leaderboard(): void
    {
        $data = [
            'title' => 'Test Competitive Leaderboard',
            'description' => 'A test leaderboard description',
            'type' => 'competitive',
            'start_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'show_to_users' => true,
            'max_positions' => 50,
            'referral_type' => 'direct',
            'prize_structure' => [
                ['position' => 1, 'amount' => 500],
                ['position' => 2, 'amount' => 300],
                ['position' => 3, 'amount' => 200],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaderboards', [
            'title' => 'Test Competitive Leaderboard',
            'type' => 'competitive',
        ]);
    }

    public function test_admin_can_create_target_leaderboard(): void
    {
        $data = [
            'title' => 'Test Target Leaderboard',
            'description' => 'Reach the target to win',
            'type' => 'target',
            'start_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'show_to_users' => true,
            'max_positions' => 100,
            'referral_type' => 'direct',
            'target_referrals' => 10,
            'target_prize_amount' => 100,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaderboards', [
            'title' => 'Test Target Leaderboard',
            'type' => 'target',
            'target_referrals' => 10,
        ]);
    }

    public function test_admin_can_create_multi_tier_leaderboard(): void
    {
        $data = [
            'title' => 'Multi-Tier Leaderboard',
            'description' => 'Multiple reward tiers',
            'type' => 'target',
            'start_date' => Carbon::now()->addDay()->format('Y-m-d'),
            'end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'show_to_users' => true,
            'max_positions' => 100,
            'referral_type' => 'direct',
            'target_tiers' => [
                ['target' => 5, 'amount' => 50],
                ['target' => 10, 'amount' => 100],
                ['target' => 20, 'amount' => 250],
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaderboards', [
            'title' => 'Multi-Tier Leaderboard',
            'type' => 'target',
        ]);
    }

    public function test_create_leaderboard_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.store'), []);

        $response->assertSessionHasErrors(['title', 'type', 'start_date', 'end_date']);
    }

    public function test_create_leaderboard_validates_end_date_after_start(): void
    {
        $data = [
            'title' => 'Test Leaderboard',
            'type' => 'competitive',
            'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'end_date' => Carbon::now()->addDay()->format('Y-m-d'),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.store'), $data);

        $response->assertSessionHasErrors('end_date');
    }

    public function test_admin_can_view_leaderboard_details(): void
    {
        $leaderboard = Leaderboard::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.show', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewIs('admin.leaderboards.show');
        $response->assertViewHas('leaderboard');
    }

    public function test_admin_can_view_edit_form(): void
    {
        $leaderboard = Leaderboard::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.edit', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewIs('admin.leaderboards.edit');
    }

    public function test_admin_can_update_inactive_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.leaderboards.update', $leaderboard), [
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'type' => $leaderboard->type,
                'start_date' => $leaderboard->start_date->format('Y-m-d'),
                'end_date' => $leaderboard->end_date->format('Y-m-d'),
                'show_to_users' => true,
                'max_positions' => 50,
                'referral_type' => 'direct',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('leaderboards', [
            'id' => $leaderboard->id,
            'title' => 'Updated Title',
        ]);
    }

    public function test_admin_can_activate_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->inactive()->create([
            'start_date' => Carbon::now()->subDay(),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.activate', $leaderboard));

        $response->assertRedirect();
        $leaderboard->refresh();
        $this->assertEquals('active', $leaderboard->status);
    }

    public function test_admin_can_complete_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->create([
            'status' => 'active',
            'end_date' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.complete', $leaderboard));

        $response->assertRedirect();
        $leaderboard->refresh();
        $this->assertEquals('completed', $leaderboard->status);
    }

    public function test_admin_can_calculate_positions(): void
    {
        $leaderboard = Leaderboard::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.calculate-positions', $leaderboard));

        $response->assertRedirect();
    }

    public function test_admin_can_distribute_prizes(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->create([
            'prizes_distributed' => false,
        ]);

        $winner = User::factory()->create();
        LeaderboardPosition::factory()
            ->forLeaderboard($leaderboard)
            ->forUser($winner)
            ->withPrize(500)
            ->create(['prize_awarded' => false]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.distribute-prizes', $leaderboard));

        $response->assertRedirect();
    }

    public function test_admin_cannot_distribute_prizes_twice(): void
    {
        $leaderboard = Leaderboard::factory()->completed()->prizesDistributed()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.distribute-prizes', $leaderboard));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_delete_inactive_leaderboard(): void
    {
        $leaderboard = Leaderboard::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.leaderboards.destroy', $leaderboard));

        $response->assertRedirect();
        $this->assertDatabaseMissing('leaderboards', ['id' => $leaderboard->id]);
    }

    public function test_admin_can_filter_by_status(): void
    {
        Leaderboard::factory()->create(['status' => 'active']);
        Leaderboard::factory()->completed()->create();
        Leaderboard::factory()->inactive()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.index', ['status' => 'active']));

        $response->assertStatus(200);
    }

    public function test_admin_can_filter_by_type(): void
    {
        Leaderboard::factory()->competitive()->create();
        Leaderboard::factory()->target()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.index', ['type' => 'competitive']));

        $response->assertStatus(200);
    }

    public function test_admin_can_view_leaderboard_participants(): void
    {
        $leaderboard = Leaderboard::factory()->create();
        LeaderboardPosition::factory()->forLeaderboard($leaderboard)->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.leaderboards.show', $leaderboard));

        $response->assertStatus(200);
        $response->assertViewHas('leaderboard');
    }

    public function test_admin_can_toggle_visibility(): void
    {
        $leaderboard = Leaderboard::factory()->create(['show_to_users' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.leaderboards.toggle-visibility', $leaderboard));

        $response->assertRedirect();
        $leaderboard->refresh();
        $this->assertFalse($leaderboard->show_to_users);
    }
}
