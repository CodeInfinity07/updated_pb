@extends('admin.layouts.vertical', ['title' => 'Commission Settings', 'subTitle' => 'Admin'])

@section('content')

{{-- Header Section --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">Commission Settings</h4>
                        <p class="text-muted mb-0">Manage referral commission tiers and requirements</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-info btn-sm" onclick="updateUserTiers()">
                            Update User Tiers
                        </button>
                        <button type="button" class="btn btn-success btn-sm" onclick="showAddTierModal()">
                            Add New Tier
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="exportSettings()">
                                    <iconify-icon icon="iconamoon:download-duotone" class="me-2"></iconify-icon>Export CSV
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="showCalculator()">
                                    <iconify-icon icon="iconamoon:calculator-duotone" class="me-2"></iconify-icon>Commission Calculator
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-warning" href="#" onclick="seedDefaultTiers()">
                                    <iconify-icon icon="iconamoon:database-duotone" class="me-2"></iconify-icon>Seed Defaults
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Platform Statistics --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="mb-2">
                    <iconify-icon icon="iconamoon:profile-duotone" class="text-primary" style="font-size: 2rem;"></iconify-icon>
                </div>
                <h6 class="text-muted mb-1">Total Users</h6>
                <h5 class="mb-0">{{ number_format($totalUsers ?? 0) }}</h5>
                <small class="text-muted">Platform wide</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="mb-2">
                    <iconify-icon icon="material-symbols:check-box-rounded" class="text-success" style="font-size: 2rem;"></iconify-icon>
                </div>
                <h6 class="text-muted mb-1">Active Users</h6>
                <h5 class="mb-0">{{ number_format($totalActiveUsers ?? 0) }}</h5>
                <small class="text-success">{{ $totalUsers > 0 ? number_format(($totalActiveUsers / $totalUsers) * 100, 1) : 0 }}% active</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="mb-2">
                    <iconify-icon icon="material-symbols:attach-money" class="text-warning" style="font-size: 2rem;"></iconify-icon>
                </div>
                <h6 class="text-muted mb-1">Commissions Paid</h6>
                <h5 class="mb-0">${{ number_format($totalCommissionsPaid ?? 0, 2) }}</h5>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card text-center">
            <div class="card-body">
                <div class="mb-2">
                    <iconify-icon icon="mdi:certificate" class="text-info" style="font-size: 2rem;"></iconify-icon>
                </div>
                <h6 class="text-muted mb-1">Commission Tiers</h6>
                <h5 class="mb-0">{{ $commissionTiers->count() ?? 0 }}</h5>
                <small class="text-muted">{{ $commissionTiers->where('is_active', true)->count() ?? 0 }} active</small>
            </div>
        </div>
    </div>
</div>

{{-- Commission Simulation --}}
@if(isset($commissionSimulation) && is_array($commissionSimulation))
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Commission Simulation (${{ number_format($simulationAmount ?? 1000) }} Transaction)</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($commissionSimulation as $level => $simulation)
                        @php $tier = $commissionTiers->where('level', $level)->first(); @endphp
                        @if($tier)
                        <div class="col-lg-3 col-md-6">
                            <div class="simulation-card border rounded p-3" style="border-color: {{ $tier->color ?? '#6c757d' }} !important;">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="tier-indicator me-2" style="background-color: {{ $tier->color ?? '#6c757d' }};"></div>
                                    <h6 class="mb-0">{{ $tier->name ?? 'Tier ' . $level }}</h6>
                                </div>
                                <div class="simulation-details">
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Level 1:</small>
                                        <strong>${{ number_format($simulation['level_1'] ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Level 2:</small>
                                        <strong>${{ number_format($simulation['level_2'] ?? 0, 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <small>Level 3:</small>
                                        <strong>${{ number_format($simulation['level_3'] ?? 0, 2) }}</strong>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <strong>Total:</strong>
                                        <strong class="text-success">${{ number_format($simulation['total'] ?? 0, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Commission Tiers Table --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Commission Tiers</h5>
            </div>

            @if($commissionTiers && $commissionTiers->count() > 0)
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-nowrap align-middle mb-0">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th>Tier</th>
                                <th>Requirements</th>
                                <th>Commission Rates</th>
                                <th>Users</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commissionTiers as $tier)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="tier-indicator me-2" style="background-color: {{ $tier->color ?? '#6c757d' }};"></div>
                                        <div>
                                            <h6 class="mb-1">{{ $tier->name ?? 'Tier ' . $tier->level }}</h6>
                                            <small class="text-muted">Level {{ $tier->level }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="requirements-list">
                                        <div class="requirement-item">
                                            <iconify-icon icon="material-symbols:attach-money" class="text-success me-1"></iconify-icon>
                                            <small>${{ number_format($tier->min_investment ?? 0, 0) }} investment</small>
                                        </div>
                                        <div class="requirement-item">
                                            <iconify-icon icon="iconamoon:profile-add-duotone" class="text-primary me-1"></iconify-icon>
                                            <small>{{ $tier->min_direct_referrals ?? 0 }} direct referrals</small>
                                        </div>
                                        <div class="requirement-item">
                                            <iconify-icon icon="iconamoon:hierarchy-duotone" class="text-info me-1"></iconify-icon>
                                            <small>{{ $tier->min_indirect_referrals ?? 0 }} indirect referrals</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="commission-rates">
                                        <span class="badge bg-primary-subtle text-primary">L1: {{ $tier->commission_level_1 ?? 0 }}%</span>
                                        <span class="badge bg-secondary-subtle text-secondary">L2: {{ $tier->commission_level_2 ?? 0 }}%</span>
                                        <span class="badge bg-info-subtle text-info">L3: {{ $tier->commission_level_3 ?? 0 }}%</span>
                                        <div class="mt-1">
                                            <small class="text-muted">Total: {{ number_format(($tier->commission_level_1 ?? 0) + ($tier->commission_level_2 ?? 0) + ($tier->commission_level_3 ?? 0), 1) }}%</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <h6 class="mb-0">{{ number_format($tier->users_count ?? 0) }}</h6>
                                        <small class="text-muted">users</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                               {{ ($tier->is_active ?? false) ? 'checked' : '' }}
                                               onchange="toggleTierStatus({{ $tier->id }}, this)">
                                        <label class="form-check-label">
                                            <span class="badge {{ ($tier->is_active ?? false) ? 'bg-success' : 'bg-secondary' }}-subtle text-{{ ($tier->is_active ?? false) ? 'success' : 'secondary' }}">
                                                {{ ($tier->is_active ?? false) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="#" onclick="editTier({{ $tier->id }})">
                                                <iconify-icon icon="iconamoon:edit-duotone" class="me-2"></iconify-icon>Edit Tier
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="calculatePreview({{ $tier->id }})">
                                                <iconify-icon icon="iconamoon:calculator-duotone" class="me-2"></iconify-icon>Calculate Preview
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteTier({{ $tier->id }}, '{{ $tier->name ?? 'Tier' }}')">
                                                <iconify-icon icon="iconamoon:trash-duotone" class="me-2"></iconify-icon>Delete
                                            </a></li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card-body text-center py-5">
                <iconify-icon icon="mdi:certificate-outline" class="text-muted mb-3" style="font-size: 4rem;"></iconify-icon>
                <h5 class="text-muted">No Commission Tiers Found</h5>
                <p class="text-muted mb-4">Get started by creating your first commission tier or seed default tiers.</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-success" onclick="showAddTierModal()">
                        <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-2"></iconify-icon>Add New Tier
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="seedDefaultTiers()">
                        <iconify-icon icon="iconamoon:database-duotone" class="me-2"></iconify-icon>Seed Default Tiers
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Add/Edit Tier Modal --}}
<div class="modal fade" id="tierModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tierModalTitle">Add New Tier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="tierForm">
                <div class="modal-body">
                    <input type="hidden" id="tierId" name="tier_id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tier Level <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="tierLevel" name="level" min="1" required>
                            <small class="text-muted">Unique numeric level (e.g., 1, 2, 3)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tier Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tierName" name="name" required>
                            <small class="text-muted">Display name (e.g., Bronze, Silver, Gold)</small>
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="text-muted mb-3">Requirements</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Min Investment ($)</label>
                            <input type="number" class="form-control" id="minInvestment" name="min_investment" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Direct Referrals</label>
                            <input type="number" class="form-control" id="minDirectReferrals" name="min_direct_referrals" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Min Indirect Referrals</label>
                            <input type="number" class="form-control" id="minIndirectReferrals" name="min_indirect_referrals" min="0" value="0">
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="text-muted mb-3">Commission Rates (%)</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Level 1 Commission</label>
                            <input type="number" class="form-control" id="commissionLevel1" name="commission_level_1" min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Level 2 Commission</label>
                            <input type="number" class="form-control" id="commissionLevel2" name="commission_level_2" min="0" max="100" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Level 3 Commission</label>
                            <input type="number" class="form-control" id="commissionLevel3" name="commission_level_3" min="0" max="100" step="0.01" value="0">
                        </div>

                        <div class="col-12">
                            <hr class="my-2">
                            <h6 class="text-muted mb-3">Additional Settings</h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Tier Color</label>
                            <input type="color" class="form-control form-control-color" id="tierColor" name="color" value="#6c757d">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="tierActive" name="is_active" checked>
                                <label class="form-check-label" for="tierActive">Active</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="tierDescription" name="description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="tierSubmitBtn">Save Tier</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Calculator Modal --}}
<div class="modal fade" id="calculatorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Commission Calculator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Investment Amount ($)</label>
                    <input type="number" class="form-control" id="calculatorAmount" min="0.01" step="0.01" value="1000">
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Tier</label>
                    <select class="form-select" id="calculatorTier">
                        <option value="">Choose a tier...</option>
                        @foreach($commissionTiers as $tier)
                        <option value="{{ $tier->id }}">{{ $tier->name }} (Level {{ $tier->level }})</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" class="btn btn-primary w-100" onclick="calculateCommission()">Calculate</button>

                <div id="calculationResult" class="mt-4" style="display: none;">
                    <hr>
                    <h6>Calculation Results</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <tbody id="calculationBody">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.tier-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.requirements-list .requirement-item {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
}

.simulation-card {
    transition: all 0.3s ease;
}

.simulation-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script>
const tierDataStore = @json($commissionTiers->keyBy('id'));

function getModal(elementId) {
    const el = document.getElementById(elementId);
    if (!el) return null;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        return bootstrap.Modal.getOrCreateInstance(el);
    }
    return null;
}

document.addEventListener('DOMContentLoaded', function() {
    const tierForm = document.getElementById('tierForm');
    if (tierForm) {
        tierForm.addEventListener('submit', function(e) {
            e.preventDefault();
            saveTier();
        });
    }
});

function showAlert(message, type = 'success') {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) alert.remove();
    }, 5000);
}

function showAddTierModal() {
    document.getElementById('tierModalTitle').textContent = 'Add New Tier';
    document.getElementById('tierForm').reset();
    document.getElementById('tierId').value = '';
    document.getElementById('tierColor').value = '#6c757d';
    document.getElementById('tierActive').checked = true;
    
    const modal = getModal('tierModal');
    if (modal) {
        modal.show();
    } else {
        showAlert('Could not open modal. Please refresh the page.', 'danger');
    }
}

function editTier(id) {
    const tier = tierDataStore[id];
    if (!tier) {
        showAlert('Tier not found', 'danger');
        return;
    }

    document.getElementById('tierModalTitle').textContent = 'Edit Tier: ' + tier.name;
    document.getElementById('tierId').value = tier.id;
    document.getElementById('tierLevel').value = tier.level;
    document.getElementById('tierName').value = tier.name;
    document.getElementById('minInvestment').value = tier.min_investment;
    document.getElementById('minDirectReferrals').value = tier.min_direct_referrals;
    document.getElementById('minIndirectReferrals').value = tier.min_indirect_referrals;
    document.getElementById('commissionLevel1').value = tier.commission_level_1;
    document.getElementById('commissionLevel2').value = tier.commission_level_2;
    document.getElementById('commissionLevel3').value = tier.commission_level_3;
    document.getElementById('tierColor').value = tier.color || '#6c757d';
    document.getElementById('tierDescription').value = tier.description || '';
    document.getElementById('tierActive').checked = tier.is_active;

    const modal = getModal('tierModal');
    if (modal) {
        modal.show();
    } else {
        showAlert('Could not open modal. Please refresh the page.', 'danger');
    }
}

function saveTier() {
    const tierId = document.getElementById('tierId').value;
    const isEdit = tierId !== '';

    const data = {
        level: parseInt(document.getElementById('tierLevel').value),
        name: document.getElementById('tierName').value,
        min_investment: parseFloat(document.getElementById('minInvestment').value) || 0,
        min_direct_referrals: parseInt(document.getElementById('minDirectReferrals').value) || 0,
        min_indirect_referrals: parseInt(document.getElementById('minIndirectReferrals').value) || 0,
        commission_level_1: parseFloat(document.getElementById('commissionLevel1').value) || 0,
        commission_level_2: parseFloat(document.getElementById('commissionLevel2').value) || 0,
        commission_level_3: parseFloat(document.getElementById('commissionLevel3').value) || 0,
        color: document.getElementById('tierColor').value,
        description: document.getElementById('tierDescription').value,
        is_active: document.getElementById('tierActive').checked
    };

    const url = isEdit
        ? "{{ url('/admin/referrals/commission/tiers') }}/" + tierId
        : "{{ route('admin.referrals.commission.tiers.store') }}";

    const method = isEdit ? 'PUT' : 'POST';

    document.getElementById('tierSubmitBtn').disabled = true;
    document.getElementById('tierSubmitBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert(result.message, 'success');
            tierModal.hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(result.message || 'Failed to save tier', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred while saving', 'danger');
    })
    .finally(() => {
        document.getElementById('tierSubmitBtn').disabled = false;
        document.getElementById('tierSubmitBtn').innerHTML = 'Save Tier';
    });
}

function toggleTierStatus(id, checkbox) {
    fetch("{{ url('/admin/referrals/commission/tiers') }}/" + id + "/toggle-status", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            checkbox.checked = !checkbox.checked;
            showAlert(result.message || 'Failed to update status', 'danger');
        }
    })
    .catch(error => {
        checkbox.checked = !checkbox.checked;
        showAlert('An error occurred', 'danger');
    });
}

function deleteTier(id, name) {
    if (!confirm(`Are you sure you want to delete the "${name}" tier? This action cannot be undone.`)) {
        return;
    }

    fetch("{{ url('/admin/referrals/commission/tiers') }}/" + id, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(result.message || 'Failed to delete tier', 'danger');
        }
    })
    .catch(error => {
        showAlert('An error occurred while deleting', 'danger');
    });
}

function updateUserTiers() {
    if (!confirm('This will recalculate and update tier assignments for all users based on their current qualifications. Continue?')) {
        return;
    }

    showAlert('Updating user tiers...', 'info');

    fetch("{{ route('admin.referrals.commission.update-user-tiers') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => location.reload(), 2000);
        } else {
            showAlert(result.message || 'Failed to update user tiers', 'danger');
        }
    })
    .catch(error => {
        showAlert('An error occurred while updating user tiers', 'danger');
    });
}

function seedDefaultTiers() {
    if (!confirm('This will create default commission tiers. Existing tiers with matching levels will be updated. Continue?')) {
        return;
    }

    fetch("{{ route('admin.referrals.commission.seed-defaults') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(result.message || 'Failed to seed default tiers', 'danger');
        }
    })
    .catch(error => {
        showAlert('An error occurred while seeding defaults', 'danger');
    });
}

