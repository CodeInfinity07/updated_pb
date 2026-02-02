@extends('admin.layouts.vertical', ['title' => 'Commission Management', 'subTitle' => 'Admin'])

@section('title', 'Commission Settings')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Profit Sharing Levels</h5>
                        <small class="text-muted">Configure profit sharing percentages for each referral level (based on daily ROI)</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetDefaults()">
                            <iconify-icon icon="solar:refresh-bold-duotone" class="me-1"></iconify-icon>
                            Reset Defaults
                        </button>
                        <button type="button" class="btn btn-primary" onclick="saveCommissions()">
                            <iconify-icon icon="solar:diskette-bold-duotone" class="me-1"></iconify-icon>
                            Save Changes
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="alertContainer"></div>

                    <div class="card border border-primary mb-4">
                        <div class="card-header bg-primary bg-opacity-10">
                            <h6 class="mb-0 text-primary">
                                <iconify-icon icon="solar:user-plus-bold-duotone" class="me-2"></iconify-icon>
                                Direct Sponsor Commission (On Investment)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label">Commission Percentage</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control" 
                                               id="directSponsorCommission"
                                               value="{{ $directSponsorCommission ?? 8 }}"
                                               min="0" 
                                               max="100" 
                                               step="0.01"
                                               placeholder="8.00">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-light mb-0 mt-3 mt-md-0">
                                        <iconify-icon icon="solar:info-circle-bold-duotone" class="me-1"></iconify-icon>
                                        <small>This commission is paid to the direct sponsor (referrer) whenever a user makes a new investment. It's a one-time payment based on the investment amount.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Profit Sharing Shield Setting --}}
                    <div class="card border border-warning mb-4">
                        <div class="card-header bg-warning bg-opacity-10">
                            <h6 class="mb-0 text-warning">
                                <iconify-icon icon="solar:shield-bold-duotone" class="me-2"></iconify-icon>
                                Profit Sharing Shield
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" 
                                       id="profitSharingShield" 
                                       {{ ($profitSharingShieldEnabled ?? false) ? 'checked' : '' }}
                                       onchange="toggleMinInvestmentField()">
                                <label class="form-check-label fw-semibold" for="profitSharingShield">
                                    Enable Profit Sharing Shield
                                </label>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted d-block">
                                    When enabled, profit sharing eligibility depends on the number of direct referrals and their combined investment amount.
                                </small>
                            </div>

                            <div id="minInvestmentSection" class="mt-3 p-3 bg-light rounded" style="{{ ($profitSharingShieldEnabled ?? false) ? '' : 'display: none;' }}">
                                <label class="form-label fw-semibold">Minimum Combined Investment ($)</label>
                                <div class="input-group" style="max-width: 250px;">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="profitSharingShieldMinInvestment"
                                           value="{{ $profitSharingShieldMinInvestment ?? 0 }}"
                                           min="0" 
                                           step="0.01"
                                           placeholder="0.00">
                                </div>
                                <small class="text-muted d-block mt-2">
                                    For Level N, the combined investment of N direct referrals must meet this minimum.
                                </small>
                                <div class="alert alert-light mt-3 mb-0 py-2">
                                    <small>
                                        <strong>Example (Minimum: $50):</strong><br>
                                        • Level 1 → Need 1 referral with ≥ $50 invested<br>
                                        • Level 2 → Need 2 referrals with combined ≥ $50 (e.g., $30 + $20)<br>
                                        • Level 3 → Need 3 referrals with combined ≥ $50 (e.g., $20 + $15 + $15)<br>
                                        • And so on...
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-4">
                        <iconify-icon icon="solar:info-circle-bold-duotone" class="me-2"></iconify-icon>
                        <strong>Profit Sharing Levels:</strong> When a user earns daily ROI, their upline chain (up to 10 levels) receives profit share based on these percentages.
                        <br><small>Level 1 = Direct referral, Level 2 = Referral's referral, and so on.</small>
                    </div>

                    <div class="row g-4">
                        @for($i = 1; $i <= 10; $i++)
                        @php
                            $level = $levels->firstWhere('level', $i);
                            $percentage = $level ? $level->percentage : 0;
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-{{ $i <= 3 ? 'primary' : ($i <= 6 ? 'info' : 'secondary') }} rounded-pill me-2">
                                            Level {{ $i }}
                                        </span>
                                        <span class="text-muted small">
                                            @switch($i)
                                                @case(1)
                                                    Direct Referrals
                                                    @break
                                                @case(2)
                                                    2nd Generation
                                                    @break
                                                @case(3)
                                                    3rd Generation
                                                    @break
                                                @default
                                                    {{ $i }}th Generation
                                            @endswitch
                                        </span>
                                    </div>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control commission-input" 
                                               id="level{{ $i }}"
                                               data-level="{{ $i }}"
                                               value="{{ $percentage }}"
                                               min="0" 
                                               max="100" 
                                               step="0.01"
                                               placeholder="0.00">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        Percentage of ROI earned by Level {{ $i }} upline
                                    </small>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Commission Calculator</h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Daily ROI Amount ($)</label>
                                    <input type="number" class="form-control" id="testRoiAmount" value="10" min="0.01" step="0.01">
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-outline-primary" onclick="calculatePreview()">
                                        <iconify-icon icon="solar:calculator-bold-duotone" class="me-1"></iconify-icon>
                                        Calculate
                                    </button>
                                </div>
                            </div>
                            <div id="calculationResult" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
