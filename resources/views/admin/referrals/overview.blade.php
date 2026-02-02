@extends('admin.layouts.vertical', ['title' => 'Referral Overview', 'subTitle' => 'Admin'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Referral Overview</h4>
                            <p class="text-muted mb-0">View 10-level referral breakdown for any user</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ route('admin.referrals.index') }}" class="btn btn-outline-secondary btn-sm">
                                <iconify-icon icon="material-symbols:arrow-back" class="me-1"></iconify-icon>
                                Back to Referrals
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Search User</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.referrals.overview') }}" id="userSearchForm">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-8">
                                <label class="form-label">Search by Name, Email or Username</label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="userSearchInput" placeholder="Type to search users..." autocomplete="off">
                                    <input type="hidden" name="user_id" id="selectedUserId" value="{{ $userId }}">
                                    <div id="userSearchResults" class="dropdown-menu w-100" style="display: none; position: absolute; z-index: 1000;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary w-100" id="searchBtn" {{ !$userId ? 'disabled' : '' }}>
                                    <iconify-icon icon="material-symbols:search" class="me-1"></iconify-icon>
                                    View Referrals
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($selectedUser)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Selected User: {{ $selectedUser->full_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Email:</strong> {{ $selectedUser->email }}
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong> 
                            <span class="badge {{ $selectedUser->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($selectedUser->status) }}</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Invested:</strong> ${{ number_format($selectedUser->total_invested ?? 0, 2) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Total Referrals:</strong> <span class="badge bg-info fs-6">{{ number_format($totalReferrals) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">10-Level Referral Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Level</th>
                                    <th class="text-center">Total Users</th>
                                    <th class="text-center">Active Users</th>
                                    <th class="text-end">Total Invested</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($levelBreakdown as $level => $data)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">Level {{ $level }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong>{{ number_format($data['count']) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-success">{{ number_format($data['active_count']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        ${{ number_format($data['total_invested'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($data['count'] > 0)
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="viewLevelUsers({{ $level }})">
                                            <iconify-icon icon="material-symbols:visibility"></iconify-icon>
                                            View Users
                                        </button>
                                        @else
                                        <span class="text-muted">No users</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <td><strong>Total</strong></td>
                                    <td class="text-center"><strong>{{ number_format($totalReferrals) }}</strong></td>
                                    <td class="text-center"><strong>{{ number_format(collect($levelBreakdown)->sum('active_count')) }}</strong></td>
                                    <td class="text-end"><strong>${{ number_format(collect($levelBreakdown)->sum('total_invested'), 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="modal fade" id="levelUsersModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="levelUsersModalTitle">Level Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="levelUsersLoading" class="text-center py-4" style="display: none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">Loading users...</p>
                </div>
                <div id="levelUsersContent">
                    <div class="table-responsive">
                        <table class="table table-hover" id="levelUsersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Sponsor</th>
                                    <th>Invested</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody id="levelUsersTableBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearchInput');
    const resultsDiv = document.getElementById('userSearchResults');
    const hiddenInput = document.getElementById('selectedUserId');
    const searchBtn = document.getElementById('searchBtn');
    let searchTimeout;

    @if($selectedUser)
    searchInput.value = '{{ $selectedUser->full_name }} ({{ $selectedUser->email }})';
    @endif

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsDiv.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('admin.referrals.search.users') }}?search=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        resultsDiv.innerHTML = data.data.map(user => `
                            <a href="#" class="dropdown-item" data-id="${user.id}" data-display="${user.display}">
                                <strong>${user.name}</strong><br>
                                <small class="text-muted">${user.email}</small>
                            </a>
                        `).join('');
                        resultsDiv.style.display = 'block';
                    } else {
                        resultsDiv.innerHTML = '<span class="dropdown-item text-muted">No users found</span>';
                        resultsDiv.style.display = 'block';
                    }
                });
        }, 300);
    });

    resultsDiv.addEventListener('click', function(e) {
        e.preventDefault();
        const item = e.target.closest('.dropdown-item');
        if (item && item.dataset.id) {
            searchInput.value = item.dataset.display;
            hiddenInput.value = item.dataset.id;
            resultsDiv.style.display = 'none';
            searchBtn.disabled = false;
        }
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
});

function viewLevelUsers(level) {
    const userId = document.getElementById('selectedUserId').value;
    const modal = new bootstrap.Modal(document.getElementById('levelUsersModal'));
    
    document.getElementById('levelUsersModalTitle').textContent = `Level ${level} Users`;
    document.getElementById('levelUsersLoading').style.display = 'block';
    document.getElementById('levelUsersContent').style.display = 'none';
    document.getElementById('levelUsersTableBody').innerHTML = '';
    
    modal.show();

    fetch(`{{ route('admin.referrals.overview.level-users') }}?user_id=${userId}&level=${level}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('levelUsersLoading').style.display = 'none';
            document.getElementById('levelUsersContent').style.display = 'block';

            if (data.success && data.data.length > 0) {
                const tbody = document.getElementById('levelUsersTableBody');
                tbody.innerHTML = data.data.map(user => `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.name}</td>
                        <td>${user.email}</td>
                        <td><span class="badge ${user.status === 'active' ? 'bg-success' : 'bg-secondary'}">${user.status}</span></td>
                        <td>${user.sponsor_name}</td>
                        <td>$${parseFloat(user.total_invested).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                        <td>${user.created_at}</td>
                    </tr>
                `).join('');
            } else {
                document.getElementById('levelUsersTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-muted">No users found</td></tr>';
            }
        })
        .catch(err => {
            document.getElementById('levelUsersLoading').style.display = 'none';
            document.getElementById('levelUsersContent').style.display = 'block';
            document.getElementById('levelUsersTableBody').innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading users</td></tr>';
        });
}
</script>
@endpush
