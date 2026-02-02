@extends('admin.layouts.vertical', ['title' => 'Salary Evaluations', 'subTitle' => 'Admin'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['total'] }}</h4>
                        <p class="mb-0">Total Evaluations</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['passed'] }}</h4>
                        <p class="mb-0">Passed</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['failed'] }}</h4>
                        <p class="mb-0">Failed</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h4 class="text-dark mb-1">${{ number_format($stats['total_paid'], 2) }}</h4>
                        <p class="mb-0">Total Paid</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['pending_payments'] ?? 0 }}</h4>
                        <p class="mb-0">Pending Approval</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Monthly Evaluations</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary btn-sm" id="runEvaluationBtn">
                            <iconify-icon icon="solar:play-bold"></iconify-icon> Run Evaluation
                        </button>
                        <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Search user..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="passed" class="form-select">
                            <option value="">All Results</option>
                            <option value="1" {{ request('passed') === '1' ? 'selected' : '' }}>Passed</option>
                            <option value="0" {{ request('passed') === '0' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="paid" class="form-select">
                            <option value="">All Payment</option>
                            <option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-centered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Month</th>
                                <th>Period</th>
                                <th>Team</th>
                                <th>Direct</th>
                                <th>Stage</th>
                                <th>Result</th>
                                <th>Amount</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluations as $eval)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $eval->user) }}">
                                        {{ $eval->user->first_name }} {{ $eval->user->last_name }}
                                    </a>
                                </td>
                                <td>{{ $eval->month_number }}</td>
                                <td>{{ $eval->period_start->format('M d') }} - {{ $eval->period_end->format('M d') }}</td>
                                <td>
                                    {{ $eval->achieved_team_new }} / {{ $eval->target_team }}
                                    @if($eval->achieved_team_new >= $eval->target_team)
                                        <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                    @else
                                        <iconify-icon icon="solar:close-circle-bold" class="text-danger"></iconify-icon>
                                    @endif
                                </td>
                                <td>
                                    {{ $eval->achieved_direct_new }} / {{ $eval->target_direct_new }}
                                    @if($eval->achieved_direct_new >= $eval->target_direct_new)
                                        <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                    @else
                                        <iconify-icon icon="solar:close-circle-bold" class="text-danger"></iconify-icon>
                                    @endif
                                </td>
                                <td>{{ $eval->salaryStage->name }}</td>
                                <td>
                                    @if($eval->passed)
                                        <span class="badge bg-success">Passed</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $eval->salary_amount ? 'text-success' : 'text-muted' }}">
                                    {{ $eval->salary_amount ? '$' . number_format($eval->salary_amount, 2) : '-' }}
                                </td>
                                <td>
                                    @if($eval->salary_paid)
                                        <span class="badge bg-success">Paid</span>
                                        <br><small class="text-muted">{{ $eval->paid_at->format('M d') }}</small>
                                    @elseif($eval->passed)
                                        <button type="button" 
                                            class="btn btn-success btn-sm approve-payment-btn"
                                            data-evaluation-id="{{ $eval->id }}"
                                            data-amount="{{ $eval->salary_amount }}"
                                            data-user="{{ $eval->user->first_name }} {{ $eval->user->last_name }}">
                                            <iconify-icon icon="solar:check-circle-bold"></iconify-icon> Approve & Pay
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No evaluations found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $evaluations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
document.getElementById('runEvaluationBtn').addEventListener('click', function() {
    if (!confirm('Run salary evaluation for all due applications?')) return;
    
    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Running...';
    
    fetch('{{ route("admin.salary.run-evaluation") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
        if (data.passed > 0 || data.failed > 0) {
            location.reload();
        }
    })
    .catch(error => {
        alert('Error running evaluation');
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<iconify-icon icon="solar:play-bold"></iconify-icon> Run Evaluation';
    });
});

document.querySelectorAll('.approve-payment-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const evaluationId = this.dataset.evaluationId;
        const amount = this.dataset.amount;
        const userName = this.dataset.user;
        
        if (!confirm(`Approve and pay $${parseFloat(amount).toFixed(2)} salary to ${userName}?`)) return;
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        
        fetch(`{{ url('admin/salary/evaluations') }}/${evaluationId}/approve-payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Failed to process payment');
                this.disabled = false;
                this.innerHTML = '<iconify-icon icon="solar:check-circle-bold"></iconify-icon> Approve & Pay';
            }
        })
        .catch(error => {
            alert('Error processing payment');
            this.disabled = false;
            this.innerHTML = '<iconify-icon icon="solar:check-circle-bold"></iconify-icon> Approve & Pay';
        });
    });
});
</script>
@endsection
