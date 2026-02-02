@extends('admin.layouts.vertical', ['title' => 'Preview Qualifications', 'subTitle' => 'Check who qualifies for ranks'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Preview Rank Qualifications</h4>
                            <p class="text-muted mb-0">Check which users qualify for ranks before distributing rewards</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ route('admin.ranks.index') }}" class="btn btn-outline-secondary btn-sm">
                                <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                                Back to Ranks
                            </a>
                            @if($qualifiedUsers->count() > 0)
                            <button type="button" class="btn btn-success btn-sm" onclick="processAndDistribute()">
                                <iconify-icon icon="iconamoon:check-circle-1-duotone" class="me-1"></iconify-icon>
                                Process & Distribute Rewards
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.ranks.preview-qualifications') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Select Rank</label>
                            <select name="rank_id" class="form-select">
                                <option value="">All Ranks</option>
                                @foreach($ranks as $rank)
                                <option value="{{ $rank->id }}" {{ $selectedRankId == $rank->id ? 'selected' : '' }}>
                                    {{ $rank->name }} (${{ number_format($rank->reward_amount, 0) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search User</label>
                            <input type="text" name="search" class="form-control" placeholder="Username, email, name..." value="{{ $search ?? '' }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <iconify-icon icon="iconamoon:search-duotone" class="me-1"></iconify-icon>
                                Check Qualifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($submitted)
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:profile-duotone" class="text-primary mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Qualified Users</h6>
                    <h4 class="mb-0 fw-bold">{{ number_format($qualifiedUsers->count()) }}</h4>
                    <small class="text-muted">Ready for rewards</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:dollar-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Total Potential Reward</h6>
                    <h4 class="mb-0 fw-bold text-success">${{ number_format($totalPotentialReward, 2) }}</h4>
                    <small class="text-muted">If all distributed</small>
                </div>
            </div>
        </div>
        @if($selectedRank)
        <div class="col-12 col-lg-4">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    @if($selectedRank->icon)
                    <iconify-icon icon="{{ $selectedRank->icon }}" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                    @else
                    <iconify-icon icon="iconamoon:badge-duotone" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                    @endif
                    <h6 class="text-muted mb-1">Selected Rank</h6>
                    <h4 class="mb-0 fw-bold">{{ $selectedRank->name }}</h4>
                    <small class="text-muted">${{ number_format($selectedRank->reward_amount, 0) }} reward each</small>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success me-2"></iconify-icon>
                        Qualified Users
                        @if($selectedRank)
                        <span class="badge bg-primary ms-2">{{ $selectedRank->name }}</span>
                        @endif
                    </h5>
                    <span class="badge bg-success">{{ $qualifiedUsers->count() }} user(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    @if(!$selectedRank)
                                    <th>Rank</th>
                                    @endif
                                    <th class="text-center">Self Deposit</th>
                                    <th class="text-center">Direct Members</th>
                                    <th class="text-center">Team Members</th>
                                    <th class="text-center">Reward</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($qualifiedUsers as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                {{ strtoupper(substr($item['user']->first_name ?? $item['user']->username, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $item['user']->first_name }} {{ $item['user']->last_name }}</div>
                                                <small class="text-muted">{{ '@' . $item['user']->username }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    @if(!$selectedRank)
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($item['rank']->icon)
                                            <iconify-icon icon="{{ $item['rank']->icon }}" class="text-primary"></iconify-icon>
                                            @endif
                                            <span>{{ $item['rank']->name }}</span>
                                        </div>
                                    </td>
                                    @endif
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            ${{ number_format($item['progress']['self_deposit']['current'], 0) }}
                                        </span>
                                        <small class="text-muted d-block">/ ${{ number_format($item['progress']['self_deposit']['required'], 0) }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            {{ $item['progress']['direct_members']['current'] }}
                                        </span>
                                        <small class="text-muted d-block">/ {{ $item['progress']['direct_members']['required'] }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success">
                                            {{ $item['progress']['team_members']['current'] }}
                                        </span>
                                        <small class="text-muted d-block">/ {{ $item['progress']['team_members']['required'] }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark fs-6">
                                            ${{ number_format($selectedRank ? $selectedRank->reward_amount : $item['rank']->reward_amount, 0) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="{{ $selectedRank ? 5 : 6 }}" class="text-center py-4">
                                        <iconify-icon icon="iconamoon:search-duotone" class="text-muted" style="font-size: 3rem;"></iconify-icon>
                                        <p class="text-muted mb-0 mt-2">No users currently qualify for this rank.</p>
                                        <small class="text-muted">Users may not meet requirements or have already achieved this rank.</small>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <iconify-icon icon="iconamoon:search-duotone" class="text-muted" style="font-size: 4rem;"></iconify-icon>
                    <h5 class="text-muted mt-3">Select a Rank to Preview</h5>
                    <p class="text-muted mb-0">Choose a rank from the dropdown above to see which users qualify for it.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('script')
<script>
function processAndDistribute() {
    if (!confirm('This will process all qualified users and distribute rewards. Continue?')) {
        return;
    }
    
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
    
    fetch('{{ route("admin.ranks.process") }}', {
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
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert('Failed to process ranks', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to process ranks', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<iconify-icon icon="iconamoon:check-circle-1-duotone" class="me-1"></iconify-icon> Process & Distribute Rewards';
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
