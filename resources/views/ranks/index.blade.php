@extends('layouts.vertical', ['title' => 'Rank & Reward', 'subTitle' => 'Achievements'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title d-flex align-items-center mb-0">
                    <iconify-icon icon="akar-icons:trophy" class="me-2"></iconify-icon>
                    Your Rank Progress
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-primary border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="text-primary mb-2">Current Rank</h5>
                                        @if($highestRank)
                                        <h4 class="mb-0 fw-bold">
                                            @if($highestRank->icon)
                                            <iconify-icon icon="{{ $highestRank->icon }}" class="me-1"></iconify-icon>
                                            @endif
                                            {{ $highestRank->name }}
                                        </h4>
                                        @else
                                        <h4 class="mb-0 text-muted">No Rank Yet</h4>
                                        @endif
                                    </div>
                                    <div class="avatar-lg d-inline-block">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle">
                                            <iconify-icon icon="iconamoon:badge-duotone" class="fs-32"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-success border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="text-success mb-2">Ranks Achieved</h5>
                                        <h4 class="mb-0 fw-bold">{{ count($userRanks) }} / {{ $ranks->count() }}</h4>
                                    </div>
                                    <div class="avatar-lg d-inline-block">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle">
                                            <iconify-icon icon="iconamoon:check-circle-duotone" class="fs-32"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-warning border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="text-warning mb-2">Total Rewards Earned</h5>
                                        <h4 class="mb-0 fw-bold">${{ number_format(\App\Models\UserRank::where('user_id', $user->id)->where('reward_paid', true)->join('ranks', 'user_ranks.rank_id', '=', 'ranks.id')->sum('ranks.reward_amount'), 2) }}</h4>
                                    </div>
                                    <div class="avatar-lg d-inline-block">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded-circle">
                                            <iconify-icon icon="iconamoon:gift-duotone" class="fs-32"></iconify-icon>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title d-flex align-items-center mb-0">
                    <iconify-icon icon="iconamoon:layers-duotone" class="me-2"></iconify-icon>
                    Available Ranks
                </h4>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    @foreach($ranks as $rank)
                    @php
                        $isAchieved = in_array($rank->id, $userRanks);
                        $progress = $progressData[$rank->id] ?? [];
                        $selfDepositPct = min(100, (($progress['self_deposit']['current'] ?? 0) / max(1, $progress['self_deposit']['required'] ?? 1)) * 100);
                        $directMembersPct = min(100, (($progress['direct_members']['current'] ?? 0) / max(1, $progress['direct_members']['required'] ?? 1)) * 100);
                        $teamMembersPct = $rank->min_team_members > 0 ? min(100, (($progress['team_members']['current'] ?? 0) / max(1, $progress['team_members']['required'] ?? 1)) * 100) : 100;
                        $overallPct = ($selfDepositPct + $directMembersPct + $teamMembersPct) / ($rank->min_team_members > 0 ? 3 : 2);
                    @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 {{ $isAchieved ? 'border-success' : 'border-light' }} border-2 shadow-sm">
                            <div class="card-header bg-{{ $isAchieved ? 'success' : 'light' }} {{ $isAchieved ? 'text-white' : '' }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        @if($rank->icon)
                                        <iconify-icon icon="{{ $rank->icon }}" class="me-2" style="font-size: 1.5rem;"></iconify-icon>
                                        @else
                                        <iconify-icon icon="iconamoon:badge-duotone" class="me-2" style="font-size: 1.5rem;"></iconify-icon>
                                        @endif
                                        <h5 class="mb-0">{{ $rank->name }}</h5>
                                    </div>
                                    @if($isAchieved)
                                    <span class="badge bg-white text-success">Achieved</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if($rank->description)
                                <p class="text-muted small mb-3">{{ $rank->description }}</p>
                                @endif

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Self Deposit</small>
                                        <small class="{{ ($progress['self_deposit']['met'] ?? false) ? 'text-success' : '' }}">
                                            ${{ number_format($progress['self_deposit']['current'] ?? 0, 0) }} / ${{ number_format($progress['self_deposit']['required'] ?? 0, 0) }}
                                        </small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ ($progress['self_deposit']['met'] ?? false) ? 'bg-success' : 'bg-primary' }}" style="width: {{ $selfDepositPct }}%;"></div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Direct Members <span class="text-muted">(min ${{ number_format($rank->min_direct_member_investment, 0) }})</span></small>
                                        <small class="{{ ($progress['direct_members']['met'] ?? false) ? 'text-success' : '' }}">
                                            {{ $progress['direct_members']['current'] ?? 0 }} / {{ $progress['direct_members']['required'] ?? 0 }}
                                        </small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ ($progress['direct_members']['met'] ?? false) ? 'bg-success' : 'bg-primary' }}" style="width: {{ $directMembersPct }}%;"></div>
                                    </div>
                                </div>

                                @if($rank->min_team_members > 0)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Team Members (L2+L3) <span class="text-muted">(min ${{ number_format($rank->min_team_member_investment, 0) }})</span></small>
                                        <small class="{{ ($progress['team_members']['met'] ?? false) ? 'text-success' : '' }}">
                                            {{ $progress['team_members']['current'] ?? 0 }} / {{ $progress['team_members']['required'] ?? 0 }}
                                        </small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ ($progress['team_members']['met'] ?? false) ? 'bg-success' : 'bg-primary' }}" style="width: {{ $teamMembersPct }}%;"></div>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="card-footer bg-transparent">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-{{ $isAchieved ? 'success' : 'secondary' }} fs-6">
                                        <iconify-icon icon="iconamoon:gift-duotone" class="me-1"></iconify-icon>
                                        ${{ number_format($rank->reward_amount, 0) }} Reward
                                    </span>
                                    @if(!$isAchieved)
                                    <span class="text-muted small">{{ round($overallPct) }}% Complete</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
