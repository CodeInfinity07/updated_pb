@extends('layouts.vertical', ['title' => $leaderboard->title, 'subTitle' => 'Leaderboard'])

@section('content')
<div class="container-fluid">

    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('user.leaderboards.index') }}" class="text-decoration-none">
                                <iconify-icon icon="iconamoon:arrow-left-duotone" class="me-1"></iconify-icon>
                                Promotions
                            </a>
                        </li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    @if($leaderboard->isActive())
                        <span class="badge bg-success">
                            <iconify-icon icon="iconamoon:clock-duotone" class="me-1"></iconify-icon>
                            Live
                        </span>
                    @endif
                    <small class="text-muted">Updated: <span id="last-updated">{{ now()->format('H:i') }}</span></small>
                    @if($leaderboard->isActive())
                        <button class="btn btn-sm btn-outline-primary" onclick="refreshLeaderboard()">
                            <iconify-icon icon="streamline-freehand:synchronize-arrows"></iconify-icon>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center">
                    <iconify-icon icon="akar-icons:trophy" class="fs-4 text-warning me-2"></iconify-icon>
                    <div>
                        <h5 class="mb-0">{{ $leaderboard->title }}</h5>
                        <div class="d-flex align-items-center flex-wrap gap-2 mt-1">
                            <span class="badge bg-{{ $leaderboard->isActive() ? 'success' : ($leaderboard->isCompleted() ? 'secondary' : 'warning') }}">
                                {{ ucfirst($leaderboard->status) }}
                            </span>
                            <span class="badge bg-{{ $leaderboard->type === 'target' ? 'info' : 'primary' }}">
                                {{ $leaderboard->type_display }}
                            </span>
                            @if($leaderboard->isActive())
                                <span class="text-dark fw-medium" id="countdown-timer" data-end="{{ $leaderboard->end_date->toIso8601String() }}">
                                    Loading...
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-end d-none d-md-block">
                    <div class="fs-4 fw-bold text-success">${{ number_format($stats['total_prize_amount']) }}</div>
                    <small class="text-muted">Total Prize Pool</small>
                </div>
            </div>
            
            @if($userPosition)
            <div class="mt-2 pt-2 border-top">
                <div class="d-flex align-items-center flex-wrap gap-3">
                    <span class="fw-bold">Your Stats:</span>
                    <span class="badge bg-primary text-white">Rank #{{ $userPosition->position }}</span>
                    <span class="text-muted">|</span>
                    <a href="{{ route('referrals.index') }}" class="badge bg-info text-white text-decoration-none">{{ $userPosition->referral_count }} Referrals</a>
                    @if($leaderboard->type === 'target')
                        @php 
                            $userReferrals = $userPosition->referral_count;
                            $achievedTier = $leaderboard->getTierForReferralCount($userReferrals);
                            $nextTier = $leaderboard->getNextTierForReferralCount($userReferrals);
                        @endphp
                        @if($achievedTier)
                            <span class="badge bg-success">
                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="me-1"></iconify-icon>
                                Tier {{ $achievedTier['target'] }} Achieved - ${{ number_format($achievedTier['amount']) }}
                            </span>
                            @if($nextTier)
                                <span class="text-muted small">
                                    ({{ $nextTier['target'] - $userReferrals }} more for next tier: ${{ number_format($nextTier['amount']) }})
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <iconify-icon icon="iconamoon:star-duotone" class="me-1"></iconify-icon>
                                    Max Tier Reached!
                                </span>
                            @endif
                        @else
                            @php $minTarget = $leaderboard->getMinimumTargetReferrals(); @endphp
                            <span class="text-muted small">({{ $minTarget - $userReferrals }} to T1)</span>
                        @endif
                    @endif
                </div>
            </div>
            @endif
        </div>

        @if($leaderboard->type === 'target')
        @php 
            $userRefs = $userPosition ? $userPosition->referral_count : 0;
            $userAchievedTier = $leaderboard->getTierForReferralCount($userRefs);
            $userNextTier = $leaderboard->getNextTierForReferralCount($userRefs);
            $userMinTarget = $leaderboard->getMinimumTargetReferrals();
            $userTiers = $leaderboard->getSortedTiers();
        @endphp
        <div class="card-body border-bottom bg-warning-subtle py-3 px-2 px-md-3">
            <h6 class="text-muted mb-2 px-1">
                <iconify-icon icon="iconamoon:user-duotone" class="me-1"></iconify-icon>
                Your Stats
            </h6>
            <div class="row g-2">
                <div class="col-4">
                    <div class="bg-white rounded p-2 text-center border h-100">
                        <div class="fw-bold text-primary fs-5">{{ $userRefs }}</div>
                        <small class="text-muted" style="font-size: 0.7rem;">Referrals</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-white rounded p-2 text-center border h-100">
                        @if($userAchievedTier)
                            <div class="fw-bold text-success fs-5">${{ number_format($userAchievedTier['amount']) }}</div>
                            <small class="text-success" style="font-size: 0.7rem;">Prize</small>
                        @else
                            <div class="fw-bold text-muted fs-5">$0</div>
                            <small class="text-muted" style="font-size: 0.7rem;">Not Qualified</small>
                        @endif
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-white rounded p-2 text-center border h-100">
                        @if($userNextTier)
                            <div class="fw-bold text-info fs-5">{{ $userNextTier['target'] - $userRefs }}</div>
                            <small class="text-muted" style="font-size: 0.7rem;">to T{{ array_search($userNextTier, $userTiers) + 1 }}</small>
                        @elseif($userAchievedTier)
                            <div class="fw-bold text-warning fs-5">
                                <iconify-icon icon="iconamoon:star-duotone"></iconify-icon>
                            </div>
                            <small class="text-warning" style="font-size: 0.7rem;">Max!</small>
                        @else
                            <div class="fw-bold text-info fs-5">{{ $userMinTarget - $userRefs }}</div>
                            <small class="text-muted" style="font-size: 0.7rem;">to T1</small>
                        @endif
                    </div>
                </div>
            </div>
            @if($userNextTier || !$userAchievedTier)
            <div class="mt-3">
                @php
                    $targetForProgress = $userNextTier ? $userNextTier['target'] : $userMinTarget;
                    $progressPercent = min(100, ($userRefs / max($targetForProgress, 1)) * 100);
                @endphp
                <div class="d-flex justify-content-between mb-1">
                    <small class="text-muted">Progress to {{ $userNextTier ? 'Tier ' . (array_search($userNextTier, $userTiers) + 1) : 'Tier 1' }}</small>
                    <small class="fw-bold">{{ $userRefs }}/{{ $targetForProgress }} ({{ number_format($progressPercent, 0) }}%)</small>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-{{ $userAchievedTier ? 'primary' : 'info' }}" style="width: {{ $progressPercent }}%"></div>
                </div>
            </div>
            @endif
        </div>
        @endif

        <div class="card-body p-0">
            @if($topPositions->count() > 0)

                @if($leaderboard->type === 'competitive' && $topPositions->count() >= 3)
                <div class="podium-section p-4 bg-gradient-light border-bottom">
                    <div class="podium-container">
                        <div class="podium-position podium-second">
                            <div class="podium-content">
                                <div class="podium-info">
                                    <h6 class="podium-name">{{ $topPositions[1]->user->first_name }} {{ Str::limit($topPositions[1]->user->last_name, 6) }}</h6>
                                </div>
                                <div class="podium-avatar bg-secondary">
                                    <iconify-icon icon="noto:2nd-place-medal" class="podium-medal-icon"></iconify-icon>
                                </div>
                                <div class="podium-stats">
                                    <div class="podium-score text-secondary fw-bold">{{ $topPositions[1]->referral_count }} refs</div>
                                    <small class="podium-prize text-success fw-bold">${{ number_format($topPositions[1]->prize_amount) }}</small>
                                </div>
                            </div>
                            <div class="podium-base podium-silver"></div>
                        </div>
                        
                        <div class="podium-position podium-first">
                            <div class="podium-content winner-glow">
                                <div class="podium-info">
                                    <h6 class="podium-name fw-bold">{{ $topPositions[0]->user->first_name }} {{ Str::limit($topPositions[0]->user->last_name, 6) }}</h6>
                                </div>
                                <div class="podium-avatar bg-warning winner">
                                    <iconify-icon icon="noto:1st-place-medal" class="podium-medal-icon podium-medal-gold"></iconify-icon>
                                </div>
                                <div class="podium-stats">
                                    <div class="podium-score text-warning fw-bold fs-5">{{ $topPositions[0]->referral_count }} refs</div>
                                    <small class="podium-prize text-success fw-bold fs-6">${{ number_format($topPositions[0]->prize_amount) }}</small>
                                </div>
                            </div>
                            <div class="podium-base podium-gold"></div>
                        </div>
                        
                        <div class="podium-position podium-third">
                            <div class="podium-content">
                                <div class="podium-info">
                                    <h6 class="podium-name">{{ $topPositions[2]->user->first_name }} {{ Str::limit($topPositions[2]->user->last_name, 6) }}</h6>
                                </div>
                                <div class="podium-avatar bg-info">
                                    <iconify-icon icon="noto:3rd-place-medal" class="podium-medal-icon"></iconify-icon>
                                </div>
                                <div class="podium-stats">
                                    <div class="podium-score text-info fw-bold">{{ $topPositions[2]->referral_count }} refs</div>
                                    <small class="podium-prize text-success fw-bold">${{ number_format($topPositions[2]->prize_amount) }}</small>
                                </div>
                            </div>
                            <div class="podium-base podium-bronze"></div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="leaderboard-list" id="rankings-tbody">
                    @foreach($topPositions as $position)
                    @php
                        $isCurrentUser = $position->user_id == $user->id;
                        $isTopThree = $position->position <= 3 && $leaderboard->type === 'competitive';
                        $rankPos = $position->position;
                        $rankOrdinal = match(true) {
                            $rankPos % 100 >= 11 && $rankPos % 100 <= 13 => $rankPos . 'th',
                            $rankPos % 10 == 1 => $rankPos . 'st',
                            $rankPos % 10 == 2 => $rankPos . 'nd',
                            $rankPos % 10 == 3 => $rankPos . 'rd',
                            default => $rankPos . 'th'
                        };
                    @endphp
                    <div class="leaderboard-item p-3 border-bottom {{ $isCurrentUser ? 'bg-warning-subtle' : '' }}">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                <div class="rank-badge me-3 text-center" style="min-width: 45px;">
                                    @if($isTopThree)
                                        <iconify-icon icon="iconamoon:{{ $position->position == 1 ? 'crown' : 'medal' }}-duotone" 
                                            class="fs-5 text-{{ $position->position == 1 ? 'warning' : ($position->position == 2 ? 'secondary' : 'info') }}"></iconify-icon>
                                    @endif
                                    <div class="fw-bold {{ $isTopThree ? 'text-primary' : 'text-muted' }}">{{ $rankOrdinal }}</div>
                                </div>
                                <div class="user-info flex-grow-1" style="min-width: 0;">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="fw-medium text-truncate">{{ $position->user->first_name }} {{ Str::limit($position->user->last_name, 8) }}</span>
                                        @if($isCurrentUser)
                                            <span class="badge bg-warning ms-1">You</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center mt-1">
                                        <span class="badge bg-info me-2">{{ $position->referral_count }} refs</span>
                                        @if($leaderboard->type === 'target')
                                            @php
                                                $refCount = $position->referral_count;
                                                $achievedTier = $leaderboard->getTierForReferralCount($refCount);
                                                $nextTier = $leaderboard->getNextTierForReferralCount($refCount);
                                                $minTarget = $leaderboard->getMinimumTargetReferrals();
                                                $sortedTiers = $leaderboard->getSortedTiers();
                                                $targetForProgress = $nextTier ? $nextTier['target'] : ($achievedTier ? $achievedTier['target'] : $minTarget);
                                                $progress = min(100, ($refCount / max($targetForProgress, 1)) * 100);
                                            @endphp
                                            @if($achievedTier)
                                                <span class="badge bg-success">
                                                    <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                    T{{ array_search($achievedTier, $sortedTiers) + 1 }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="text-end ms-2">
                                @if($leaderboard->type === 'target')
                                    @if($achievedTier && !$nextTier)
                                        <div class="fw-bold text-success">${{ number_format($achievedTier['amount']) }}</div>
                                        <small class="text-warning">MAX</small>
                                    @elseif($achievedTier)
                                        <div class="fw-bold text-success">${{ number_format($achievedTier['amount']) }}</div>
                                        <small class="text-muted">{{ $nextTier['target'] - $refCount }} to T{{ array_search($nextTier, $sortedTiers) + 1 }}</small>
                                    @else
                                        <div class="text-muted">$0</div>
                                        <small class="text-muted">{{ $minTarget - $refCount }} to T1</small>
                                    @endif
                                @else
                                    <div class="fw-bold text-success">${{ number_format($position->prize_amount) }}</div>
                                    @if($position->prize_awarded)
                                        <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success" title="Awarded"></iconify-icon>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @if($leaderboard->type === 'target' && ($nextTier || !$achievedTier))
                        <div class="mt-2">
                            <div class="progress" style="height: 4px;">
                                <div class="progress-bar bg-{{ $achievedTier ? 'primary' : 'info' }}" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">{{ $refCount }}/{{ $targetForProgress }}</small>
                                <small class="text-muted">{{ number_format($progress, 0) }}%</small>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                @if($paginatedPositions && $paginatedPositions->total() > 0)
                <div class="border-top">
                    <div class="p-3 bg-light d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#moreEntriesSection" style="cursor: pointer;">
                        <span class="fw-medium">
                            <iconify-icon icon="iconamoon:arrow-down-2-duotone" class="me-2 collapse-arrow"></iconify-icon>
                            View More Participants ({{ $paginatedPositions->total() }} more)
                        </span>
                        <span class="badge bg-primary">{{ $paginatedPositions->currentPage() }} / {{ $paginatedPositions->lastPage() }}</span>
                    </div>
                    <div class="collapse {{ request()->has('page') ? 'show' : '' }}" id="moreEntriesSection">
                        <div class="leaderboard-list">
                            @foreach($paginatedPositions as $position)
                            @php
                                $isCurrentUser = $position->user_id == $user->id;
                                $rankPos = $position->position;
                                $rankOrdinal = match(true) {
                                    $rankPos % 100 >= 11 && $rankPos % 100 <= 13 => $rankPos . 'th',
                                    $rankPos % 10 == 1 => $rankPos . 'st',
                                    $rankPos % 10 == 2 => $rankPos . 'nd',
                                    $rankPos % 10 == 3 => $rankPos . 'rd',
                                    default => $rankPos . 'th'
                                };
                            @endphp
                            <div class="leaderboard-item p-3 border-bottom {{ $isCurrentUser ? 'bg-warning-subtle' : '' }}">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
                                        <div class="rank-badge me-3 text-center" style="min-width: 45px;">
                                            <div class="fw-bold text-muted">{{ $rankOrdinal }}</div>
                                        </div>
                                        <div class="user-info flex-grow-1" style="min-width: 0;">
                                            <div class="d-flex align-items-center flex-wrap">
                                                <span class="fw-medium text-truncate">{{ $position->user->first_name }} {{ Str::limit($position->user->last_name, 8) }}</span>
                                                @if($isCurrentUser)
                                                    <span class="badge bg-warning ms-1">You</span>
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center mt-1">
                                                <span class="badge bg-info me-2">{{ $position->referral_count }} refs</span>
                                                @if($leaderboard->type === 'target')
                                                    @php
                                                        $refCount = $position->referral_count;
                                                        $achievedTier = $leaderboard->getTierForReferralCount($refCount);
                                                        $nextTier = $leaderboard->getNextTierForReferralCount($refCount);
                                                        $minTarget = $leaderboard->getMinimumTargetReferrals();
                                                        $sortedTiers = $leaderboard->getSortedTiers();
                                                        $targetForProgress = $nextTier ? $nextTier['target'] : ($achievedTier ? $achievedTier['target'] : $minTarget);
                                                        $progress = min(100, ($refCount / max($targetForProgress, 1)) * 100);
                                                    @endphp
                                                    @if($achievedTier)
                                                        <span class="badge bg-success">
                                                            <iconify-icon icon="iconamoon:check-circle-1-duotone"></iconify-icon>
                                                            T{{ array_search($achievedTier, $sortedTiers) + 1 }}
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-end ms-2">
                                        @if($leaderboard->type === 'target')
                                            @if($achievedTier && !$nextTier)
                                                <div class="fw-bold text-success">${{ number_format($achievedTier['amount']) }}</div>
                                                <small class="text-warning">MAX</small>
                                            @elseif($achievedTier)
                                                <div class="fw-bold text-success">${{ number_format($achievedTier['amount']) }}</div>
                                                <small class="text-muted">{{ $nextTier['target'] - $refCount }} to T{{ array_search($nextTier, $sortedTiers) + 1 }}</small>
                                            @else
                                                <div class="text-muted">$0</div>
                                                <small class="text-muted">{{ $minTarget - $refCount }} to T1</small>
                                            @endif
                                        @else
                                            <div class="fw-bold text-success">${{ number_format($position->prize_amount) }}</div>
                                            @if($position->prize_awarded)
                                                <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success" title="Awarded"></iconify-icon>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                @if($leaderboard->type === 'target' && ($nextTier || !$achievedTier))
                                <div class="mt-2">
                                    <div class="progress" style="height: 4px;">
                                        <div class="progress-bar bg-{{ $achievedTier ? 'primary' : 'info' }}" style="width: {{ $progress }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <small class="text-muted">{{ $refCount }}/{{ $targetForProgress }}</small>
                                        <small class="text-muted">{{ number_format($progress, 0) }}%</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @if($paginatedPositions->lastPage() > 1)
                        <div class="card-footer d-flex justify-content-center py-3">
                            {{ $paginatedPositions->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                @endif

            @else
                <div class="text-center py-5">
                    <iconify-icon icon="iconamoon:chart-line-duotone" class="fs-1 text-muted mb-3"></iconify-icon>
                    <h6 class="text-muted">Leaderboard Updating</h6>
                    <p class="text-muted mb-0">Rankings will appear as users make progress with their referrals.</p>
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#detailsSection" style="cursor: pointer;">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <iconify-icon icon="iconamoon:information-circle-duotone" class="me-2"></iconify-icon>
                    Promotion Details
                </h6>
                <iconify-icon icon="iconamoon:arrow-down-2-duotone" class="collapse-icon"></iconify-icon>
            </div>
        </div>
        <div class="collapse show" id="detailsSection">
            <div class="card-body">
                @if($leaderboard->description)
                <div class="mb-3">
                    <label class="text-muted small">Description</label>
                    <p class="mb-0">{{ $leaderboard->description }}</p>
                </div>
                @endif

                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <label class="text-muted small">Start Date</label>
                        <div class="fw-medium">{{ $leaderboard->start_date->format('M d, Y') }}</div>
                        <small class="text-muted">{{ $leaderboard->start_date->format('H:i') }}</small>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="text-muted small">End Date</label>
                        <div class="fw-medium">{{ $leaderboard->end_date->format('M d, Y') }}</div>
                        <small class="text-muted">{{ $leaderboard->end_date->format('H:i') }}</small>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="text-muted small">Referral Type</label>
                        <div class="fw-medium">{{ $leaderboard->referral_type_display }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="text-muted small">Status</label>
                        <div>
                            <span class="badge bg-{{ $leaderboard->isActive() ? 'success' : ($leaderboard->isCompleted() ? 'secondary' : 'warning') }}">
                                {{ ucfirst($leaderboard->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($leaderboard->min_investment_amount)
                <div class="mt-3 p-2 bg-warning-subtle rounded">
                    <small class="text-warning fw-medium">
                        Minimum investment of ${{ number_format($leaderboard->min_investment_amount) }} required for referrals to count
                    </small>
                </div>
                @endif

                @if($leaderboard->type === 'competitive' && $leaderboard->prize_structure)
                <div class="mt-4">
                    <label class="text-muted small mb-2 d-block">Prize Structure</label>
                    <div class="row g-2">
                        @foreach($leaderboard->prize_structure as $prize)
                        @php
                            $pos = (int) $prize['position'];
                            $ordinal = match(true) {
                                $pos % 100 >= 11 && $pos % 100 <= 13 => $pos . 'th',
                                $pos % 10 == 1 => $pos . 'st',
                                $pos % 10 == 2 => $pos . 'nd',
                                $pos % 10 == 3 => $pos . 'rd',
                                default => $pos . 'th'
                            };
                            $bgClass = match($pos) {
                                1 => 'bg-primary',
                                2 => 'bg-dark',
                                3 => 'text-white',
                                default => 'bg-light text-dark'
                            };
                            $customStyle = $pos === 3 ? 'background-color: #6f42c1 !important;' : '';
                        @endphp
                        <div class="col-auto">
                            <div class="badge {{ $bgClass }} px-3 py-2" @if($customStyle) style="{{ $customStyle }}" @endif>
                                {{ $ordinal }}: ${{ number_format($prize['amount']) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                
                @if($leaderboard->type === 'target')
                @php $tiers = $leaderboard->getSortedTiers(); @endphp
                <div class="mt-4">
                    <label class="text-muted small mb-2 d-block">Reward Tiers</label>
                    <div class="row g-2">
                        @foreach($tiers as $index => $tier)
                        @php
                            $tierNum = $index + 1;
                            $bgClass = match($tierNum) {
                                1 => 'bg-primary',
                                2 => 'bg-dark',
                                3 => 'text-white',
                                default => 'bg-secondary'
                            };
                            $customStyle = $tierNum === 3 ? 'background-color: #6f42c1 !important;' : '';
                        @endphp
                        <div class="col-auto">
                            <div class="badge {{ $bgClass }} px-3 py-2" @if($customStyle) style="{{ $customStyle }}" @endif>
                                {{ $tier['target'] }} referrals = ${{ number_format($tier['amount']) }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Users earn the highest tier they achieve. Rewards are distributed after the promotion ends.</small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>


</div>
@endsection

@section('script')
<script>
let refreshInterval;
let countdownInterval;

document.addEventListener('DOMContentLoaded', function() {
    @if($leaderboard->isActive())
    refreshInterval = setInterval(function() {
        if (document.visibilityState === 'visible') {
            silentRefresh();
        }
    }, 60000);
    
    // Initialize countdown timer
    initCountdown();
    @endif
});

function initCountdown() {
    const timerEl = document.getElementById('countdown-timer');
    if (!timerEl) return;
    
    const endDate = new Date(timerEl.dataset.end);
    
    function updateTimer() {
        const now = new Date();
        const diff = endDate - now;
        
        if (diff <= 0) {
            timerEl.textContent = 'Ended';
            timerEl.classList.remove('text-dark', 'text-danger');
            timerEl.classList.add('text-muted');
            clearInterval(countdownInterval);
            return;
        }
        
        const totalHours = Math.floor(diff / (1000 * 60 * 60));
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        // Change color to red when less than 24 hours remaining
        if (totalHours < 24) {
            timerEl.classList.remove('text-dark');
            timerEl.classList.add('text-danger');
        } else {
            timerEl.classList.remove('text-danger');
            timerEl.classList.add('text-dark');
        }
        
        let timeStr = '';
        if (days > 0) {
            timeStr = `${days}d ${hours}h ${minutes}m ${seconds}s (${totalHours}h total)`;
        } else if (hours > 0) {
            timeStr = `${hours}h ${minutes}m ${seconds}s`;
        } else {
            timeStr = `${minutes}m ${seconds}s`;
        }
        
        timerEl.textContent = timeStr + ' left';
    }
    
    updateTimer();
    countdownInterval = setInterval(updateTimer, 1000);
}

function refreshLeaderboard() {
    window.location.reload();
}

function silentRefresh() {
    fetch('{{ route("user.leaderboards.api.live-updates") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            leaderboard_ids: [{{ $leaderboard->id }}]
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('last-updated').textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
    })
    .catch(error => console.error('Refresh error:', error));
}
</script>

<style>
.podium-container {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 1rem;
    min-height: 180px;
}

.podium-position {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100px;
}

.podium-content {
    text-align: center;
    padding: 0.75rem;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 0.5rem;
    width: 100%;
}

.podium-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    position: relative;
    background: transparent !important;
}

.podium-medal-icon {
    font-size: 48px;
    line-height: 1;
}

.podium-medal-gold {
    font-size: 56px;
}

.podium-name {
    font-size: 0.8rem;
    margin-bottom: 0.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.podium-score {
    font-size: 0.9rem;
}

.podium-prize {
    font-size: 0.85rem;
}

.podium-base {
    width: 100%;
    border-radius: 4px 4px 0 0;
}

.podium-gold {
    height: 60px;
    background: linear-gradient(180deg, #ffd700 0%, #ffb300 100%);
}

.podium-silver {
    height: 45px;
    background: linear-gradient(180deg, #c0c0c0 0%, #a0a0a0 100%);
}

.podium-bronze {
    height: 35px;
    background: linear-gradient(180deg, #cd7f32 0%, #a86828 100%);
}

.podium-first {
    order: 2;
}

.podium-second {
    order: 1;
}

.podium-third {
    order: 3;
}

.winner-glow {
    box-shadow: 0 0 20px rgba(255, 193, 7, 0.3);
}

.avatar-sm {
    width: 32px;
    height: 32px;
    min-width: 32px;
}

.collapse-icon {
    transition: transform 0.3s ease;
}

.collapsed .collapse-icon {
    transform: rotate(-90deg);
}

.table-warning {
    --bs-table-bg: rgba(255, 193, 7, 0.15);
}

@media (max-width: 576px) {
    .podium-container {
        gap: 0.5rem;
    }
    
    .podium-position {
        width: 80px;
    }
    
    .podium-name {
        font-size: 0.7rem;
    }
    
    .podium-score {
        font-size: 0.8rem;
    }
}
</style>
@endsection
