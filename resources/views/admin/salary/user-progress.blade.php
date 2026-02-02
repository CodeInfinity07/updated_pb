@extends('admin.layouts.vertical', ['title' => 'User Salary Progress', 'subTitle' => 'Admin'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        Salary Progress: {{ $user->first_name }} {{ $user->last_name }}
                    </h4>
                    <a href="{{ route('admin.salary.applications') }}" class="btn btn-outline-secondary btn-sm">
                        <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>User Details</h6>
                        <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                        <p class="mb-1"><strong>Username:</strong> {{ $user->username }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Eligibility Status</h6>
                        @if($eligibility['eligible'])
                            <span class="badge bg-success">Eligible to Apply</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $eligibility['reason'])) }}</span>
                        @endif
                    </div>
                </div>

                @if($application)
                <div class="alert alert-info">
                    <h6 class="alert-heading">Current Application</h6>
                    <p class="mb-1"><strong>Stage:</strong> {{ $application->salaryStage->name ?? 'N/A' }}</p>
                    <p class="mb-1"><strong>Status:</strong> <span class="badge bg-{{ $application->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($application->status) }}</span></p>
                    <p class="mb-1"><strong>Applied:</strong> {{ $application->applied_at->format('M d, Y') }}</p>
                    <p class="mb-0"><strong>Months Completed:</strong> {{ $application->months_completed }}</p>
                </div>
                @else
                <div class="alert alert-secondary">
                    No active application found.
                </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Evaluation History</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th>Period</th>
                                <th>Stage</th>
                                <th>Team</th>
                                <th>Direct</th>
                                <th>Result</th>
                                <th>Amount</th>
                                <th>Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($evaluations as $eval)
                            <tr>
                                <td>{{ $eval->month_number }}</td>
                                <td>{{ $eval->period_start->format('M d') }} - {{ $eval->period_end->format('M d, Y') }}</td>
                                <td>{{ $eval->salaryStage->name ?? 'N/A' }}</td>
                                <td>{{ $eval->achieved_team_new }} / {{ $eval->target_team }}</td>
                                <td>{{ $eval->achieved_direct_new }} / {{ $eval->target_direct_new }}</td>
                                <td>
                                    @if($eval->passed)
                                        <span class="badge bg-success">Passed</span>
                                    @else
                                        <span class="badge bg-danger">Failed</span>
                                    @endif
                                </td>
                                <td>{{ $eval->salary_amount ? '$' . number_format($eval->salary_amount, 2) : '-' }}</td>
                                <td>
                                    @if($eval->salary_paid)
                                        <span class="badge bg-success">{{ $eval->paid_at->format('M d') }}</span>
                                    @elseif($eval->passed)
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No evaluations found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
