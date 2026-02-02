<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use App\Services\RankService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RankController extends Controller
{
    protected RankService $rankService;

    public function __construct(RankService $rankService)
    {
        $this->rankService = $rankService;
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        
        $ranks = Rank::active()->ordered()->get();
        
        $userRanks = $user->ranks()->pluck('rank_id')->toArray();
        
        $progressData = $this->rankService->getUserRankProgress($user);
        
        $highestRank = $user->highestRank();
        
        return view('ranks.index', compact('user', 'ranks', 'userRanks', 'progressData', 'highestRank'));
    }
}
