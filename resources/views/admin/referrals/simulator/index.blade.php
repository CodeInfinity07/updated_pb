@extends('admin.layouts.vertical', ['title' => 'Commission & ROI Simulator', 'subTitle' => 'Admin'])

@section('content')

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                    <div>
                        <h4 class="mb-1">Commission & ROI Simulator</h4>
                        <p class="text-muted mb-0">Simulate ROI earnings and commission distributions for any user</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Select User to Simulate</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Select User</label>
                    <select id="userSelect" class="form-select" style="width: 100%;">
                        <option value="">Search for a user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user['id'] }}" 
                                data-username="{{ $user['username'] }}"
                                data-email="{{ $user['email'] }}"
                                data-level="{{ $user['level'] }}"
                                data-invested="{{ $user['total_invested'] }}">
                                {{ $user['username'] }} - {{ $user['full_name'] }} ({{ $user['email'] }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Downline Investment Amount (for referral commission calculation)</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" id="investmentAmount" class="form-control" value="1000" min="0" step="100">
                    </div>
                    <small class="text-muted">Enter the amount a new downline would invest</small>
                </div>
                <button type="button" class="btn btn-primary w-100" onclick="runSimulation()">
                    <iconify-icon icon="mdi:calculator" class="me-1"></iconify-icon>
                    Run Simulation
                </button>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card" id="userInfoCard" style="display: none;">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0 text-white">Selected User Info</h5>
            </div>
            <div class="card-body">
                <div id="userInfoContent"></div>
            </div>
        </div>
    </div>
</div>

<div id="simulationResults" style="display: none;">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="card-title mb-0 text-white">
                        <iconify-icon icon="mdi:chart-line" class="me-2"></iconify-icon>
                        Tomorrow's ROI Earnings
                    </h5>
                </div>
                <div class="card-body">
                    <div id="roiResults"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0 text-white">
                        <iconify-icon icon="mdi:account-group" class="me-2"></iconify-icon>
                        Sponsor Chain Profit Share (When User Earns ROI)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">When this user earns ROI, their upline sponsors receive a percentage as profit share.</p>
                    <div id="profitShareResults"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0 text-dark">
                        <iconify-icon icon="mdi:cash-multiple" class="me-2"></iconify-icon>
                        Referral Commission (When Downline Invests)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Commission this user will receive when their next downline makes an investment.</p>
                    <div id="referralCommissionResults"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="loadingOverlay" style="display: none;" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    <p class="mt-2">Running simulation...</p>
</div>

@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('userSelect');
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            if (this.value) {
                showUserInfo(this);
            } else {
                document.getElementById('userInfoCard').style.display = 'none';
            }
        });
    }
});

function showUserInfo(select) {
    const option = select.options[select.selectedIndex];
    if (!option.value) return;
    
    const username = option.dataset.username;
    const email = option.dataset.email;
    const level = option.dataset.level;
    const invested = parseFloat(option.dataset.invested || 0);
    
    document.getElementById('userInfoCard').style.display = 'block';
    document.getElementById('userInfoContent').innerHTML = `
        <table class="table table-sm mb-0">
            <tr><td><strong>Username:</strong></td><td>${username}</td></tr>
            <tr><td><strong>Email:</strong></td><td>${email}</td></tr>
            <tr><td><strong>Profile Level:</strong></td><td><span class="badge bg-primary">Level ${level}</span></td></tr>
            <tr><td><strong>Total Invested:</strong></td><td>$${invested.toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
        </table>
    `;
}

function runSimulation() {
    const userId = document.getElementById('userSelect').value;
    const investmentAmount = document.getElementById('investmentAmount').value;
    
    if (!userId) {
        alert('Please select a user first');
        return;
    }
    
    document.getElementById('loadingOverlay').style.display = 'block';
    document.getElementById('simulationResults').style.display = 'none';
    
    fetch('{{ route("admin.referrals.simulator.simulate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            user_id: userId,
            downline_investment_amount: investmentAmount
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingOverlay').style.display = 'none';
        
        if (data.success) {
            displayResults(data.data);
            document.getElementById('simulationResults').style.display = 'block';
        } else {
            alert('Simulation failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').style.display = 'none';
        alert('Error running simulation: ' + error.message);
    });
}

