@extends('admin.layouts.vertical', ['title' => 'Role Management', 'subTitle' => 'Manage Admin Roles and Permissions'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Role Management</h4>
                            <p class="text-muted mb-0">Create and manage admin roles with custom permissions</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            @if(auth()->user()->adminRole && auth()->user()->adminRole->isSuperAdmin())
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#promoteUserModal">
                                <iconify-icon icon="iconamoon:arrow-up-2-duotone" class="me-1"></iconify-icon>Promote User
                            </button>
                            @endif
                            <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm">
                                <iconify-icon icon="guidance:care-staff-area" class="me-1"></iconify-icon>
                                Staff Management
                            </a>
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                                <iconify-icon icon="iconamoon:plus-duotone" class="me-1"></iconify-icon>
                                Create Role
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:shield-duotone" class="text-primary mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Total Roles</h6>
                    <h5 class="mb-0 fw-bold">{{ $stats['total_roles'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:lock-duotone" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">System Roles</h6>
                    <h5 class="mb-0 fw-bold">{{ $stats['system_roles'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:edit-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Custom Roles</h6>
                    <h5 class="mb-0 fw-bold">{{ $stats['custom_roles'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:profile-duotone" class="text-info mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Staff Users</h6>
                    <h5 class="mb-0 fw-bold">{{ $stats['staff_users'] }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">All Roles</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-3">Role Name</th>
                                    <th class="border-0">Description</th>
                                    <th class="border-0 text-center">Permissions</th>
                                    <th class="border-0 text-center">Users</th>
                                    <th class="border-0 text-center">Type</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="border-0 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2">
                                                <iconify-icon icon="iconamoon:shield-duotone"></iconify-icon>
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $role->name }}</h6>
                                                <small class="text-muted">{{ $role->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ Str::limit($role->description, 50) ?: 'No description' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($role->slug === 'super-admin')
                                            <span class="badge bg-success">All</span>
                                        @else
                                            <span class="badge bg-info">{{ $role->permissions_count }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $role->users_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($role->is_system)
                                            <span class="badge bg-warning">System</span>
                                        @else
                                            <span class="badge bg-primary">Custom</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($role->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-outline-info btn-sm" title="View">
                                                <iconify-icon icon="iconamoon:eye-duotone"></iconify-icon>
                                            </a>
                                            @if($role->slug !== 'super-admin')
                                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                                <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                                            </a>
                                            @endif
                                            @if(!$role->is_system)
                                            <button type="button" class="btn btn-outline-danger btn-sm" title="Delete" 
                                                    onclick="confirmDelete({{ $role->id }}, '{{ $role->name }}')"
                                                    @if($role->users_count > 0) disabled @endif>
                                                <iconify-icon icon="iconamoon:trash-duotone"></iconify-icon>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <iconify-icon icon="iconamoon:shield-duotone" class="text-muted mb-2" style="font-size: 3rem;"></iconify-icon>
                                        <p class="text-muted mb-0">No roles found</p>
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

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">Delete Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <iconify-icon icon="iconamoon:trash-duotone" class="text-danger mb-3" style="font-size: 4rem;"></iconify-icon>
                <p>Are you sure you want to delete the role "<strong id="deleteRoleName"></strong>"?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Role</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Promote User Modal --}}
<div class="modal fade" id="promoteUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Promote User to Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="promoteUserForm">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Search and Select User <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="userSearch" placeholder="Search by name, email, or username...">
                                <button type="button" class="btn btn-outline-secondary" onclick="searchUsers()">
                                    <iconify-icon icon="iconamoon:search-duotone"></iconify-icon>
                                </button>
                            </div>
                            <div class="form-text">Search for users with "user" role to promote them to staff</div>
                        </div>

                        <div class="col-12" id="userSearchResults" style="display: none;">
                            <label class="form-label fw-semibold">Select User</label>
                            <div class="border rounded p-2 bg-light" style="max-height: 300px; overflow-y: auto;">
                                <div id="searchResultsList"></div>
                            </div>
                        </div>

                        <div class="col-12" id="selectedUserSection" style="display: none;">
                            <label class="form-label fw-semibold">Selected User</label>
                            <div class="border rounded p-3 bg-success bg-opacity-10">
                                <div id="selectedUserInfo"></div>
                                <input type="hidden" id="selectedUserId" name="user_id">
                            </div>
                        </div>

                        <input type="hidden" id="newRole" name="role" value="admin">
                        
                        @if(auth()->user()->adminRole && auth()->user()->adminRole->isSuperAdmin())
                        <div class="col-12">
                            <label for="adminRoleId" class="form-label fw-semibold">Admin Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="adminRoleId" name="admin_role_id" required>
                                <option value="">Select Admin Role</option>
                                @foreach($adminRoles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }} {{ $role->isSuperAdmin() ? '(Full Access)' : '' }}</option>
                                @endforeach
                            </select>
                            <div class="form-text">This determines what permissions the staff member will have in the admin panel</div>
                        </div>
                        @endif

                        <div class="col-12">
                            <label for="promoteReason" class="form-label fw-semibold">Reason for Promotion</label>
                            <input type="text" class="form-control" id="promoteReason" name="reason" placeholder="Optional reason...">
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="notifyUser" name="notify_user" value="1" checked>
                                <label class="form-check-label" for="notifyUser">
                                    Send notification email to user about their promotion
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <iconify-icon icon="iconamoon:arrow-up-2-duotone" class="me-2"></iconify-icon>
                        Promote User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let selectedUserId = null;
let searchTimeout = null;

function confirmDelete(roleId, roleName) {
    document.getElementById('deleteRoleName').textContent = roleName;
    document.getElementById('deleteForm').action = '/admin/roles/' + roleId;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

function searchUsers() {
    const searchTerm = document.getElementById('userSearch').value;
    
    if (searchTerm.length < 2) {
        showAlert('Please enter at least 2 characters to search', 'warning');
        return;
    }
    
    fetch(`{{ route('admin.staff.search-users') }}?search=${encodeURIComponent(searchTerm)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displaySearchResults(data.users);
            } else {
                showAlert(data.message || 'Failed to search users', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to search users', 'danger');
        });
}

function displaySearchResults(users) {
    const resultsList = document.getElementById('searchResultsList');
    const resultsSection = document.getElementById('userSearchResults');
    
    if (users.length === 0) {
        resultsList.innerHTML = '<div class="text-center text-muted py-3">No users found</div>';
    } else {
        resultsList.innerHTML = users.map(user => `
            <div class="d-flex align-items-center justify-content-between p-2 border rounded mb-2 user-search-result" onclick="selectUser(${user.id}, '${user.full_name}', '${user.email}', '${user.username}')">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm rounded-circle bg-primary me-2">
                        <span class="avatar-title text-white">${user.initials}</span>
                    </div>
                    <div>
                        <div class="fw-semibold">${user.full_name}</div>
                        <small class="text-muted">${user.email}</small>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-primary">Select</button>
            </div>
        `).join('');
    }
    
    resultsSection.style.display = 'block';
}

function selectUser(id, name, email, username) {
    selectedUserId = id;
    document.getElementById('selectedUserId').value = id;
    
    document.getElementById('selectedUserInfo').innerHTML = `
        <div class="d-flex align-items-center">
            <div class="avatar avatar-sm rounded-circle bg-success me-2">
                <span class="avatar-title text-white">${name.split(' ').map(n => n[0]).join('')}</span>
            </div>
            <div>
                <div class="fw-semibold">${name}</div>
                <small class="text-muted">${email} • @${username}</small>
            </div>
        </div>
    `;
    
    document.getElementById('selectedUserSection').style.display = 'block';
    document.getElementById('userSearchResults').style.display = 'none';
    document.getElementById('userSearch').value = '';
}

document.getElementById('userSearch').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const searchTerm = this.value;
    
    if (searchTerm.length >= 2) {
        searchTimeout = setTimeout(() => {
            searchUsers();
        }, 500);
    } else if (searchTerm.length === 0) {
        document.getElementById('userSearchResults').style.display = 'none';
    }
});

document.getElementById('promoteUserForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!selectedUserId) {
        showAlert('Please select a user to promote', 'warning');
        return;
    }
    
    const formData = new FormData(this);
    formData.append('user_id', selectedUserId);
    
    fetch('{{ route("admin.staff.promote-user") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(text || 'Server error');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('promoteUserModal')).hide();
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Promotion failed', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to promote user: ' + error.message, 'danger');
    });
});

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { if (alertDiv.parentNode) alertDiv.remove(); }, 4000);
}

document.getElementById('promoteUserModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('promoteUserForm').reset();
    document.getElementById('userSearchResults').style.display = 'none';
    document.getElementById('selectedUserSection').style.display = 'none';
    selectedUserId = null;
});
</script>

<style>
.user-search-result {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.user-search-result:hover {
    background-color: #f8f9fa;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.8rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
</style>
@endsection
