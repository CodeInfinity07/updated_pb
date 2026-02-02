@extends('admin.layouts.vertical', ['title' => 'Edit Role', 'subTitle' => 'Edit Admin Role'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1 text-dark">Edit Role: {{ $role->name }}</h4>
                            <p class="text-muted mb-0">Modify role settings and permissions</p>
                        </div>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                            Back to Roles
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($role->is_system)
    <div class="alert alert-warning mb-4">
        <iconify-icon icon="iconamoon:attention-circle-duotone" class="me-2"></iconify-icon>
        <strong>System Role:</strong> You can only modify the description and permissions of system roles. The name cannot be changed.
    </div>
    @endif

    <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Role Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name', $role->name) }}" placeholder="e.g., Finance Manager" 
                                   {{ $role->is_system ? 'readonly' : '' }} required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Slug</label>
                            <input type="text" class="form-control" value="{{ $role->slug }}" readonly>
                            <small class="text-muted">Auto-generated from role name</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Describe what this role can do...">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assigned Users</label>
                            <p class="mb-0">
                                <span class="badge bg-info fs-6">{{ $role->users()->count() }}</span> users with this role
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @if($role->slug === 'super-admin')
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center py-5">
                        <iconify-icon icon="iconamoon:shield-yes-duotone" class="text-success mb-3" style="font-size: 4rem;"></iconify-icon>
                        <h5>Super Admin Role</h5>
                        <p class="text-muted">This role has full access to all permissions and cannot be modified.</p>
                    </div>
                </div>
                @else
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Permissions</h5>
                            <div>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="selectAll()">Select All</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="deselectAll()">Deselect All</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($permissionsByModule as $module => $permissions)
                            <div class="col-md-6 mb-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-bold text-primary">
                                            <iconify-icon icon="iconamoon:folder-duotone" class="me-1"></iconify-icon>
                                            {{ $moduleLabels[$module] ?? ucfirst($module) }}
                                        </h6>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                onclick="toggleModule('{{ $module }}')">Toggle</button>
                                    </div>
                                    @foreach($permissions as $permission)
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input permission-checkbox module-{{ $module }}" 
                                               name="permissions[]" value="{{ $permission['id'] }}" id="perm_{{ $permission['id'] }}"
                                               {{ in_array($permission['id'], old('permissions', $rolePermissionIds)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm_{{ $permission['id'] }}">
                                            {{ $permission['name'] }}
                                            <small class="text-muted d-block">{{ $permission['description'] }}</small>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <iconify-icon icon="iconamoon:check-duotone" class="me-1"></iconify-icon>
                        Update Role
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('script')
<script>
function selectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
}

function deselectAll() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
}

function toggleModule(module) {
    const checkboxes = document.querySelectorAll('.module-' + module);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}
</script>
@endsection