function showCalculator() {
    const modal = getModal('calculatorModal');
    if (modal) {
        modal.show();
    } else {
        showAlert('Could not open calculator. Please refresh the page.', 'danger');
    }
}

function calculatePreview(tierId) {
    document.getElementById('calculatorTier').value = tierId;
    showCalculator();
}

function calculateCommission() {
    const amount = document.getElementById('calculatorAmount').value;
    const tierId = document.getElementById('calculatorTier').value;

    if (!amount || !tierId) {
        showAlert('Please enter amount and select tier', 'warning');
        return;
    }

    fetch("{{ route('admin.referrals.commission.calculate-preview') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            amount: parseFloat(amount),
            tier_id: parseInt(tierId)
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            displayCalculationResults(result.data);
        } else {
            showAlert(result.message || 'Failed to calculate', 'danger');
        }
    })
    .catch(error => {
        showAlert('An error occurred during calculation', 'danger');
    });
}

function displayCalculationResults(data) {
    const body = document.getElementById('calculationBody');
    const commissions = data.commissions;

    body.innerHTML = `
        <tr><td>Investment Amount</td><td class="text-end"><strong>$${parseFloat(data.amount).toFixed(2)}</strong></td></tr>
        <tr><td>Level 1 Commission</td><td class="text-end text-success">$${parseFloat(commissions.level_1).toFixed(2)}</td></tr>
        <tr><td>Level 2 Commission</td><td class="text-end text-success">$${parseFloat(commissions.level_2).toFixed(2)}</td></tr>
        <tr><td>Level 3 Commission</td><td class="text-end text-success">$${parseFloat(commissions.level_3).toFixed(2)}</td></tr>
        <tr class="table-primary"><td><strong>Total Commission</strong></td><td class="text-end"><strong>$${parseFloat(commissions.total).toFixed(2)}</strong></td></tr>
    `;

    document.getElementById('calculationResult').style.display = 'block';
}

function exportSettings() {
    window.open("{{ route('admin.referrals.commission.export') }}", '_blank');
}
</script>
@endpush
