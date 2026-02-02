@extends('admin.layouts.vertical', ['title' => 'Rank & Reward', 'subTitle' => 'Manage Rank Program'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Rank & Reward Program</h4>
                            <p class="text-muted mb-0">Manage ranks and reward achievements</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ route('admin.ranks.preview-qualifications') }}" class="btn btn-outline-warning btn-sm">
                                <iconify-icon icon="iconamoon:eye-duotone" class="me-1"></iconify-icon>
                                Preview Qualifications
                            </a>
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="processAllRanks()">
                                <iconify-icon icon="iconamoon:refresh-duotone" class="me-1"></iconify-icon>
                                Process All Users
                            </button>
                            <a href="{{ route('admin.ranks.achievements') }}" class="btn btn-outline-primary btn-sm">
                                <iconify-icon icon="akar-icons:trophy" class="me-1"></iconify-icon>
                                View Achievements
                            </a>
                            <a href="{{ route('admin.ranks.create') }}" class="btn btn-primary btn-sm">
                                <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-1"></iconify-icon>
                                Add Rank
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:layers-duotone" class="text-primary mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Total Ranks</h6>
                    <h5 class="mb-0 fw-bold">{{ number_format($statistics['total_ranks']) }}</h5>
                    <small class="text-muted">Created</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="akar-icons:trophy" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Total Achievements</h6>
                    <h5 class="mb-0 fw-bold">{{ number_format($statistics['total_achievements']) }}</h5>
                    <small class="text-muted">All time</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:dollar-duotone" class="text-info mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Rewards Paid</h6>
                    <h5 class="mb-0 fw-bold">${{ number_format($statistics['total_rewards_paid'], 2) }}</h5>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:clock-duotone" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Pending Rewards</h6>
                    <h5 class="mb-0 fw-bold">{{ number_format($statistics['pending_rewards']) }}</h5>
                    <small class="text-muted">Unpaid</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Ranks Overview</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order</th>
                                    <th>Rank Name</th>
                                    <th class="text-center">Self Deposit</th>
                                    <th class="text-center">Direct Members</th>
                                    <th class="text-center">Team</th>
                                    <th class="text-center">Reward</th>
                                    <th class="text-center">Users</th>
                                    <th class="text-center">Total Paid</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ranks as $rank)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">{{ $rank->display_order }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($rank->icon)
                                            <iconify-icon icon="{{ $rank->icon }}" class="text-primary" style="font-size: 1.5rem;"></iconify-icon>
                                            @else
                                            <iconify-icon icon="iconamoon:badge-duotone" class="text-primary" style="font-size: 1.5rem;"></iconify-icon>
                                            @endif
                                            <span class="fw-semibold">{{ $rank->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">${{ number_format($rank->min_self_deposit, 0) }}</td>
                                    <td class="text-center">
                                        {{ $rank->min_direct_members }}
                                        @if($rank->min_direct_members > 0)
                                        <small class="text-muted">(Each ${{ number_format($rank->min_direct_member_investment, 0) }})</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($rank->min_team_members > 0)
                                        {{ $rank->min_team_members }}
                                        <small class="text-muted">(Each ${{ number_format($rank->min_team_member_investment, 0) }})</small>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6">${{ number_format($rank->reward_amount, 0) }}</span>
                                    </td>
                                    <td class="text-center">{{ number_format($rank->users_achieved) }}</td>
                                    <td class="text-center">${{ number_format($rank->total_rewards_paid, 2) }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="status{{ $rank->id }}" 
                                                   {{ $rank->is_active ? 'checked' : '' }}
                                                   onchange="toggleRankStatus({{ $rank->id }})">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('admin.ranks.edit', $rank) }}" class="btn btn-sm btn-outline-primary">
                                                <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                                            </a>
                                            <form action="{{ route('admin.ranks.destroy', $rank) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this rank?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <iconify-icon icon="iconamoon:layers-duotone" class="text-muted" style="font-size: 3rem;"></iconify-icon>
                                        <p class="text-muted mb-0 mt-2">No ranks created yet.</p>
                                        <a href="{{ route('admin.ranks.create') }}" class="btn btn-primary btn-sm mt-2">Create First Rank</a>
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
</div>
@endsection

@section('script')
<script>
function toggleRankStatus(rankId) {
    fetch(`{{ url('admin/ranks') }}/${rankId}/toggle-status`, {
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
        } else {
            showAlert('Failed to update status', 'danger');
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to update status', 'danger');
        location.reload();
    });
}

function processAllRanks() {
    if (!confirm('This will check all active users for rank qualifications. Continue?')) {
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
        btn.innerHTML = '<iconify-icon icon="iconamoon:refresh-duotone" class="me-1"></iconify-icon> Process All Users';
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
