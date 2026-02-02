@extends('admin.layouts.vertical', ['title' => 'Rank Achievements', 'subTitle' => 'View User Rank Achievements'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.ranks.index') }}" class="btn btn-outline-secondary">
                    <iconify-icon icon="iconamoon:arrow-left-2-duotone"></iconify-icon>
                </a>
                <div>
                    <h4 class="mb-0">Rank Achievements</h4>
                    <small class="text-muted">View all user rank achievements and reward status</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.ranks.achievements') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="rank_id" class="form-label">Filter by Rank</label>
                            <select class="form-select" name="rank_id" id="rank_id">
                                <option value="">All Ranks</option>
                                @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}" {{ $selectedRankId == $rank->id ? 'selected' : '' }}>{{ $rank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="reward_status" class="form-label">Reward Status</label>
                            <select class="form-select" name="reward_status" id="reward_status">
                                <option value="">All</option>
                                <option value="paid" {{ request('reward_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="pending" {{ request('reward_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search User</label>
                            <input type="text" class="form-control" name="search" id="search" value="{{ request('search') }}" placeholder="Username, email, or name">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <iconify-icon icon="iconamoon:search-duotone" class="me-1"></iconify-icon>
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Achievements ({{ $achievements->total() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Rank</th>
                                    <th class="text-center">Achieved At</th>
                                    <th class="text-center">Reward</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($achievements as $achievement)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm rounded-circle bg-primary">
                                                <span class="avatar-title text-white">{{ $achievement->user->initials ?? 'U' }}</span>
                                            </div>
                                            <div>
                                                <a href="javascript:void(0)" class="fw-semibold clickable-user" onclick="showUserDetails('{{ $achievement->user->id }}')">{{ $achievement->user->full_name ?? 'Unknown' }}</a>
                                                <div class="small text-muted">{{ $achievement->user->email ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($achievement->rank->icon)
                                            <iconify-icon icon="{{ $achievement->rank->icon }}" class="text-primary"></iconify-icon>
                                            @endif
                                            <span class="fw-semibold">{{ $achievement->rank->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        {{ $achievement->achieved_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">${{ number_format($achievement->rank->reward_amount, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($achievement->reward_paid)
                                        <span class="badge bg-success">Paid</span>
                                        <div class="small text-muted">{{ $achievement->reward_paid_at?->format('M d, Y') }}</div>
                                        @else
                                        <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$achievement->reward_paid && $achievement->rank->reward_amount > 0)
                                        <button type="button" class="btn btn-sm btn-success" onclick="payReward({{ $achievement->id }})">
                                            <iconify-icon icon="iconamoon:dollar-duotone" class="me-1"></iconify-icon>
                                            Pay Reward
                                        </button>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <iconify-icon icon="akar-icons:trophy" class="text-muted" style="font-size: 3rem;"></iconify-icon>
                                        <p class="text-muted mb-0 mt-2">No achievements found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($achievements->hasPages())
                <div class="card-footer bg-white">
                    {{ $achievements->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('admin.partials.user-details-modal')
@endsection

@section('script')
<script>
function payReward(achievementId) {
    if (!confirm('Pay the reward for this rank achievement?')) {
        return;
    }
    
    fetch(`{{ url('admin/ranks/achievements') }}/${achievementId}/pay`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message || 'Failed to pay reward', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to pay reward', 'danger');
    });
}

function showAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
}
</script>
@endsection
