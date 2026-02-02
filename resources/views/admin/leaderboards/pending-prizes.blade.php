@extends('admin.layouts.vertical', ['title' => 'Pending Prizes', 'subTitle' => 'Review and Approve Leaderboard Prizes'])

@section('content')
<div class="container-fluid">
    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Pending Prize Approvals</h4>
                            <p class="text-muted mb-0">Review and approve prizes for leaderboard winners to claim</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ route('admin.leaderboards.index') }}" class="btn btn-outline-secondary btn-sm">
                                <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                                Back to Leaderboards
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm bg-warning-subtle">
                <div class="card-body">
                    <iconify-icon icon="mdi:trophy-award" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Pending Approval</h6>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($totalPendingCount) }}</h4>
                    <small class="text-muted">Winners awaiting approval</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm bg-danger-subtle">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:dollar-duotone" class="text-danger mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Total Pending</h6>
                    <h4 class="mb-0 fw-bold text-danger">${{ number_format($totalPendingAmount, 2) }}</h4>
                    <small class="text-muted">Amount to be approved</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm bg-info-subtle">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:clock-duotone" class="text-info mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Upcoming Prizes</h6>
                    <h4 class="mb-0 fw-bold text-info">${{ number_format($totalUpcomingAmount ?? 0, 2) }}</h4>
                    <small class="text-muted">From {{ $totalUpcomingCount ?? 0 }} ongoing positions</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm bg-success-subtle">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Released This Month</h6>
                    <h4 class="mb-0 fw-bold text-success">${{ number_format($totalAwardedThisMonth, 2) }}</h4>
                    <small class="text-muted">Successfully distributed</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Pending Prizes by Leaderboard --}}
    @if($leaderboardsWithPendingPrizes->count() > 0)
        @foreach($leaderboardsWithPendingPrizes as $leaderboard)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">{{ $leaderboard->title }}</h5>
                            <small class="text-muted">
                                {{ $leaderboard->type === 'target' ? 'Target-based' : 'Competitive' }} | 
                                Ended: {{ $leaderboard->end_date->format('M d, Y') }} |
                                {{ $leaderboard->positions->count() }} pending prize(s)
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.leaderboards.show', $leaderboard) }}" class="btn btn-outline-primary btn-sm">
                                <iconify-icon icon="iconamoon:eye-duotone" class="me-1"></iconify-icon>
                                View
                            </a>
                            <button type="button" class="btn btn-success btn-sm" onclick="approveAllPrizes({{ $leaderboard->id }}, '{{ addslashes($leaderboard->title) }}', event)">
                                <iconify-icon icon="iconamoon:check-circle-duotone" class="me-1"></iconify-icon>
                                Approve All ({{ $leaderboard->positions->count() }})
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Position</th>
                                        <th>Winner</th>
                                        <th>Referrals</th>
                                        <th>Prize Amount</th>
                                        <th class="text-end pe-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboard->positions as $position)
                                    <tr id="prize-row-{{ $position->id }}">
                                        <td class="ps-3">
                                            <span class="badge {{ $position->position_badge_class }} px-2 py-1">
                                                {{ $position->position_display }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($position->user)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar avatar-sm bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                        <span class="text-primary fw-medium">{{ strtoupper(substr($position->user->full_name ?? $position->user->username, 0, 1)) }}</span>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0)" class="clickable-user fw-medium" onclick="showUserDetails('{{ $position->user->id }}')">{{ $position->user->full_name ?? $position->user->username }}</a>
                                                        <small class="text-muted d-block">{{ $position->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-danger">User not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $position->referral_count }} referrals
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success fs-5">${{ number_format($position->prize_amount, 2) }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            @if($position->user)
                                            <button type="button" class="btn btn-primary btn-sm" onclick="approvePrize({{ $position->id }}, '{{ addslashes($position->user->full_name ?? $position->user->username) }}', {{ $position->prize_amount }}, event)">
                                                <iconify-icon icon="iconamoon:check-circle-duotone" class="me-1"></iconify-icon>
                                                Approve
                                            </button>
                                            @else
                                            <span class="text-muted">Cannot approve</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="ps-3 fw-medium">Total for this leaderboard:</td>
                                        <td class="fw-bold text-success fs-5">${{ number_format($leaderboard->positions->sum('prize_amount'), 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success mb-3" style="font-size: 4rem;"></iconify-icon>
                        <h5 class="text-muted mb-2">No Pending Prizes</h5>
                        <p class="text-muted mb-0">All leaderboard prizes have been released. Great work!</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Upcoming Prizes from Ongoing Leaderboards --}}
    @if(isset($ongoingLeaderboards) && $ongoingLeaderboards->count() > 0)
        <div class="row mb-3">
            <div class="col-12">
                <h5 class="text-muted">
                    <iconify-icon icon="iconamoon:clock-duotone" class="me-2"></iconify-icon>
                    Upcoming Prizes (Ongoing Leaderboards)
                </h5>
            </div>
        </div>
        @foreach($ongoingLeaderboards as $leaderboard)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-info">
                    <div class="card-header d-flex align-items-center justify-content-between bg-info bg-opacity-10">
                        <div>
                            <h5 class="mb-1">
                                <iconify-icon icon="iconamoon:clock-duotone" class="text-info me-2"></iconify-icon>
                                {{ $leaderboard->title }}
                            </h5>
                            <small class="text-muted">
                                {{ $leaderboard->type === 'target' ? 'Target-based' : 'Competitive' }} | 
                                Ends: {{ $leaderboard->end_date->format('M d, Y') }} 
                                ({{ $leaderboard->end_date->diffForHumans() }}) |
                                {{ $leaderboard->positions->count() }} position(s)
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-info px-3 py-2">Ongoing</span>
                            <a href="{{ route('admin.leaderboards.show', $leaderboard) }}" class="btn btn-outline-info btn-sm">
                                <iconify-icon icon="iconamoon:eye-duotone" class="me-1"></iconify-icon>
                                View
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Position</th>
                                        <th>Current Leader</th>
                                        <th>Referrals</th>
                                        <th>Prize Amount</th>
                                        <th class="text-end pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leaderboard->positions as $position)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge {{ $position->position_badge_class ?? 'bg-secondary' }} px-2 py-1">
                                                {{ $position->position_display ?? '#' . $position->position }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($position->user)
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar avatar-sm bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                        <span class="text-info fw-medium">{{ strtoupper(substr($position->user->full_name ?? $position->user->username, 0, 1)) }}</span>
                                                    </div>
                                                    <div>
                                                        <a href="javascript:void(0)" class="clickable-user fw-medium" onclick="showUserDetails('{{ $position->user->id }}')">{{ $position->user->full_name ?? $position->user->username }}</a>
                                                        <small class="text-muted d-block">{{ $position->user->email }}</small>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">No leader yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $position->referral_count ?? 0 }} referrals
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-info fs-5">${{ number_format($position->prize_amount, 2) }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                <iconify-icon icon="iconamoon:clock-duotone" class="me-1"></iconify-icon>
                                                In Progress
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="ps-3 fw-medium">Total upcoming for this leaderboard:</td>
                                        <td class="fw-bold text-info fs-5">${{ number_format($leaderboard->positions->sum('prize_amount'), 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection

@section('vite_scripts')
<script>
function approvePrize(positionId, userName, amount, event) {
    if (!confirm(`Are you sure you want to approve $${amount.toFixed(2)} for ${userName} to claim?`)) {
        return;
    }

    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Approving...';

    fetch(`{{ url('admin/leaderboards/approve-prize') }}/${positionId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            const row = document.getElementById(`prize-row-${positionId}`);
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to approve prize', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to approve prize', 'danger');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function approveAllPrizes(leaderboardId, leaderboardTitle, event) {
    if (!confirm(`Are you sure you want to approve ALL pending prizes for "${leaderboardTitle}"? Users will be able to claim these prizes.`)) {
        return;
    }

    const btn = event.currentTarget;
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Approving...';

    fetch(`{{ url('admin/leaderboards/approve-all-prizes') }}/${leaderboardId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to approve prizes', 'danger');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to approve prizes', 'danger');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert.position-fixed');
        alerts.forEach(alert => alert.remove());
    }, 5000);
}
</script>
@endsection
