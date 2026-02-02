{{-- resources/views/admin/investment/create.blade.php --}}
@extends('admin.layouts.vertical', ['title' => 'Create Investment Plan', 'subTitle' => 'Add New Investment Plan'])

@section('css')
<style>
    .plan-card {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 1.5rem;
        cursor: pointer;
        transition: border-color 0.2s;
        text-align: center;
    }
    .plan-card:hover {
        border-color: #0d6efd;
    }
    .plan-card.active {
        border-color: #0d6efd;
        background: #f8f9ff;
    }
    .section-card {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    .section-header {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #dee2e6;
        border-radius: 7px 7px 0 0;
    }
    .tier-item {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        margin-bottom: 1rem;
        background: white;
    }
    .tier-header {
        background: #f1f3f4;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #dee2e6;
    }
    .commission-item {
        background: #fff9e6;
        border: 1px solid #ffc107;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
    .commission-header {
        background: #fff3cd;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #ffc107;
    }
    .color-box {
        padding: 1rem;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
    }
    .color-box:hover {
        border-color: #0d6efd;
    }
    .color-box.selected {
        border-color: #0d6efd;
        background: #f8f9ff;
    }
    .hidden { display: none !important; }
    .commission-valid { color: #198754; font-weight: 600; }
    .commission-invalid { color: #dc3545; font-weight: 600; }
    .avatar-xl {
        width: 4rem;
        height: 4rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-title {
        font-size: 2rem;
    }
    .bg-primary-subtle {
        background-color: rgba(13, 110, 253, 0.1);
    }
    .bg-info-subtle {
        background-color: rgba(13, 202, 240, 0.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    

    <div class="row">
        <div class="col-lg-8">
            
            <!-- Basic Information -->
            <div class="section-card">
                <div class="section-header">
                    <h6 class="mb-0"><iconify-icon icon="solar:document-text-bold-duotone" class="me-2"></iconify-icon>Basic Information</h6>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="planName" placeholder="Enter plan name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="planDescription" rows="3" placeholder="Plan description"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Badge Text</label>
                            <input type="text" class="form-control" id="planBadge" placeholder="e.g., Popular">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="planSortOrder" value="0" min="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Configuration -->
            <div class="section-card">
                <div class="section-header">
                    <h6 class="mb-0"><iconify-icon icon="solar:settings-bold-duotone" class="me-2"></iconify-icon>Plan Configuration</h6>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Interest Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="interestType" required>
                                <option value="">Select Type</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Duration (Days) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="durationDays" placeholder="30" min="1" required>
                            <small class="text-muted">Package runs until 3x/6x cap is reached (duration is informational)</small>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="capitalReturn">
                                <label class="form-check-label" for="capitalReturn">
                                    Return principal at maturity
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Investment Amount Settings -->
            <div class="section-card">
                <div class="section-header">
                    <h6 class="mb-0"><iconify-icon icon="solar:wallet-money-bold-duotone" class="me-2"></iconify-icon>Investment Amount</h6>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Minimum Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="minimumAmount" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Maximum Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="maximumAmount" step="0.01">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ROI Settings -->
            <div class="section-card">
                <div class="section-header">
                    <h6 class="mb-0"><iconify-icon icon="solar:chart-2-bold-duotone" class="me-2"></iconify-icon>Return on Investment (ROI)</h6>
                </div>
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">ROI Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="roiType" id="roiFixed" value="fixed" checked onchange="toggleRoiType()">
                                    <label class="form-check-label" for="roiFixed">
                                        <strong>Fixed</strong> - Same rate every day
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="roiType" id="roiVariable" value="variable" onchange="toggleRoiType()">
                                    <label class="form-check-label" for="roiVariable">
                                        <strong>Variable</strong> - Random rate within range
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fixed ROI -->
                        <div class="col-12" id="fixedRoiSection">
                            <label class="form-label">Interest Rate <span class="text-danger">*</span></label>
                            <div class="input-group" style="max-width: 200px;">
                                <input type="number" class="form-control" id="interestRate" step="0.01" placeholder="0.50">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Users will receive this exact rate each period</small>
                        </div>
                        
                        <!-- Variable ROI -->
                        <div class="col-12 hidden" id="variableRoiSection">
                            <label class="form-label">Interest Rate Range <span class="text-danger">*</span></label>
                            <div class="row g-2" style="max-width: 400px;">
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-text">Min</span>
                                        <input type="number" class="form-control" id="minInterestRate" step="0.0001" placeholder="0.30">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-text">Max</span>
                                        <input type="number" class="form-control" id="maxInterestRate" step="0.0001" placeholder="0.90">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Users will receive a random rate between min and max each period (e.g., 0.30% to 0.90%)</small>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Plan Features -->
            <div class="section-card">
                <div class="section-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><iconify-icon icon="solar:star-circle-bold-duotone" class="me-2"></iconify-icon>Plan Features</h6>
                    <button type="button" class="btn btn-primary btn-sm" onclick="addFeature()">
                        <iconify-icon icon="solar:add-circle-bold-duotone" class="me-1"></iconify-icon>Add Feature
                    </button>
                </div>
                <div class="p-4">
                    <div id="featuresContainer">
                        <div class="input-group mb-2">
                            <span class="input-group-text"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success"></iconify-icon></span>
                            <input type="text" class="form-control feature-input" placeholder="Enter feature">
                            <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
                                <iconify-icon icon="solar:trash-bin-minimalistic-bold-duotone"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status -->
            <div class="section-card">
                <div class="section-header">
                    <h6 class="mb-0"><iconify-icon icon="solar:eye-bold-duotone" class="me-2"></iconify-icon>Plan Status</h6>
                </div>
                <div class="p-4">
                    <label class="form-label">Initial Status</label>
                    <select class="form-select" id="planStatus">
                        <option value="active">Active - Live immediately</option>
                        <option value="inactive" selected>Inactive - Save as draft</option>
                        <option value="paused">Paused - Visible but closed</option>
                    </select>
                </div>
            </div>

            <!-- Color Scheme -->
            <div class="section-card">
                <div class="section-header">
                    <h6 class="mb-0"><iconify-icon icon="solar:palette-bold-duotone" class="me-2"></iconify-icon>Color Scheme</h6>
                </div>
                <div class="p-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="color-box selected" onclick="selectColor('primary')" data-color="primary">
                                <div class="badge bg-primary w-100 mb-1">Primary</div>
                                <small>Blue</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="color-box" onclick="selectColor('success')" data-color="success">
                                <div class="badge bg-success w-100 mb-1">Success</div>
                                <small>Green</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="color-box" onclick="selectColor('warning')" data-color="warning">
                                <div class="badge bg-warning w-100 mb-1">Warning</div>
                                <small>Yellow</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="color-box" onclick="selectColor('danger')" data-color="danger">
                                <div class="badge bg-danger w-100 mb-1">Danger</div>
                                <small>Red</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="section-card">
                <div class="p-4">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success btn-lg" onclick="createPlan()">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="me-1"></iconify-icon>Create Investment Plan
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="saveDraft()">
                            <iconify-icon icon="solar:diskette-bold-duotone" class="me-1"></iconify-icon>Save as Draft
                        </button>
                        <a href="{{ route('admin.investment.index') }}" class="btn btn-outline-secondary">
                            <iconify-icon icon="solar:arrow-left-bold-duotone" class="me-1"></iconify-icon>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
let selectedColorScheme = 'primary';
let currentRoiType = 'fixed';

document.addEventListener('DOMContentLoaded', function() {
    toggleRoiType();
});

function toggleRoiType() {
    const isVariable = document.getElementById('roiVariable').checked;
    currentRoiType = isVariable ? 'variable' : 'fixed';
    
    if (isVariable) {
        document.getElementById('fixedRoiSection').classList.add('hidden');
        document.getElementById('variableRoiSection').classList.remove('hidden');
    } else {
        document.getElementById('fixedRoiSection').classList.remove('hidden');
        document.getElementById('variableRoiSection').classList.add('hidden');
    }
}

function addFeature() {
    const container = document.getElementById('featuresContainer');
    const featureDiv = document.createElement('div');
    featureDiv.className = 'input-group mb-2';
    featureDiv.innerHTML = `
        <span class="input-group-text"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success"></iconify-icon></span>
        <input type="text" class="form-control feature-input" placeholder="Enter feature">
        <button type="button" class="btn btn-outline-danger" onclick="removeFeature(this)">
            <iconify-icon icon="solar:trash-bin-minimalistic-bold-duotone"></iconify-icon>
        </button>
    `;
    container.appendChild(featureDiv);
}

function removeFeature(button) {
    const container = document.getElementById('featuresContainer');
    if (container.children.length > 1) {
        button.closest('.input-group').remove();
    }
}

function selectColor(color) {
    document.querySelectorAll('.color-box').forEach(box => {
        box.classList.remove('selected');
    });
    event.target.closest('.color-box').classList.add('selected');
    selectedColorScheme = color;
}

function createPlan() {
    const isVariable = document.getElementById('roiVariable').checked;
    
    const planData = {
        name: document.getElementById('planName').value.trim(),
        description: document.getElementById('planDescription').value.trim(),
        badge: document.getElementById('planBadge').value.trim(),
        sort_order: parseInt(document.getElementById('planSortOrder').value) || 0,
        interest_type: document.getElementById('interestType').value,
        duration_days: parseInt(document.getElementById('durationDays').value),
        return_type: 'fixed',
        capital_return: document.getElementById('capitalReturn').checked,
        status: document.getElementById('planStatus').value,
        color_scheme: selectedColorScheme,
        minimum_amount: parseFloat(document.getElementById('minimumAmount').value),
        maximum_amount: parseFloat(document.getElementById('maximumAmount').value),
        roi_type: isVariable ? 'variable' : 'fixed',
        is_tiered: false
    };
    
    // Validate basic fields
    if (!planData.name || !planData.interest_type || !planData.duration_days) {
        showAlert('Please fill in all required fields', 'danger');
        return;
    }
    
    if (!planData.minimum_amount || !planData.maximum_amount) {
        showAlert('Please fill in investment amount fields', 'danger');
        return;
    }
    
    // Get ROI values based on type
    if (isVariable) {
        planData.min_interest_rate = parseFloat(document.getElementById('minInterestRate').value);
        planData.max_interest_rate = parseFloat(document.getElementById('maxInterestRate').value);
        
        if (!planData.min_interest_rate || !planData.max_interest_rate) {
            showAlert('Please fill in both min and max interest rates', 'danger');
            return;
        }
        
        if (planData.min_interest_rate >= planData.max_interest_rate) {
            showAlert('Max rate must be greater than min rate', 'danger');
            return;
        }
    } else {
        planData.interest_rate = parseFloat(document.getElementById('interestRate').value);
        
        if (!planData.interest_rate) {
            showAlert('Please fill in interest rate', 'danger');
            return;
        }
    }
    
    // Get features
    const features = [];
    document.querySelectorAll('.feature-input').forEach(input => {
        if (input.value.trim()) {
            features.push(input.value.trim());
        }
    });
    planData.features = features;
    
    // Submit data
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
    button.disabled = true;
    
    fetch('{{ route("admin.investment.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(planData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Investment plan created successfully!', 'success');
            setTimeout(() => {
                window.location.href = '{{ route("admin.investment.index") }}';
            }, 1500);
        } else {
            showAlert(data.message || 'Error creating plan', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error occurred', 'danger');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

function saveDraft() {
    document.getElementById('planStatus').value = 'inactive';
    createPlan();
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 350px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 4000);
}
</script>
@endsection