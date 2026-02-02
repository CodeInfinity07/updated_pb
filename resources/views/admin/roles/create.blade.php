@extends('admin.layouts.vertical', ['title' => 'Create Role', 'subTitle' => 'Create New Admin Role'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-1 text-dark">Create New Role</h4>
                            <p class="text-muted mb-0">Define a new admin role with custom permissions</p>
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

    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
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
                                   value="{{ old('name') }}" placeholder="e.g., Finance Manager" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Describe what this role can do...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
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
                                               {{ in_array($permission['id'], old('permissions', [])) ? 'checked' : '' }}>
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

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <iconify-icon icon="iconamoon:plus-duotone" class="me-1"></iconify-icon>
                        Create Role
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