function displayResults(data) {
    displayRoiResults(data.tomorrow_roi);
    displayProfitShareResults(data.sponsor_chain_profit_share);
    displayReferralCommissionResults(data.referral_commission, data.downline_investment_amount);
}

function displayRoiResults(roiData) {
    let html = '';
    
    if (roiData.investments.length === 0) {
        html = '<div class="alert alert-warning mb-0">This user has no active investments.</div>';
    } else {
        html = `
            <div class="alert alert-success mb-3">
                <strong>Total ROI Tomorrow: $${roiData.total_roi.toLocaleString('en-US', {minimumFractionDigits: 4})}</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="table-light">
                        <tr>
                            <th>Plan</th>
                            <th class="text-end">Investment</th>
                            <th class="text-end">ROI %</th>
                            <th class="text-end">ROI Amount</th>
                            <th class="text-end">Accumulated</th>
                            <th class="text-end">Expiry Cap</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        roiData.investments.forEach(inv => {
            const statusBadge = inv.status === 'active' ? 'bg-success' : 
                               (inv.status === 'capped' ? 'bg-warning' : 'bg-danger');
            html += `
                <tr>
                    <td>${inv.plan_name}</td>
                    <td class="text-end">$${inv.amount.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="text-end">${inv.roi_percentage}%</td>
                    <td class="text-end text-success fw-bold">$${inv.roi_amount.toLocaleString('en-US', {minimumFractionDigits: 4})}</td>
                    <td class="text-end">$${inv.earnings_accumulated.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td class="text-end">$${inv.expiry_cap.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                    <td><span class="badge ${statusBadge}">${inv.status.toUpperCase()}</span></td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
    }
    
    document.getElementById('roiResults').innerHTML = html;
}

function displayProfitShareResults(profitShareData) {
    let html = '';
    
    if (profitShareData.roi_amount <= 0) {
        html = `<div class="alert alert-warning mb-0">${profitShareData.message || 'No ROI to distribute.'}</div>`;
    } else {
        html = `
            <div class="alert alert-info mb-3">
                <strong>ROI Amount Being Distributed: $${profitShareData.roi_amount.toLocaleString('en-US', {minimumFractionDigits: 4})}</strong>
                <br><small>Total to be distributed to uplines: $${profitShareData.total_distributed.toLocaleString('en-US', {minimumFractionDigits: 4})}</small>
            </div>
        `;
        
        if (profitShareData.profit_sharing_shield_enabled) {
            html += `
                <div class="alert alert-secondary mb-3">
                    <iconify-icon icon="mdi:shield" class="me-1"></iconify-icon>
                    <strong>Profit Sharing Shield is ENABLED</strong>
                    <br><small>Min Investment Required: $${profitShareData.profit_sharing_shield_min_investment}</small>
                </div>
            `;
        }
        
        if (profitShareData.chain.length === 0) {
            html += '<div class="alert alert-secondary">No sponsors in the upline chain.</div>';
        } else {
            html += `
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Level</th>
                                <th>Sponsor</th>
                                <th>User Level</th>
                                <th class="text-end">Percentage</th>
                                <th class="text-end">Commission</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            profitShareData.chain.forEach(sponsor => {
                const statusBadge = sponsor.will_receive ? 'bg-success' : 'bg-danger';
                const statusText = sponsor.will_receive ? 'WILL RECEIVE' : 'SKIPPED';
                html += `
                    <tr class="${sponsor.will_receive ? '' : 'table-danger'}">
                        <td><span class="badge bg-secondary">L${sponsor.level}</span></td>
                        <td>
                            <strong>${sponsor.username}</strong>
                            <br><small class="text-muted">${sponsor.full_name}</small>
                        </td>
                        <td><span class="badge bg-primary">Level ${sponsor.user_level}</span></td>
                        <td class="text-end">${sponsor.percentage}%</td>
                        <td class="text-end fw-bold ${sponsor.will_receive ? 'text-success' : 'text-muted'}">
                            $${sponsor.commission_amount.toLocaleString('en-US', {minimumFractionDigits: 4})}
                        </td>
                        <td>
                            <span class="badge ${statusBadge}">${statusText}</span>
                            ${sponsor.skip_reason ? `<br><small class="text-danger">${sponsor.skip_reason}</small>` : ''}
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table></div>';
        }
    }
    
    document.getElementById('profitShareResults').innerHTML = html;
}

function displayReferralCommissionResults(commissionData, investmentAmount) {
    const dsc = commissionData.direct_sponsor_commission;
    const tbc = commissionData.tier_based_commission;
    
    let html = `
        <div class="alert alert-warning mb-3">
            <strong>If a new downline invests: $${investmentAmount.toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0 text-white">Direct Sponsor Commission (Current System)</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm mb-0">
                            <tr>
                                <td>Commission Rate:</td>
                                <td class="text-end"><strong>${dsc.percentage}%</strong></td>
                            </tr>
                            <tr>
                                <td>Commission Amount:</td>
                                <td class="text-end text-success fw-bold">$${dsc.amount.toLocaleString('en-US', {minimumFractionDigits: 4})}</td>
                            </tr>
                            <tr>
                                <td>Will Receive:</td>
                                <td class="text-end">
                                    <span class="badge ${dsc.will_receive ? 'bg-success' : 'bg-danger'}">
                                        ${dsc.will_receive ? 'YES' : 'NO'}
                                    </span>
                                    ${dsc.skip_reason ? `<br><small class="text-danger">${dsc.skip_reason}</small>` : ''}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0 text-white">Tier-Based Commission (Old System Reference)</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>User Profile Level:</strong> 
                            <span class="badge bg-primary">Level ${tbc.user_profile_level}</span>
                        </p>
                        <p class="mb-2">
                            <strong>Tier:</strong> 
                            <span class="badge bg-info">${tbc.tier_name}</span>
                        </p>
    `;
    
    if (tbc.commissions && Object.keys(tbc.commissions).length > 0) {
        html += `
            <table class="table table-sm mb-0">
                <thead>
                    <tr><th>Level</th><th class="text-end">Rate</th><th class="text-end">Amount</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Level 1</td>
                        <td class="text-end">${tbc.commissions.level_1.percentage}%</td>
                        <td class="text-end">$${tbc.commissions.level_1.amount.toLocaleString('en-US', {minimumFractionDigits: 4})}</td>
                    </tr>
                    <tr>
                        <td>Level 2</td>
                        <td class="text-end">${tbc.commissions.level_2.percentage}%</td>
                        <td class="text-end">$${tbc.commissions.level_2.amount.toLocaleString('en-US', {minimumFractionDigits: 4})}</td>
                    </tr>
                    <tr>
                        <td>Level 3</td>
                        <td class="text-end">${tbc.commissions.level_3.percentage}%</td>
                        <td class="text-end">$${tbc.commissions.level_3.amount.toLocaleString('en-US', {minimumFractionDigits: 4})}</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="2"><strong>Total</strong></td>
                        <td class="text-end fw-bold">$${tbc.total_tier_commission.toLocaleString('en-US', {minimumFractionDigits: 4})}</td>
                    </tr>
                </tbody>
            </table>
        `;
    } else {
        html += '<p class="text-muted mb-0">No tier configuration found for this user\'s level.</p>';
    }
    
    html += `
                    </div>
                </div>
            </div>
        </div>
        
        <div class="alert alert-secondary">
            <iconify-icon icon="mdi:information" class="me-1"></iconify-icon>
            <strong>Note:</strong> ${commissionData.note}
        </div>
    `;
    
    document.getElementById('referralCommissionResults').innerHTML = html;
}
</script>
@endsection
