@extends('admin.layouts.vertical', ['title' => 'System Logs', 'subTitle' => 'Transaction & Email Monitoring'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <h5 class="mb-0">System Logs Dashboard</h5>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="?date_range=today" class="btn btn-sm {{ $dateRange == 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">Today</a>
                            <a href="?date_range=yesterday" class="btn btn-sm {{ $dateRange == 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' }}">Yesterday</a>
                            <a href="?date_range=this_week" class="btn btn-sm {{ $dateRange == 'this_week' ? 'btn-primary' : 'btn-outline-secondary' }}">This Week</a>
                            <a href="?date_range=this_month" class="btn btn-sm {{ $dateRange == 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                            <a href="?date_range=all" class="btn btn-sm {{ $dateRange == 'all' ? 'btn-secondary' : 'btn-outline-secondary' }}">All Time</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.system-logs.deposits') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-md bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:arrow-down-circle-duotone" class="fs-1 text-success"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Deposits</h6>
                                <h3 class="mb-0">{{ number_format($depositStats['total']) }}</h3>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-success"><i class="ri-check-line"></i> {{ $depositStats['completed'] }} Completed</span>
                            <span class="text-danger"><i class="ri-close-line"></i> {{ $depositStats['failed'] }} Failed</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $depositStats['success_rate'] }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ 100 - $depositStats['success_rate'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $depositStats['success_rate'] }}% success rate</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.system-logs.withdrawals') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-md bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:arrow-up-circle-duotone" class="fs-1 text-warning"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Withdrawals</h6>
                                <h3 class="mb-0">{{ number_format($withdrawalStats['total']) }}</h3>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-success"><i class="ri-check-line"></i> {{ $withdrawalStats['completed'] }} Completed</span>
                            <span class="text-danger"><i class="ri-close-line"></i> {{ $withdrawalStats['failed'] }} Failed</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $withdrawalStats['success_rate'] }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ 100 - $withdrawalStats['success_rate'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $withdrawalStats['success_rate'] }}% success rate</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.system-logs.investments') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-md bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:trend-up-duotone" class="fs-1 text-primary"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Investments</h6>
                                <h3 class="mb-0">{{ number_format($investmentStats['total']) }}</h3>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-success"><i class="ri-check-line"></i> {{ $investmentStats['completed'] }} Completed</span>
                            <span class="text-danger"><i class="ri-close-line"></i> {{ $investmentStats['failed'] }} Failed</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $investmentStats['success_rate'] }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ 100 - $investmentStats['success_rate'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $investmentStats['success_rate'] }}% success rate</small>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-6 col-xl-3">
            <a href="{{ route('admin.system-logs.emails') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 hover-lift">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-md bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:send-duotone" class="fs-1 text-info"></iconify-icon>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Emails</h6>
                                <h3 class="mb-0">{{ number_format($emailStats['total']) }}</h3>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span class="text-success"><i class="ri-check-line"></i> {{ $emailStats['sent'] }} Sent</span>
                            <span class="text-danger"><i class="ri-close-line"></i> {{ $emailStats['failed'] }} Failed</span>
                        </div>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ $emailStats['success_rate'] }}%"></div>
                            <div class="progress-bar bg-danger" style="width: {{ 100 - $emailStats['success_rate'] }}%"></div>
                        </div>
                        <small class="text-muted">{{ $emailStats['success_rate'] }}% success rate</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Transaction Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th class="text-center">Pending</th>
                                <th class="text-center">Completed</th>
                                <th class="text-center">Failed</th>
                                <th class="text-center">Cancelled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success-subtle text-success">Deposits</span></td>
                                <td class="text-center"><span class="badge bg-warning">{{ $depositStats['pending'] }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $depositStats['completed'] }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $depositStats['failed'] }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $depositStats['cancelled'] }}</span></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning-subtle text-warning">Withdrawals</span></td>
                                <td class="text-center"><span class="badge bg-warning">{{ $withdrawalStats['pending'] }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $withdrawalStats['completed'] }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $withdrawalStats['failed'] }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $withdrawalStats['cancelled'] }}</span></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary-subtle text-primary">Investments</span></td>
                                <td class="text-center"><span class="badge bg-warning">{{ $investmentStats['pending'] }}</span></td>
                                <td class="text-center"><span class="badge bg-success">{{ $investmentStats['completed'] }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $investmentStats['failed'] }}</span></td>
                                <td class="text-center"><span class="badge bg-secondary">{{ $investmentStats['cancelled'] }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5 class="mb-0">Email System Health</h5>
                </div>
                <div class="card-body">
                    @if($emailStats['total'] > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Delivery Rate</span>
                                <span class="fw-semibold {{ $emailStats['success_rate'] >= 90 ? 'text-success' : ($emailStats['success_rate'] >= 70 ? 'text-warning' : 'text-danger') }}">
                                    {{ $emailStats['success_rate'] }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar {{ $emailStats['success_rate'] >= 90 ? 'bg-success' : ($emailStats['success_rate'] >= 70 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $emailStats['success_rate'] }}%"></div>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-success mb-0">{{ number_format($emailStats['sent']) }}</h4>
                                    <small class="text-muted">Delivered</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="border rounded p-2">
                                    <h4 class="text-info mb-0">{{ number_format($emailStats['total']) }}</h4>
                                    <small class="text-muted">Total Logged</small>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-2">Logs all emails sent via the mail system</small>
                    @else
                        <div class="text-center text-muted py-4">
                            <iconify-icon icon="iconamoon:send-duotone" class="fs-1 mb-2"></iconify-icon>
                            <p class="mb-0">No email logs recorded yet</p>
                            <small>Email logs will appear here once emails are sent through the system</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .hover-lift:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
</style>
@endsection
