@extends('layouts.vertical', ['title' => 'Promotions', 'subTitle' => 'Referral Competitions'])

@section('content')
<div class="container-fluid">

    @if(isset($error))
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        {{ $error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">Promotions</h4>
                    <p class="text-muted mb-0">Compete for prizes based on your referrals</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <small class="text-muted">Last updated: <span id="last-updated">{{ now()->format('H:i') }}</span></small>
                    <button class="btn btn-sm btn-outline-primary" onclick="refreshPage()">
                        <iconify-icon icon="streamline-freehand:synchronize-arrows"></iconify-icon>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($activeLeaderboards->count() > 0)
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-success me-2">{{ $activeLeaderboards->count() }} Active</span>
            <h5 class="mb-0">Active Competitions</h5>
        </div>
        
        <div class="row g-3">
            @foreach($activeLeaderboards as $leaderboard)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-success h-100 promotion-card" onclick="window.location='{{ route('user.leaderboards.show', $leaderboard) }}'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1 fw-bold">{{ $leaderboard->title }}</h6>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge bg-success">Active</span>
                                    <span class="badge bg-{{ $leaderboard->type === 'target' ? 'info' : 'primary' }}">
                                        {{ $leaderboard->type_display }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-bold text-success">${{ number_format($leaderboard->total_prize_amount ?? 0) }}</div>
                                <small class="text-muted">Prize Pool</small>
                            </div>
                        </div>

                        @if($leaderboard->type === 'competitive' && $leaderboard->prize_structure)
                        <div class="d-flex justify-content-between gap-1 mb-3">
                            @foreach(array_slice($leaderboard->prize_structure, 0, 3) as $prize)
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
                            <span class="badge {{ $bgClass }} px-2 py-1 flex-fill text-center" @if($customStyle) style="{{ $customStyle }}" @endif>{{ $ordinal }}: ${{ number_format($prize['amount']) }}</span>
                            @endforeach
                        </div>
                        @endif

                        @if($leaderboard->type === 'target')
                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                            @php $tiers = array_slice($leaderboard->getSortedTiers(), 0, 3); @endphp
                            @foreach($tiers as $index => $tier)
                            @php
                                $tierNum = $index + 1;
                                $bgClass = match($tierNum) {
                                    1 => 'bg-primary',
                                    2 => 'bg-dark',
                                    3 => 'text-white',
                                    default => 'bg-light text-dark'
                                };
                                $customStyle = $tierNum === 3 ? 'background-color: #6f42c1 !important;' : '';
                            @endphp
                            <span class="badge {{ $bgClass }} px-2 py-1 text-center text-nowrap" style="font-size: 0.7rem; @if($customStyle){{ $customStyle }}@endif">{{ $tier['target'] }} = ${{ number_format($tier['amount']) }}</span>
                            @endforeach
                            @if(count($leaderboard->getSortedTiers()) > 3)
                            <span class="badge bg-secondary px-2 py-1 text-nowrap" style="font-size: 0.7rem;">+{{ count($leaderboard->getSortedTiers()) - 3 }}</span>
                            @endif
                        </div>
                        @endif

                        <div class="bg-success-subtle rounded p-2 text-center mb-3">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <iconify-icon icon="iconamoon:clock-duotone" class="text-success"></iconify-icon>
                                <span class="fw-medium countdown-timer" data-end="{{ $leaderboard->end_date->toIso8601String() }}" data-type="ends">
                                    {{ $leaderboard->days_remaining }}d remaining
                                </span>
                            </div>
                            <small class="text-muted">{{ $leaderboard->start_date->format('M d') }} - {{ $leaderboard->end_date->format('M d, Y') }}</small>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('user.leaderboards.show', $leaderboard) }}" class="btn btn-primary btn-sm w-100" onclick="event.stopPropagation();">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(isset($upcomingLeaderboards) && $upcomingLeaderboards->count() > 0)
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-warning me-2">{{ $upcomingLeaderboards->count() }}</span>
            <h5 class="mb-0">Upcoming</h5>
        </div>
        
        <div class="row g-3">
            @foreach($upcomingLeaderboards as $leaderboard)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-warning h-100 promotion-card" onclick="window.location='{{ route('user.leaderboards.show', $leaderboard) }}'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1 fw-bold">{{ $leaderboard->title }}</h6>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge bg-warning text-dark">Upcoming</span>
                                    <span class="badge bg-{{ $leaderboard->type === 'target' ? 'info' : 'primary' }}">
                                        {{ $leaderboard->type_display }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-bold text-success">${{ number_format($leaderboard->total_prize_amount ?? 0) }}</div>
                                <small class="text-muted">Prize Pool</small>
                            </div>
                        </div>

                        @if($leaderboard->type === 'competitive' && $leaderboard->prize_structure)
                        <div class="d-flex justify-content-between gap-1 mb-3">
                            @foreach(array_slice($leaderboard->prize_structure, 0, 3) as $prize)
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
                            <span class="badge {{ $bgClass }} px-2 py-1 flex-fill text-center" @if($customStyle) style="{{ $customStyle }}" @endif>{{ $ordinal }}: ${{ number_format($prize['amount']) }}</span>
                            @endforeach
                        </div>
                        @endif

                        @if($leaderboard->type === 'target')
                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                            @php $tiers = array_slice($leaderboard->getSortedTiers(), 0, 3); @endphp
                            @foreach($tiers as $index => $tier)
                            @php
                                $tierNum = $index + 1;
                                $bgClass = match($tierNum) {
                                    1 => 'bg-primary',
                                    2 => 'bg-dark',
                                    3 => 'text-white',
                                    default => 'bg-light text-dark'
                                };
                                $customStyle = $tierNum === 3 ? 'background-color: #6f42c1 !important;' : '';
                            @endphp
                            <span class="badge {{ $bgClass }} px-2 py-1 text-center text-nowrap" style="font-size: 0.7rem; @if($customStyle){{ $customStyle }}@endif">{{ $tier['target'] }} = ${{ number_format($tier['amount']) }}</span>
                            @endforeach
                            @if(count($leaderboard->getSortedTiers()) > 3)
                            <span class="badge bg-secondary px-2 py-1 text-nowrap" style="font-size: 0.7rem;">+{{ count($leaderboard->getSortedTiers()) - 3 }}</span>
                            @endif
                        </div>
                        @endif

                        <div class="bg-warning-subtle rounded p-2 text-center mb-3">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <iconify-icon icon="iconamoon:clock-duotone" class="text-warning"></iconify-icon>
                                <span class="fw-medium countdown-timer" data-end="{{ $leaderboard->start_date->toIso8601String() }}" data-type="starts">
                                    @php $daysUntilStart = (int) now()->startOfDay()->diffInDays($leaderboard->start_date->startOfDay(), false); @endphp
                                    Starts in {{ max(0, $daysUntilStart) }}d
                                </span>
                            </div>
                            <small class="text-muted">{{ $leaderboard->start_date->format('M d') }} - {{ $leaderboard->end_date->format('M d, Y') }}</small>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('user.leaderboards.show', $leaderboard) }}" class="btn btn-warning btn-sm w-100" onclick="event.stopPropagation();">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($completedLeaderboards->count() > 0)
    <div class="mb-4">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-secondary me-2">{{ $completedLeaderboards->count() }}</span>
            <h5 class="mb-0">Completed</h5>
        </div>
        
        <div class="row g-3">
            @foreach($completedLeaderboards as $leaderboard)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-secondary h-100 promotion-card" onclick="window.location='{{ route('user.leaderboards.show', $leaderboard) }}'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1 fw-bold">{{ $leaderboard->title }}</h6>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge bg-secondary">Completed</span>
                                    <span class="badge bg-{{ $leaderboard->type === 'target' ? 'info' : 'primary' }}">
                                        {{ $leaderboard->type_display }}
                                    </span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-bold text-success">${{ number_format($leaderboard->total_prize_amount ?? 0) }}</div>
                                <small class="text-muted">Prize Pool</small>
                            </div>
                        </div>

                        {{-- Champion Podium --}}
                        @if($leaderboard->positions->count() >= 3)
                        @php
                            $first = $leaderboard->positions->where('position', 1)->first();
                            $second = $leaderboard->positions->where('position', 2)->first();
                            $third = $leaderboard->positions->where('position', 3)->first();
                        @endphp
                        <div class="podium-section-mini bg-gradient-light rounded mb-3">
                            <div class="podium-container-mini">
                                <div class="podium-position-mini podium-second-mini">
                                    <div class="podium-content-mini">
                                        <div class="podium-info-mini">
                                            <h6 class="podium-name-mini">{{ $second->user->first_name ?? 'N/A' }}</h6>
                                        </div>
                                        <div class="podium-avatar-mini">
                                            <iconify-icon icon="noto:2nd-place-medal" class="podium-medal-icon-mini"></iconify-icon>
                                        </div>
                                        <div class="podium-stats-mini">
                                            <div class="podium-score-mini text-secondary fw-bold">{{ $second->referral_count }} refs</div>
                                            <small class="podium-prize-mini text-success fw-bold">${{ number_format($second->prize_amount) }}</small>
                                        </div>
                                    </div>
                                    <div class="podium-base-mini podium-silver-mini"></div>
                                </div>
                                
                                <div class="podium-position-mini podium-first-mini">
                                    <div class="podium-content-mini winner-glow-mini">
                                        <div class="podium-info-mini">
                                            <h6 class="podium-name-mini fw-bold">{{ $first->user->first_name ?? 'N/A' }}</h6>
                                        </div>
                                        <div class="podium-avatar-mini winner-mini">
                                            <iconify-icon icon="noto:1st-place-medal" class="podium-medal-icon-mini podium-medal-gold-mini"></iconify-icon>
                                        </div>
                                        <div class="podium-stats-mini">
                                            <div class="podium-score-mini text-warning fw-bold">{{ $first->referral_count }} refs</div>
                                            <small class="podium-prize-mini text-success fw-bold">${{ number_format($first->prize_amount) }}</small>
                                        </div>
                                    </div>
                                    <div class="podium-base-mini podium-gold-mini"></div>
                                </div>
                                
                                <div class="podium-position-mini podium-third-mini">
                                    <div class="podium-content-mini">
                                        <div class="podium-info-mini">
                                            <h6 class="podium-name-mini">{{ $third->user->first_name ?? 'N/A' }}</h6>
                                        </div>
                                        <div class="podium-avatar-mini">
                                            <iconify-icon icon="noto:3rd-place-medal" class="podium-medal-icon-mini"></iconify-icon>
                                        </div>
                                        <div class="podium-stats-mini">
                                            <div class="podium-score-mini text-info fw-bold">{{ $third->referral_count }} refs</div>
                                            <small class="podium-prize-mini text-success fw-bold">${{ number_format($third->prize_amount) }}</small>
                                        </div>
                                    </div>
                                    <div class="podium-base-mini podium-bronze-mini"></div>
                                </div>
                            </div>
                        </div>
                        @elseif($leaderboard->positions->count() > 0)
                        <div class="bg-light rounded p-2 mb-3 text-center">
                            <small class="text-muted">{{ $leaderboard->positions->count() }} participant(s)</small>
                        </div>
                        @else
                        <div class="bg-light rounded p-3 mb-3 text-center">
                            <small class="text-muted">No winners recorded</small>
                        </div>
                        @endif

                        <div class="bg-secondary-subtle rounded p-2 text-center mb-3">
                            <small class="text-muted">
                                {{ $leaderboard->start_date->format('M d') }} - {{ $leaderboard->end_date->format('M d, Y') }}
                            </small>
                        </div>

                        <div class="mt-3 text-center">
                            <a href="{{ route('user.leaderboards.show', $leaderboard) }}" class="btn btn-outline-secondary btn-sm w-100" onclick="event.stopPropagation();">
                                View Results
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($activeLeaderboards->count() == 0 && $completedLeaderboards->count() == 0)
    <div class="card">
        <div class="card-body text-center py-5">
            <iconify-icon icon="akar-icons:trophy" class="fs-1 text-muted mb-3"></iconify-icon>
            <h5 class="text-muted">No Promotions Available</h5>
            <p class="text-muted mb-3">Check back later for new competitions</p>
            <button class="btn btn-outline-primary" onclick="refreshPage()">
                <iconify-icon icon="iconamoon:refresh" class="me-1"></iconify-icon>
                Check for Updates
            </button>
        </div>
    </div>
    @endif

    @if($userRankings && count($userRankings) > 0)
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <iconify-icon icon="iconamoon:star-duotone" class="me-2 text-warning"></iconify-icon>
                Your Current Rankings
            </h5>
        </div>
        <div class="card-body p-0">
            @foreach($userRankings as $ranking)
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom" onclick="window.location='{{ route('user.leaderboards.show', $ranking['leaderboard']) }}'" style="cursor: pointer;">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        @if($ranking['position'])
                            <span class="badge bg-{{ $ranking['position']->position <= 3 ? 'warning' : 'secondary' }} fs-6">
                                #{{ $ranking['position']->position }}
                            </span>
                        @else
                            <span class="badge bg-light text-muted">-</span>
                        @endif
                    </div>
                    <div>
                        <div class="fw-medium">{{ Str::limit($ranking['leaderboard']->title, 30) }}</div>
                        <div class="d-flex flex-wrap gap-2 mt-1">
                            <small class="text-info fw-bold">{{ $ranking['position']->referral_count ?? $ranking['referral_count'] ?? 0 }} refs</small>
                            @if($ranking['leaderboard']->days_remaining > 0)
                                <small class="text-warning">{{ $ranking['leaderboard']->days_remaining }}d left</small>
                            @else
                                <small class="text-secondary">Ended</small>
                            @endif
                        </div>
                    </div>
                </div>
                <iconify-icon icon="iconamoon:arrow-right-2" class="text-muted"></iconify-icon>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection

