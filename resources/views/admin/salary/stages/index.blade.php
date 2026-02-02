@extends('admin.layouts.vertical', ['title' => 'Salary Stages', 'subTitle' => 'Manage Salary Stage Requirements'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Salary Stages Configuration</h4>
                            <p class="text-muted mb-0">Configure requirements and amounts for each salary stage</p>
                        </div>
                        <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="iconamoon:arrow-left-duotone" class="me-1"></iconify-icon>
                            Back to Overview
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <iconify-icon icon="iconamoon:information-circle-duotone" class="me-2"></iconify-icon>
                <strong>Note:</strong> Users must complete stages sequentially. Referrals counted for one stage cannot be reused for subsequent stages. Minimum investment per referral is $50.
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Salary Stages</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Stage</th>
                                    <th class="text-center">Direct Members (L1)</th>
                                    <th class="text-center">Self Deposit</th>
                                    <th class="text-center">Team (L2+L3)</th>
                                    <th class="text-center">Salary Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stages as $stage)
                                <tr id="stage-row-{{ $stage->id }}">
                                    <td>
                                        <span class="fw-semibold">{{ $stage->name }}</span>
                                        <br>
                                        <small class="text-muted">Order: {{ $stage->stage_order }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6" id="direct-{{ $stage->id }}">{{ $stage->direct_members_required }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6" id="deposit-{{ $stage->id }}">${{ number_format($stage->self_deposit_required, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info fs-6" id="team-{{ $stage->id }}">{{ $stage->team_required }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark fs-5 fw-bold" id="salary-{{ $stage->id }}">${{ number_format($stage->salary_amount, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" id="active-{{ $stage->id }}" 
                                                {{ $stage->is_active ? 'checked' : '' }} 
                                                onchange="toggleStage({{ $stage->id }})">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="editStage({{ $stage->id }})">
                                            <iconify-icon icon="iconamoon:edit-duotone"></iconify-icon>
                                            Edit
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editStageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Salary Stage</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStageForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-stage-id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Stage Name</label>
                        <input type="text" class="form-control" id="edit-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Direct Members Required (L1)</label>
                        <input type="number" class="form-control" id="edit-direct" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Self Deposit Required ($)</label>
                        <input type="number" class="form-control" id="edit-deposit" min="0" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Team Members Required (L2+L3)</label>
                        <input type="number" class="form-control" id="edit-team" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Salary Amount ($)</label>
                        <input type="number" class="form-control" id="edit-salary" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
const stagesData = @json($stages);

function editStage(stageId) {
    const stage = stagesData.find(s => s.id === stageId);
    if (!stage) return;
    
    document.getElementById('edit-stage-id').value = stage.id;
    document.getElementById('edit-name').value = stage.name;
    document.getElementById('edit-direct').value = stage.direct_members_required;
    document.getElementById('edit-deposit').value = stage.self_deposit_required;
    document.getElementById('edit-team').value = stage.team_required;
    document.getElementById('edit-salary').value = stage.salary_amount;
    
    new bootstrap.Modal(document.getElementById('editStageModal')).show();
}

document.getElementById('editStageForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const stageId = document.getElementById('edit-stage-id').value;
    const data = {
        name: document.getElementById('edit-name').value,
        direct_members_required: parseInt(document.getElementById('edit-direct').value),
        self_deposit_required: parseFloat(document.getElementById('edit-deposit').value),
        team_required: parseInt(document.getElementById('edit-team').value),
        salary_amount: parseFloat(document.getElementById('edit-salary').value)
    };
    
    fetch(`/admin/salary/stages/${stageId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('direct-' + stageId).textContent = data.direct_members_required;
            document.getElementById('deposit-' + stageId).textContent = '$' + data.self_deposit_required.toFixed(2);
            document.getElementById('team-' + stageId).textContent = data.team_required;
            document.getElementById('salary-' + stageId).textContent = '$' + data.salary_amount.toFixed(2);
            
            const stageIndex = stagesData.findIndex(s => s.id === parseInt(stageId));
            if (stageIndex !== -1) {
                stagesData[stageIndex] = { ...stagesData[stageIndex], ...data };
            }
            
            bootstrap.Modal.getInstance(document.getElementById('editStageModal')).hide();
            alert('Stage updated successfully');
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        alert('Error updating stage');
        console.error(error);
    });
});

function toggleStage(stageId) {
    fetch(`/admin/salary/stages/${stageId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(result => {
        if (!result.success) {
            document.getElementById('active-' + stageId).checked = !document.getElementById('active-' + stageId).checked;
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        document.getElementById('active-' + stageId).checked = !document.getElementById('active-' + stageId).checked;
        alert('Error toggling stage');
        console.error(error);
    });
}
</script>
@endsection
