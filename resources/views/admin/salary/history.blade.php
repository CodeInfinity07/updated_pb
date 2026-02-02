@extends('admin.layouts.vertical', ['title' => 'Payment History', 'subTitle' => 'Monthly Salary Program'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">${{ number_format($statistics['total_paid'], 2) }}</h4>
                        <p class="mb-0">Total Paid</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $statistics['total_payments'] }}</h4>
                        <p class="mb-0">Total Payments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $payouts->total() }}</h4>
                        <p class="mb-0">Records Found</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Payment History</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.salary.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                            <iconify-icon icon="solar:download-bold"></iconify-icon> Export CSV
                        </a>
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
                        <select name="stage_id" class="form-select">
                            <option value="">All Stages</option>
                            @foreach($stages as $stage)
                                <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>
                                    {{ $stage->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" placeholder="From" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" placeholder="To" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.salary.history') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-centered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Stage</th>
                                <th>Month</th>
                                <th class="text-end">Amount</th>
                                <th>Paid At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $payout->user) }}">
                                        {{ $payout->user->first_name }} {{ $payout->user->last_name }}
                                    </a>
                                    <br><small class="text-muted">{{ $payout->user->email }}</small>
                                </td>
                                <td><span class="badge bg-info">{{ $payout->salaryStage->name }}</span></td>
                                <td>Month {{ $payout->month_number }}</td>
                                <td class="text-end fw-bold text-success">${{ number_format($payout->salary_amount, 2) }}</td>
                                <td>{{ $payout->paid_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No payments found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $payouts->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
