<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rank;
use App\Models\UserRank;
use App\Models\User;
use App\Services\RankService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminRankController extends Controller
{
    protected RankService $rankService;

    public function __construct(RankService $rankService)
    {
        $this->rankService = $rankService;
    }

    public function index()
    {
        $ranks = Rank::ordered()->get()->map(function ($rank) {
            $rank->users_achieved = $rank->getUsersAchievedCount();
            $rank->total_rewards_paid = $rank->getTotalRewardsPaid();
            return $rank;
        });

        $statistics = [
            'total_ranks' => Rank::count(),
            'active_ranks' => Rank::active()->count(),
            'total_achievements' => UserRank::count(),
            'total_rewards_paid' => UserRank::where('reward_paid', true)
                ->join('ranks', 'user_ranks.rank_id', '=', 'ranks.id')
                ->sum('ranks.reward_amount'),
            'pending_rewards' => UserRank::where('reward_paid', false)->count(),
        ];

        return view('admin.ranks.index', compact('ranks', 'statistics'));
    }

    public function create()
    {
        $maxOrder = Rank::max('display_order') ?? 0;
        return view('admin.ranks.create', compact('maxOrder'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'required|integer|min:1',
            'min_self_deposit' => 'required|numeric|min:0',
            'min_direct_members' => 'required|integer|min:0',
            'min_direct_member_investment' => 'required|numeric|min:0',
            'min_team_members' => 'required|integer|min:0',
            'min_team_member_investment' => 'required|numeric|min:0',
            'reward_amount' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Rank::create($validated);

        return redirect()->route('admin.ranks.index')
            ->with('success', 'Rank created successfully.');
    }

    public function edit(Rank $rank)
    {
        return view('admin.ranks.edit', compact('rank'));
    }

    public function update(Request $request, Rank $rank)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'display_order' => 'required|integer|min:1',
            'min_self_deposit' => 'required|numeric|min:0',
            'min_direct_members' => 'required|integer|min:0',
            'min_direct_member_investment' => 'required|numeric|min:0',
            'min_team_members' => 'required|integer|min:0',
            'min_team_member_investment' => 'required|numeric|min:0',
            'reward_amount' => 'required|numeric|min:0',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rank->update($validated);

        return redirect()->route('admin.ranks.index')
            ->with('success', 'Rank updated successfully.');
    }

    public function destroy(Rank $rank)
    {
        $usersWithRank = $rank->userRanks()->count();
        
        if ($usersWithRank > 0) {
            return redirect()->route('admin.ranks.index')
                ->with('error', "Cannot delete rank. {$usersWithRank} user(s) have achieved this rank.");
        }

        $rank->delete();

        return redirect()->route('admin.ranks.index')
            ->with('success', 'Rank deleted successfully.');
    }

    public function toggleStatus(Rank $rank): JsonResponse
    {
        $rank->update(['is_active' => !$rank->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Rank status updated.',
            'is_active' => $rank->is_active,
        ]);
    }

    public function achievements(Request $request)
    {
        $ranks = Rank::ordered()->get();
        $selectedRankId = $request->get('rank_id');
        
        $query = UserRank::with(['user', 'rank'])->latest('achieved_at');
        
        if ($selectedRankId) {
            $query->where('rank_id', $selectedRankId);
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->get('reward_status') === 'paid') {
            $query->where('reward_paid', true);
        } elseif ($request->get('reward_status') === 'pending') {
            $query->where('reward_paid', false);
        }

        $achievements = $query->paginate(20);

        return view('admin.ranks.achievements', compact('achievements', 'ranks', 'selectedRankId'));
    }

    public function payReward(UserRank $userRank): JsonResponse
    {
        if ($userRank->reward_paid) {
            return response()->json([
                'success' => false,
                'message' => 'Reward has already been paid.',
            ]);
        }

        $user = $userRank->user;
        $amount = $this->rankService->payRankReward($user, $userRank);

        if ($amount > 0) {
            return response()->json([
                'success' => true,
                'message' => "Reward of \${$amount} paid successfully.",
                'amount' => $amount,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to pay reward.',
        ]);
    }

    public function processRanks(): JsonResponse
    {
        $stats = $this->rankService->processAllUsers();

        return response()->json([
            'success' => true,
            'message' => "Processed {$stats['users_checked']} users. Awarded {$stats['ranks_awarded']} new ranks. Paid \${$stats['rewards_paid']} in rewards.",
            'stats' => $stats,
        ]);
    }

    public function checkUserRanks(User $user): JsonResponse
    {
        $result = $this->rankService->checkAndAwardRanks($user);

        return response()->json([
            'success' => true,
            'new_ranks' => collect($result['new_ranks'])->pluck('name'),
            'rewards_paid' => $result['rewards_paid'],
        ]);
    }

    public function previewQualifications(Request $request)
    {
        $ranks = Rank::active()->ordered()->get();
        $selectedRankId = $request->get('rank_id');
        $search = $request->get('search');
        $submitted = $request->has('rank_id') || $request->has('search');
        
        $qualifiedUsers = collect();
        $totalPotentialReward = 0;

        if ($submitted && $selectedRankId) {
            $rank = Rank::find($selectedRankId);
            if ($rank) {
                $query = User::where('status', 'active')
                    ->where('excluded_from_stats', false);
                
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
                }

                $users = $query->get();
                
                foreach ($users as $user) {
                    if ($user->hasRank($rank->id)) {
                        continue;
                    }
                    
                    $progress = $this->rankService->getSingleRankProgress($user, $rank);
                    $qualifies = $progress['self_deposit']['met'] 
                        && $progress['direct_members']['met'] 
                        && $progress['team_members']['met'];
                    
                    $previousRanks = Rank::active()
                        ->where('display_order', '<', $rank->display_order)
                        ->pluck('id');
                    
                    $hasPreviousRanks = true;
                    foreach ($previousRanks as $prevRankId) {
                        if (!$user->hasRank($prevRankId)) {
                            $hasPreviousRanks = false;
                            break;
                        }
                    }
                    
                    if ($qualifies && $hasPreviousRanks) {
                        $qualifiedUsers->push([
                            'user' => $user,
                            'progress' => $progress,
                        ]);
                        $totalPotentialReward += $rank->reward_amount;
                    }
                }
            }
        } elseif ($submitted) {
            $users = User::where('status', 'active')
                ->where('excluded_from_stats', false);
            
            if ($search) {
                $users->where(function ($q) use ($search) {
                    $q->where('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                });
            }
            
            $users = $users->get();
            
            foreach ($users as $user) {
                $virtuallyEarnedRankIds = $user->ranks()->pluck('rank_id')->toArray();
                
                foreach ($ranks as $rank) {
                    if (in_array($rank->id, $virtuallyEarnedRankIds)) {
                        continue;
                    }
                    
                    $progress = $this->rankService->getSingleRankProgress($user, $rank);
                    $meetsRequirements = $progress['self_deposit']['met'] 
                        && $progress['direct_members']['met'] 
                        && $progress['team_members']['met'];
                    
                    if (!$meetsRequirements) {
                        continue;
                    }
                    
                    $previousRanks = Rank::active()
                        ->where('display_order', '<', $rank->display_order)
                        ->pluck('id')
                        ->toArray();
                    
                    $hasPreviousRanks = empty(array_diff($previousRanks, $virtuallyEarnedRankIds));
                    
                    if ($hasPreviousRanks) {
                        $qualifiedUsers->push([
                            'user' => $user,
                            'rank' => $rank,
                            'progress' => $progress,
                        ]);
                        $totalPotentialReward += $rank->reward_amount;
                        $virtuallyEarnedRankIds[] = $rank->id;
                    }
                }
            }
        }

        $selectedRank = $selectedRankId ? Rank::find($selectedRankId) : null;

        return view('admin.ranks.preview-qualifications', compact(
            'ranks', 
            'qualifiedUsers', 
            'selectedRank', 
            'selectedRankId',
            'totalPotentialReward',
            'search',
            'submitted'
        ));
    }
}
