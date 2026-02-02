@extends('admin.layouts.vertical', ['title' => 'View Role', 'subTitle' => 'Role Details'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1 text-dark">{{ $role->name }}</h4>
                            <p class="text-muted mb-0">{{ $role->description ?: 'No description provided' }}</p>
                        </div>
                        <div class="d-flex gap-2">
                            @if($role->slug !== 'super-admin')
                            <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-sm">
                                <iconify-icon icon="iconamoon:edit-duotone" class="me-1"></iconify-icon>
                                Edit Role
                            </a>
                            @endif
                            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                                <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                                Back to Roles
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Role Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small">Role Name</label>
                        <p class="mb-0 fw-semibold">{{ $role->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Slug</label>
                        <p class="mb-0"><code>{{ $role->slug }}</code></p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Type</label>
                        <p class="mb-0">
                            @if($role->is_system)
                                <span class="badge bg-warning">System Role</span>
                            @else
                                <span class="badge bg-primary">Custom Role</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Status</label>
                        <p class="mb-0">
                            @if($role->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small">Total Permissions</label>
                        <p class="mb-0">
                            @if($role->slug === 'super-admin')
                                <span class="badge bg-success">All Permissions</span>
                            @else
                                <span class="badge bg-info">{{ $role->permissions->count() }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="mb-0">
                        <label class="text-muted small">Assigned Users</label>
                        <p class="mb-0"><span class="badge bg-secondary">{{ $role->users->count() }}</span></p>
                    </div>
                </div>
            </div>

            @if($role->users->count() > 0)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Assigned Users</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($role->users->take(10) as $assignedUser)
                        <li class="list-group-item d-flex align-items-center">
                            <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2">
                                {{ strtoupper(substr($assignedUser->first_name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="mb-0 fw-semibold">{{ $assignedUser->first_name }} {{ $assignedUser->last_name }}</p>
                                <small class="text-muted">{{ $assignedUser->email }}</small>
                            </div>
                        </li>
                        @endforeach
                        @if($role->users->count() > 10)
                        <li class="list-group-item text-center text-muted">
                            +{{ $role->users->count() - 10 }} more users
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Permissions</h5>
                </div>
                <div class="card-body">
                    @if($role->slug === 'super-admin')
                    <div class="text-center py-4">
                        <iconify-icon icon="iconamoon:shield-yes-duotone" class="text-success mb-3" style="font-size: 4rem;"></iconify-icon>
                        <h5>Full Access</h5>
                        <p class="text-muted">Super Admin has access to all permissions in the system.</p>
                    </div>
                    @elseif($role->permissions->count() === 0)
                    <div class="text-center py-4">
                        <iconify-icon icon="iconamoon:shield-off-duotone" class="text-warning mb-3" style="font-size: 4rem;"></iconify-icon>
                        <h5>No Permissions</h5>
                        <p class="text-muted">This role has no permissions assigned.</p>
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-primary btn-sm">Assign Permissions</a>
                    </div>
                    @else
                    <div class="row">
                        @foreach($permissionsByModule as $module => $permissions)
                        <div class="col-md-6 mb-4">
                            <div class="border rounded p-3 h-100">
                                <h6 class="mb-3 fw-bold text-primary">
                                    <iconify-icon icon="iconamoon:folder-duotone" class="me-1"></iconify-icon>
                                    {{ \App\Models\AdminPermission::getModuleLabels()[$module] ?? ucfirst($module) }}
                                </h6>
                                @foreach($permissions as $permission)
                                <div class="d-flex align-items-center mb-2">
                                    <iconify-icon icon="iconamoon:check-circle-1-duotone" class="text-success me-2"></iconify-icon>
                                    <span>{{ $permission->name }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