@section('script')
<script>
function refreshPage() {
    window.location.reload();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('last-updated').textContent = new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    
    // Initialize countdown timers
    function updateCountdowns() {
        document.querySelectorAll('.countdown-timer').forEach(function(el) {
            const endDate = new Date(el.dataset.end);
            const type = el.dataset.type;
            const now = new Date();
            const diff = endDate - now;
            
            if (diff <= 0) {
                el.textContent = type === 'starts' ? 'Starting now!' : 'Ended';
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            let text = '';
            if (type === 'starts') {
                if (days > 0) {
                    text = `Starts in ${days}d ${hours}h ${minutes}m`;
                } else if (hours > 0) {
                    text = `Starts in ${hours}h ${minutes}m ${seconds}s`;
                } else {
                    text = `Starts in ${minutes}m ${seconds}s`;
                }
            } else {
                if (days > 0) {
                    text = `${days}d ${hours}h ${minutes}m remaining`;
                } else if (hours > 0) {
                    text = `${hours}h ${minutes}m ${seconds}s remaining`;
                } else {
                    text = `${minutes}m ${seconds}s remaining`;
                }
            }
            
            el.textContent = text;
        });
    }
    
    updateCountdowns();
    setInterval(updateCountdowns, 1000);
});
</script>

<style>
.promotion-card {
    cursor: pointer;
    transition: all 0.2s ease;
}

