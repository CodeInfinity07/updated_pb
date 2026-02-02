@extends('layouts.vertical', ['title' => 'Salary Program', 'subTitle' => 'Your Progress'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4 border-primary">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="card-title mb-0 text-white">
                            <iconify-icon icon="solar:wallet-money-bold-duotone" class="me-2"></iconify-icon>
                            Monthly Salary Program
                        </h4>
                        <p class="text-white-50 mb-0 mt-1">Currently at: {{ $progress['current_stage']->name }}</p>
                    </div>
                    <div class="text-end">
                        <h3 class="text-white mb-0">${{ number_format($progress['current_stage']->salary_amount, 2) }}</h3>
                        <small class="text-white-50">Monthly Salary</small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <iconify-icon icon="solar:calendar-bold-duotone" class="fs-32 text-primary mb-2"></iconify-icon>
                                <h6 class="text-muted mb-1">Current Period</h6>
                                <p class="mb-0 fw-semibold">{{ $progress['period']['start'] }} - {{ $progress['period']['end'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-32 text-warning mb-2"></iconify-icon>
                                <h6 class="text-muted mb-1">Days Remaining</h6>
                                <h3 class="mb-0 {{ $progress['period']['days_remaining'] <= 5 ? 'text-danger' : 'text-dark' }}">{{ $progress['period']['days_remaining'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light border-0 h-100">
                            <div class="card-body text-center">
                                <iconify-icon icon="solar:medal-ribbon-star-bold-duotone" class="fs-32 text-success mb-2"></iconify-icon>
                                <h6 class="text-muted mb-1">Months Completed</h6>
                                <h3 class="mb-0">{{ $progress['months_completed'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="mb-3">This Month's Targets</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border {{ $progress['team']['met'] ? 'border-success' : 'border-warning' }} h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="me-2 text-primary"></iconify-icon>
                                        New Team Members
                                    </h6>
                                    @if($progress['team']['met'])
                                        <span class="badge bg-success">Target Met!</span>
                                    @else
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-end justify-content-between mb-2">
                                    <div>
                                        <h2 class="mb-0 {{ $progress['team']['met'] ? 'text-success' : '' }}">{{ $progress['team']['current'] }}</h2>
                                        <small class="text-muted">of {{ $progress['team']['target'] }} required</small>
                                    </div>
                                    <h5 class="mb-0 {{ $progress['team']['progress'] >= 100 ? 'text-success' : 'text-muted' }}">{{ number_format($progress['team']['progress'], 1) }}%</h5>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar {{ $progress['team']['met'] ? 'bg-success' : 'bg-primary' }}" style="width: {{ $progress['team']['progress'] }}%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">Need {{ max(0, $progress['team']['target'] - $progress['team']['current']) }} more new team members</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border {{ $progress['direct']['met'] ? 'border-success' : 'border-warning' }} h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="me-2 text-primary"></iconify-icon>
                                        New Direct Members
                                    </h6>
                                    @if($progress['direct']['met'])
                                        <span class="badge bg-success">Target Met!</span>
                                    @else
                                        <span class="badge bg-warning text-dark">In Progress</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-end justify-content-between mb-2">
                                    <div>
                                        <h2 class="mb-0 {{ $progress['direct']['met'] ? 'text-success' : '' }}">{{ $progress['direct']['current'] }}</h2>
                                        <small class="text-muted">of {{ $progress['direct']['target'] }} required</small>
                                    </div>
                                    <h5 class="mb-0 {{ $progress['direct']['progress'] >= 100 ? 'text-success' : 'text-muted' }}">{{ number_format($progress['direct']['progress'], 1) }}%</h5>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar {{ $progress['direct']['met'] ? 'bg-success' : 'bg-primary' }}" style="width: {{ $progress['direct']['progress'] }}%"></div>
                                </div>
                                <small class="text-muted mt-2 d-block">Need {{ max(0, $progress['direct']['target'] - $progress['direct']['current']) }} more direct referrals (min $50 investment each)</small>
                            </div>
                        </div>
                    </div>
                </div>

                @if($progress['team']['met'] && $progress['direct']['met'])
                    <div class="alert alert-success d-flex align-items-center">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-24 me-3"></iconify-icon>
                        <div>
                            <strong>Great job!</strong> You've met both targets for this month. Your salary of <strong>${{ number_format($progress['current_salary'], 2) }}</strong> will be credited at month end.
                            @if($progress['can_advance'] && $progress['next_stage'])
                                <br><small class="text-success">You qualify for {{ $progress['next_stage']->name }} next month!</small>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning d-flex align-items-center">
                        <iconify-icon icon="solar:alarm-bold-duotone" class="fs-24 me-3"></iconify-icon>
                        <div>
                            <strong>Keep pushing!</strong> You have {{ $progress['period']['days_remaining'] }} days left to meet your targets. Don't give up!
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if($progress['evaluations']->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Payment History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-centered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Period</th>
                                <th>Stage</th>
                                <th>Team Target</th>
                                <th>Direct Target</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($progress['evaluations'] as $eval)
                            <tr>
                                <td>Month {{ $eval->month_number }}</td>
                                <td>{{ $eval->period_start->format('M d') }} - {{ $eval->period_end->format('M d, Y') }}</td>
                                <td>{{ $eval->salaryStage->name }}</td>
                                <td>
                                    {{ $eval->achieved_team_new }} / {{ $eval->target_team }}
                                    @if($eval->metTeamTarget())
                                        <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                    @else
                                        <iconify-icon icon="solar:close-circle-bold" class="text-danger"></iconify-icon>
                                    @endif
                                </td>
                                <td>
                                    {{ $eval->achieved_direct_new }} / {{ $eval->target_direct_new }}
                                    @if($eval->metDirectTarget())
                                        <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                    @else
                                        <iconify-icon icon="solar:close-circle-bold" class="text-danger"></iconify-icon>
                                    @endif
                                </td>
                                <td>
                                    @if($eval->passed)
                                        @if($eval->salary_paid)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-info">Pending Payment</span>
                                        @endif
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $eval->passed ? 'text-success' : 'text-muted' }}">
                                    @if($eval->salary_amount)
                                        ${{ number_format($eval->salary_amount, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
