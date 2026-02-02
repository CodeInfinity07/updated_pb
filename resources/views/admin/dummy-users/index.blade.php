@extends('admin.layouts.vertical', ['title' => 'Dummy Users', 'subTitle' => 'Manage Dummy User Restrictions'])

@section('content')
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md">
                            <div class="text-center">
                                <h4 class="mb-1 text-primary">{{ $stats['total'] }}</h4>
                                <small class="text-muted">Total Dummy Users</small>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="text-center">
                                <h4 class="mb-1 text-danger">{{ $stats['withdraw_disabled'] }}</h4>
                                <small class="text-muted">Withdraw Disabled</small>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="text-center">
                                <h4 class="mb-1 text-warning">{{ $stats['roi_disabled'] }}</h4>
                                <small class="text-muted">ROI Disabled</small>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="text-center">
                                <h4 class="mb-1 text-info">{{ $stats['commission_disabled'] }}</h4>
                                <small class="text-muted">Commission Disabled</small>
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="text-center">
                                <h4 class="mb-1 text-secondary">{{ $stats['referral_disabled'] }}</h4>
                                <small class="text-muted">Referral Disabled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Dummy Users</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.dummy-users.stats') }}" class="btn btn-sm btn-info">
                    <iconify-icon icon="mdi:chart-box" class="me-1"></iconify-icon>View Stats
                </a>
                <form action="{{ route('admin.dummy-users.index') }}" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary">Search</button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3 d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectAll">Select All</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">Deselect All</button>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" id="bulkActionBtn" disabled>
                        Bulk Actions
                    </button>
                    <ul class="dropdown-menu">
                        <li><h6 class="dropdown-header">Enable Restrictions</h6></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="enable_withdraw_disabled">Enable Withdraw Restriction</a></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="enable_roi_disabled">Enable ROI Restriction</a></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="enable_commission_disabled">Enable Commission Restriction</a></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="enable_referral_disabled">Enable Referral Restriction</a></li>
                        <li><a class="dropdown-item bulk-action text-danger" href="#" data-action="enable_all">Enable All Restrictions</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><h6 class="dropdown-header">Disable Restrictions</h6></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="disable_withdraw_disabled">Disable Withdraw Restriction</a></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="disable_roi_disabled">Disable ROI Restriction</a></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="disable_commission_disabled">Disable Commission Restriction</a></li>
                        <li><a class="dropdown-item bulk-action" href="#" data-action="disable_referral_disabled">Disable Referral Restriction</a></li>
                        <li><a class="dropdown-item bulk-action text-success" href="#" data-action="disable_all">Disable All Restrictions</a></li>
                    </ul>
                </div>
                <span class="text-muted" id="selectedCount">0 selected</span>
            </div>

            @if($dummyUsers->isEmpty())
                <div class="text-center py-5">
                    <iconify-icon icon="mdi:account-off" class="fs-1 text-muted mb-3"></iconify-icon>
                    <h5 class="text-muted">No Dummy Users Found</h5>
                    <p class="text-muted">Mark users as "Excluded from Stats" to manage them here.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" class="form-check-input" id="checkAll">
                                </th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th class="text-center">Withdraw</th>
                                <th class="text-center">ROI</th>
                                <th class="text-center">Commission</th>
                                <th class="text-center">Referral</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dummyUsers as $user)
                            <tr data-user-id="{{ $user->id }}">
                                <td>
                                    <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <h6 class="mb-0"><a href="javascript:void(0)" class="clickable-user" onclick="showUserDetails('{{ $user->id }}')">{{ $user->first_name }} {{ $user->last_name }}</a></h6>
                                            <small class="text-muted">{{ $user->username }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : ($user->status === 'blocked' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input restriction-toggle" type="checkbox" 
                                            data-user-id="{{ $user->id }}" 
                                            data-field="withdraw_disabled"
                                            {{ $user->withdraw_disabled ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input restriction-toggle" type="checkbox" 
                                            data-user-id="{{ $user->id }}" 
                                            data-field="roi_disabled"
                                            {{ $user->roi_disabled ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input restriction-toggle" type="checkbox" 
                                            data-user-id="{{ $user->id }}" 
                                            data-field="commission_disabled"
                                            {{ $user->commission_disabled ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input restriction-toggle" type="checkbox" 
                                            data-user-id="{{ $user->id }}" 
                                            data-field="referral_disabled"
                                            {{ $user->referral_disabled ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.users.show', $user) }}">
                                                    <iconify-icon icon="mdi:eye" class="me-2"></iconify-icon>View Profile
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-warning unmark-dummy" href="#" data-user-id="{{ $user->id }}">
                                                    <iconify-icon icon="mdi:account-remove" class="me-2"></iconify-icon>Remove from Dummy
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $dummyUsers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = '{{ csrf_token() }}';
    
    function updateSelectedCount() {
        const count = document.querySelectorAll('.user-checkbox:checked').length;
        document.getElementById('selectedCount').textContent = count + ' selected';
        document.getElementById('bulkActionBtn').disabled = count === 0;
    }

    document.querySelectorAll('.restriction-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const userId = this.dataset.userId;
            const field = this.dataset.field;
            
            fetch(`/admin/dummy-users/${userId}/toggle`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ field: field })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                } else {
                    this.checked = !this.checked;
                    showToast('error', data.message);
                }
            })
            .catch(error => {
                this.checked = !this.checked;
                showToast('error', 'An error occurred');
            });
        });
    });

    document.getElementById('selectAll').addEventListener('click', function() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = true);
        document.getElementById('checkAll').checked = true;
        updateSelectedCount();
    });

    document.getElementById('deselectAll').addEventListener('click', function() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = false);
        document.getElementById('checkAll').checked = false;
        updateSelectedCount();
    });

    document.getElementById('checkAll').addEventListener('change', function() {
        document.querySelectorAll('.user-checkbox').forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    document.querySelectorAll('.user-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    document.querySelectorAll('.bulk-action').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const userIds = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => parseInt(cb.value));
            
            if (userIds.length === 0) {
                showToast('warning', 'Please select at least one user');
                return;
            }

            if (!confirm(`Apply "${action.replace(/_/g, ' ')}" to ${userIds.length} user(s)?`)) {
                return;
            }

            fetch('/admin/dummy-users/bulk-action', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ user_ids: userIds, action: action })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('error', data.message);
                }
            })
            .catch(error => {
                showToast('error', 'An error occurred');
            });
        });
    });

    document.querySelectorAll('.unmark-dummy').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const userId = this.dataset.userId;
            
            if (!confirm('Remove this user from dummy users? This will also disable all restrictions.')) {
                return;
            }

            fetch(`/admin/dummy-users/${userId}/unmark`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('success', data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('error', data.message);
                }
            })
            .catch(error => {
                showToast('error', 'An error occurred');
            });
        });
    });

    function showToast(type, message) {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'danger')} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>`;
        
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        container.insertAdjacentHTML('beforeend', toastHtml);
        const toast = new bootstrap.Toast(container.lastElementChild);
        toast.show();
    }
});
</script>
@endsection
