@extends('admin.layouts.vertical', ['title' => 'Monthly Salary', 'subTitle' => 'Admin'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-2">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <iconify-icon icon="solar:user-plus-bold-duotone" class="text-primary mb-2" style="font-size: 2rem;"></iconify-icon>
                        <h6 class="text-muted mb-1">Applications</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($statistics['total_applications']) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                        <h6 class="text-muted mb-1">Active</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($statistics['active_applications']) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <iconify-icon icon="solar:chart-bold-duotone" class="text-info mb-2" style="font-size: 2rem;"></iconify-icon>
                        <h6 class="text-muted mb-1">Evaluations</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($statistics['total_evaluations']) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <iconify-icon icon="solar:medal-ribbon-bold-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                        <h6 class="text-muted mb-1">Passed</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($statistics['passed_evaluations']) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card h-100 text-center border-0 shadow-sm">
                    <div class="card-body">
                        <iconify-icon icon="solar:dollar-bold-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                        <h6 class="text-muted mb-1">Total Paid</h6>
                        <h5 class="mb-0 fw-bold">${{ number_format($statistics['total_paid'], 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-2">
                <div class="card h-100 text-center border-0 shadow-sm bg-warning-subtle">
                    <div class="card-body">
                        <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                        <h6 class="text-muted mb-1">Pending</h6>
                        <h5 class="mb-0 fw-bold">{{ number_format($statistics['pending_payments']) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Salary Stages</h4>
                    <a href="{{ route('admin.salary.stages.index') }}" class="btn btn-outline-primary btn-sm">
                        <iconify-icon icon="solar:settings-bold"></iconify-icon> Manage Stages
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Stage</th>
                                <th class="text-center">Direct Members</th>
                                <th class="text-center">Self Deposit</th>
                                <th class="text-center">Team Required</th>
                                <th class="text-center">Salary Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stages as $stage)
                            <tr>
                                <td><span class="fw-semibold">{{ $stage->name }}</span></td>
                                <td class="text-center">{{ $stage->direct_members_required }}</td>
                                <td class="text-center">${{ number_format($stage->self_deposit_required, 2) }}</td>
                                <td class="text-center">{{ $stage->team_required }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success fs-6">${{ number_format($stage->salary_amount, 2) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($stage->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Quick Actions</h4>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('admin.salary.applications') }}" class="card border h-100 text-decoration-none">
                            <div class="card-body text-center py-4">
                                <iconify-icon icon="solar:user-plus-bold-duotone" class="text-primary mb-3" style="font-size: 3rem;"></iconify-icon>
                                <h5 class="text-dark">Applications</h5>
                                <p class="text-muted mb-0">View enrolled users</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.salary.evaluations') }}" class="card border h-100 text-decoration-none {{ $statistics['pending_payments'] > 0 ? 'border-warning' : '' }}">
                            <div class="card-body text-center py-4">
                                <iconify-icon icon="solar:chart-bold-duotone" class="text-info mb-3" style="font-size: 3rem;"></iconify-icon>
                                <h5 class="text-dark">Evaluations</h5>
                                <p class="text-muted mb-0">
                                    Review & approve payments
                                    @if($statistics['pending_payments'] > 0)
                                        <br><span class="badge bg-warning text-dark">{{ $statistics['pending_payments'] }} pending</span>
                                    @endif
                                </p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.salary.history') }}" class="card border h-100 text-decoration-none">
                            <div class="card-body text-center py-4">
                                <iconify-icon icon="solar:history-bold-duotone" class="text-success mb-3" style="font-size: 3rem;"></iconify-icon>
                                <h5 class="text-dark">Payment History</h5>
                                <p class="text-muted mb-0">View all past payments</p>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.salary.stages.index') }}" class="card border h-100 text-decoration-none">
                            <div class="card-body text-center py-4">
                                <iconify-icon icon="solar:settings-bold-duotone" class="text-warning mb-3" style="font-size: 3rem;"></iconify-icon>
                                <h5 class="text-dark">Manage Stages</h5>
                                <p class="text-muted mb-0">Edit requirements & amounts</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