function toggleMinInvestmentField() {
    const isChecked = document.getElementById('profitSharingShield').checked;
    const section = document.getElementById('minInvestmentSection');
    section.style.display = isChecked ? 'block' : 'none';
}

function showAlert(message, type = 'success') {
    const container = document.getElementById('alertContainer');
    container.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    setTimeout(() => {
        const alert = container.querySelector('.alert');
        if (alert) alert.remove();
    }, 5000);
}

function saveCommissions() {
    const levels = [];
    for (let i = 1; i <= 10; i++) {
        const input = document.getElementById(`level${i}`);
        levels.push({
            level: i,
            percentage: parseFloat(input.value) || 0
        });
    }

    const directSponsorCommission = parseFloat(document.getElementById('directSponsorCommission').value) || 0;
    const profitSharingShieldEnabled = document.getElementById('profitSharingShield').checked;
    const profitSharingShieldMinInvestment = parseFloat(document.getElementById('profitSharingShieldMinInvestment').value) || 0;

    fetch('{{ route("admin.commission.update") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
            levels: levels,
            direct_sponsor_commission: directSponsorCommission,
            profit_sharing_shield_enabled: profitSharingShieldEnabled,
            profit_sharing_shield_min_investment: profitSharingShieldMinInvestment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
        } else {
            showAlert(data.message || 'Error saving', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error occurred', 'danger');
    });
}

function resetDefaults() {
    if (!confirm('Reset all commission percentages to default values?')) return;

    fetch('{{ route("admin.commission.reset") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Error resetting', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Network error occurred', 'danger');
    });
}

function calculatePreview() {
    const roiAmount = parseFloat(document.getElementById('testRoiAmount').value) || 0;
    
    if (roiAmount <= 0) {
        showAlert('Please enter a valid ROI amount', 'warning');
        return;
    }

    const resultContainer = document.getElementById('calculationResult');
    let html = '<table class="table table-sm table-bordered"><thead><tr><th>Level</th><th>Percentage</th><th>Commission</th></tr></thead><tbody>';
    let totalCommission = 0;

    for (let i = 1; i <= 10; i++) {
        const percentage = parseFloat(document.getElementById(`level${i}`).value) || 0;
        const commission = (roiAmount * percentage) / 100;
        totalCommission += commission;
        
        html += `<tr>
            <td>Level ${i}</td>
            <td>${percentage.toFixed(2)}%</td>
            <td>$${commission.toFixed(2)}</td>
        </tr>`;
    }

    html += `</tbody><tfoot><tr class="table-primary"><td colspan="2"><strong>Total Profit Share</strong></td><td><strong>$${totalCommission.toFixed(2)}</strong></td></tr></tfoot></table>`;
    html += `<p class="text-muted mt-2 mb-0">From a $${roiAmount.toFixed(2)} daily ROI, the upline chain would receive $${totalCommission.toFixed(2)} total in profit share.</p>`;
    
    resultContainer.innerHTML = html;
}
</script>
@endsection
