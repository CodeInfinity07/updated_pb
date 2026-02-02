@extends('admin.layouts.vertical', ['title' => 'Dummy User Stats', 'subTitle' => 'Financial data for excluded users'])

@section('content')
<div class="container-fluid">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">Date Range Filter</h5>
                        <a href="{{ route('admin.dummy-users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="mdi:arrow-left" class="me-1"></iconify-icon>Back to Dummy Users
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.dummy-users.stats') }}" method="GET" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" 
                                value="{{ $startDate ? $startDate->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" 
                                value="{{ $endDate ? $endDate->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <iconify-icon icon="mdi:filter" class="me-1"></iconify-icon>Filter
                                </button>
                                <a href="{{ route('admin.dummy-users.stats') }}" class="btn btn-outline-secondary">
                                    <iconify-icon icon="mdi:refresh" class="me-1"></iconify-icon>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-primary">{{ $stats['total_users'] }}</h3>
                    <small class="text-muted">Excluded Users</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-success">${{ number_format($stats['total_deposits'], 2) }}</h3>
                    <small class="text-muted">Total Deposits ({{ $stats['deposit_count'] }})</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-1 text-danger">${{ number_format($stats['total_withdrawals'], 2) }}</h3>
                    <small class="text-muted">Total Withdrawals ({{ $stats['withdrawal_count'] }})</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-1 {{ $stats['net_position'] >= 0 ? 'text-success' : 'text-danger' }}">
                        ${{ number_format(abs($stats['net_position']), 2) }}
                    </h3>
                    <small class="text-muted">Net {{ $stats['net_position'] >= 0 ? 'Positive' : 'Negative' }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <iconify-icon icon="mdi:arrow-down-bold-circle" class="text-success me-2"></iconify-icon>
                        Recent Deposits
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentDeposits->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <iconify-icon icon="mdi:cash-off" class="fs-1 mb-2 d-block"></iconify-icon>
                            No deposits found
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentDeposits as $deposit)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ $deposit->user->first_name ?? 'N/A' }} {{ $deposit->user->last_name ?? '' }}</span>
                                        </td>
                                        <td class="text-success">${{ number_format($deposit->amount, 2) }}</td>
                                        <td class="text-muted">{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <iconify-icon icon="mdi:arrow-up-bold-circle" class="text-danger me-2"></iconify-icon>
                        Recent Withdrawals
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentWithdrawals->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <iconify-icon icon="mdi:cash-off" class="fs-1 mb-2 d-block"></iconify-icon>
                            No withdrawals found
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentWithdrawals as $withdrawal)
                                    <tr>
                                        <td>
                                            <span class="fw-medium">{{ $withdrawal->user->first_name ?? 'N/A' }} {{ $withdrawal->user->last_name ?? '' }}</span>
                                        </td>
                                        <td class="text-danger">${{ number_format($withdrawal->amount, 2) }}</td>
                                        <td class="text-muted">{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
