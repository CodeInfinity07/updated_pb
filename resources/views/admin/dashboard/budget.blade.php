@extends('admin.layouts.vertical', ['title' => 'Budget Overview', 'subTitle' => 'Financial Budget'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1">Budget Overview</h4>
                    <p class="text-muted mb-0">Track income from bot activations and distributed prizes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-success">
                <div class="card-body text-center py-3">
                    <iconify-icon icon="iconamoon:arrow-down-2-duotone" class="fs-1 text-success mb-1"></iconify-icon>
                    <h3 class="mb-1 text-success">${{ number_format($totals['income'], 2) }}</h3>
                    <h6 class="text-muted mb-2">Total Income</h6>
                    <div class="row g-0">
                        <div class="col-3 border-end">
                            <div class="fw-semibold text-success" style="font-size: 0.75rem;">${{ number_format($today['income'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Today</small>
                        </div>
                        <div class="col-3 border-end">
                            <div class="fw-semibold text-success" style="font-size: 0.75rem;">${{ number_format($yesterday['income'] ?? 0, 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Yesterday</small>
                        </div>
                        <div class="col-3 border-end">
                            <div class="fw-semibold text-success" style="font-size: 0.75rem;">${{ number_format($weekly['income'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Week</small>
                        </div>
                        <div class="col-3">
                            <div class="fw-semibold text-success" style="font-size: 0.75rem;">${{ number_format($monthly['income'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-danger">
                <div class="card-body text-center py-3">
                    <iconify-icon icon="iconamoon:arrow-up-2-duotone" class="fs-1 text-danger mb-1"></iconify-icon>
                    <h3 class="mb-1 text-danger">${{ number_format($totals['expenses'], 2) }}</h3>
                    <h6 class="text-muted mb-2">Total Expenses</h6>
                    <div class="row g-0">
                        <div class="col-3 border-end">
                            <div class="fw-semibold text-danger" style="font-size: 0.75rem;">${{ number_format($today['expenses'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Today</small>
                        </div>
                        <div class="col-3 border-end">
                            <div class="fw-semibold text-danger" style="font-size: 0.75rem;">${{ number_format($yesterday['expenses'] ?? 0, 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Yesterday</small>
                        </div>
                        <div class="col-3 border-end">
                            <div class="fw-semibold text-danger" style="font-size: 0.75rem;">${{ number_format($weekly['expenses'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Week</small>
                        </div>
                        <div class="col-3">
                            <div class="fw-semibold text-danger" style="font-size: 0.75rem;">${{ number_format($monthly['expenses'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-primary">
                <div class="card-body text-center py-3">
                    <iconify-icon icon="iconamoon:calculator-duotone" class="fs-1 text-primary mb-1"></iconify-icon>
                    <h3 class="mb-1 {{ $totals['net'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($totals['net'], 2) }}</h3>
                    <h6 class="text-muted mb-2">Net Budget</h6>
                    <div class="row g-0">
                        <div class="col-3 border-end">
                            <div class="fw-semibold {{ $today['net'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem;">${{ number_format($today['net'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Today</small>
                        </div>
                        <div class="col-3 border-end">
                            <div class="fw-semibold {{ ($yesterday['net'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem;">${{ number_format($yesterday['net'] ?? 0, 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Yesterday</small>
                        </div>
                        <div class="col-3 border-end">
                            <div class="fw-semibold {{ $weekly['net'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem;">${{ number_format($weekly['net'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Week</small>
                        </div>
                        <div class="col-3">
                            <div class="fw-semibold {{ $monthly['net'] >= 0 ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem;">${{ number_format($monthly['net'], 0) }}</div>
                            <small class="text-muted" style="font-size: 0.65rem;">Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="iconamoon:trend-up-duotone" class="me-2"></iconify-icon>
                        Income Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Source</th>
                                    <th class="text-end">Today</th>
                                    <th class="text-end">Yesterday</th>
                                    <th class="text-end">This Week</th>
                                    <th class="text-end">This Month</th>
                                    <th class="text-end">All Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <iconify-icon icon="iconamoon:lightning-duotone" class="text-warning me-2"></iconify-icon>
                                        Bot Activation Fees
                                    </td>
                                    <td class="text-end text-success">${{ number_format($botFeeStats->today_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($botFeeStats->yesterday_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($botFeeStats->weekly_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($botFeeStats->monthly_amount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($botFeeStats->total_amount ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>Total Income</td>
                                    <td class="text-end text-success">${{ number_format($today['income'], 2) }}</td>
                                    <td class="text-end">${{ number_format($botFeeStats->yesterday_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($weekly['income'], 2) }}</td>
                                    <td class="text-end">${{ number_format($monthly['income'], 2) }}</td>
                                    <td class="text-end">${{ number_format($totals['income'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="iconamoon:gift-duotone" class="me-2"></iconify-icon>
                        Expenses Breakdown
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Expense Type</th>
                                    <th class="text-end">Today</th>
                                    <th class="text-end">Yesterday</th>
                                    <th class="text-end">This Week</th>
                                    <th class="text-end">This Month</th>
                                    <th class="text-end">All Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <iconify-icon icon="iconamoon:briefcase-duotone" class="text-primary me-2"></iconify-icon>
                                        Monthly Salary
                                    </td>
                                    <td class="text-end text-danger">${{ number_format($salaryStats->today_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($salaryStats->yesterday_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($salaryStats->weekly_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($salaryStats->monthly_amount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($salaryStats->total_amount ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <iconify-icon icon="akar-icons:trophy" class="text-warning me-2"></iconify-icon>
                                        Rank Rewards
                                    </td>
                                    <td class="text-end text-danger">${{ number_format($rankRewardStats->today_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($rankRewardStats->yesterday_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($rankRewardStats->weekly_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($rankRewardStats->monthly_amount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($rankRewardStats->total_amount ?? 0, 2) }}</td>
                                </tr>
                                <tr>
                                    <td>
                                        <iconify-icon icon="iconamoon:star-duotone" class="text-info me-2"></iconify-icon>
                                        Leaderboard Prizes
                                    </td>
                                    <td class="text-end text-danger">${{ number_format($leaderboardStats->today_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($leaderboardStats->yesterday_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($leaderboardStats->weekly_amount ?? 0, 2) }}</td>
                                    <td class="text-end">${{ number_format($leaderboardStats->monthly_amount ?? 0, 2) }}</td>
                                    <td class="text-end fw-bold">${{ number_format($leaderboardStats->total_amount ?? 0, 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr class="fw-bold">
                                    <td>Total Expenses</td>
                                    <td class="text-end text-danger">${{ number_format($today['expenses'], 2) }}</td>
                                    <td class="text-end">${{ number_format(($salaryStats->yesterday_amount ?? 0) + ($rankRewardStats->yesterday_amount ?? 0) + ($leaderboardStats->yesterday_amount ?? 0), 2) }}</td>
                                    <td class="text-end">${{ number_format($weekly['expenses'], 2) }}</td>
                                    <td class="text-end">${{ number_format($monthly['expenses'], 2) }}</td>
                                    <td class="text-end">${{ number_format($totals['expenses'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="iconamoon:calculator-duotone" class="me-2"></iconify-icon>
                        Net Budget Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th class="text-end">Income</th>
                                    <th class="text-end">Expenses</th>
                                    <th class="text-end">Net Budget</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Today</strong></td>
                                    <td class="text-end text-success">${{ number_format($today['income'], 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($today['expenses'], 2) }}</td>
                                    <td class="text-end fw-bold {{ $today['net'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($today['net'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>This Week</strong></td>
                                    <td class="text-end text-success">${{ number_format($weekly['income'], 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($weekly['expenses'], 2) }}</td>
                                    <td class="text-end fw-bold {{ $weekly['net'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($weekly['net'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>This Month</strong></td>
                                    <td class="text-end text-success">${{ number_format($monthly['income'], 2) }}</td>
                                    <td class="text-end text-danger">${{ number_format($monthly['expenses'], 2) }}</td>
                                    <td class="text-end fw-bold {{ $monthly['net'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($monthly['net'], 2) }}</td>
                                </tr>
                            </tbody>
                            <tfoot class="table-primary">
                                <tr class="fw-bold">
                                    <td>All Time</td>
                                    <td class="text-end">${{ number_format($totals['income'], 2) }}</td>
                                    <td class="text-end">${{ number_format($totals['expenses'], 2) }}</td>
                                    <td class="text-end {{ $totals['net'] >= 0 ? 'text-success' : 'text-danger' }}">${{ number_format($totals['net'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