.promotion-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.opacity-85 {
    opacity: 0.85;
}

.podium-section-mini {
    padding: 0.75rem;
}

.podium-container-mini {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 0.25rem;
}

.podium-position-mini {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    max-width: 90px;
}

.podium-content-mini {
    text-align: center;
    padding: 0.5rem 0.25rem;
    background: rgba(255,255,255,0.9);
    border-radius: 8px 8px 0 0;
    width: 100%;
}

.winner-glow-mini {
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.4);
}

.podium-avatar-mini {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.podium-medal-icon-mini {
    font-size: 32px;
    line-height: 1;
}

.podium-medal-gold-mini {
    font-size: 40px;
}

.podium-name-mini {
    font-size: 0.7rem;
    margin-bottom: 0.15rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.podium-score-mini {
    font-size: 0.7rem;
}

.podium-prize-mini {
    font-size: 0.65rem;
}

.podium-base-mini {
    width: 100%;
    border-radius: 4px 4px 0 0;
}

.podium-gold-mini {
    height: 40px;
    background: linear-gradient(180deg, #ffd700 0%, #ffb300 100%);
}

.podium-silver-mini {
    height: 30px;
    background: linear-gradient(180deg, #c0c0c0 0%, #a0a0a0 100%);
}

.podium-bronze-mini {
    height: 22px;
    background: linear-gradient(180deg, #cd7f32 0%, #a86828 100%);
}

.podium-first-mini {
    order: 2;
}

.podium-second-mini {
    order: 1;
}

.podium-third-mini {
    order: 3;
}

@media (max-width: 576px) {
    .podium-container-mini {
        gap: 0.15rem;
    }
    
    .podium-medal-icon-mini {
        font-size: 26px;
    }
    
    .podium-medal-gold-mini {
        font-size: 32px;
    }
    
    .podium-name-mini {
        font-size: 0.6rem;
    }
    
    .podium-score-mini {
        font-size: 0.6rem;
    }
    
    .podium-prize-mini {
        font-size: 0.55rem;
    }
    
    .podium-gold-mini {
        height: 32px;
    }
    
    .podium-silver-mini {
        height: 24px;
    }
    
    .podium-bronze-mini {
        height: 18px;
    }
}
</style>
@endsection
