@extends('layouts.vertical', ['title' => 'Salary History', 'subTitle' => 'Payment History'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <iconify-icon icon="solar:history-bold-duotone" class="me-2"></iconify-icon>
                        Salary Payment History
                    </h4>
                    <a href="{{ route('salary.index') }}" class="btn btn-outline-primary btn-sm">
                        <iconify-icon icon="solar:arrow-left-linear" class="me-1"></iconify-icon>
                        Back to Salary
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success-subtle border-0">
                            <div class="card-body text-center">
                                <h6 class="text-success mb-2">Total Earned</h6>
                                <h3 class="text-success mb-0">${{ number_format($totalEarned, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-primary-subtle border-0">
                            <div class="card-body text-center">
                                <h6 class="text-primary mb-2">Successful Months</h6>
                                <h3 class="text-primary mb-0">{{ $evaluations->where('passed', true)->count() }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary-subtle border-0">
                            <div class="card-body text-center">
                                <h6 class="text-secondary mb-2">Total Evaluations</h6>
                                <h3 class="text-secondary mb-0">{{ $evaluations->total() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                @if($evaluations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-centered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Period</th>
                                <th>Stage</th>
                                <th>Team Progress</th>
                                <th>Direct Progress</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluations as $eval)
                            <tr>
                                <td>{{ $eval->created_at->format('M d, Y') }}</td>
                                <td>
                                    <small>{{ $eval->period_start->format('M d') }} - {{ $eval->period_end->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary">{{ $eval->salaryStage->name }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $eval->achieved_team_new }} / {{ $eval->target_team }}</span>
                                        @if($eval->achieved_team_new >= $eval->target_team)
                                            <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                        @else
                                            <iconify-icon icon="solar:close-circle-bold" class="text-danger"></iconify-icon>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="me-2">{{ $eval->achieved_direct_new }} / {{ $eval->target_direct_new }}</span>
                                        @if($eval->achieved_direct_new >= $eval->target_direct_new)
                                            <iconify-icon icon="solar:check-circle-bold" class="text-success"></iconify-icon>
                                        @else
                                            <iconify-icon icon="solar:close-circle-bold" class="text-danger"></iconify-icon>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($eval->passed)
                                        @if($eval->salary_paid)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-info">Pending</span>
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

                <div class="mt-3">
                    {{ $evaluations->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <iconify-icon icon="solar:document-text-bold-duotone" class="fs-48 text-muted mb-3"></iconify-icon>
                    <h5 class="text-muted">No Payment History Yet</h5>
                    <p class="text-muted mb-0">Your salary payment history will appear here once you complete your first month.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
