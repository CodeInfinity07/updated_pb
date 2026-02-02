@extends('layouts.vertical', ['title' => 'My Investments', 'subTitle' => 'Finance'])

@section('content')

<div class="row">
    <div class="col-lg-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-2">Total Invested</h5>
                        <h4 class="mb-0">${{ number_format($investmentData['total_investments'], 2) }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle text-primary rounded fs-3">
                            <iconify-icon icon="iconamoon:trend-up-duotone"></iconify-icon>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-2">Active Packages</h5>
                        <h4 class="mb-0">{{ $investmentData['active_investments'] }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle text-success rounded fs-3">
                            <iconify-icon icon="iconamoon:box-duotone"></iconify-icon>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="text-muted mb-2">Total Returns</h5>
                        <h4 class="mb-0 text-success">${{ number_format($investmentData['investment_returns'], 2) }}</h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-warning-subtle text-warning rounded fs-3">
                            <iconify-icon icon="iconamoon:lightning-duotone"></iconify-icon>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="d-flex card-header justify-content-between align-items-center">
                <h4 class="card-title">Investment History</h4>
                <a href="{{ route('bot.index') }}" class="btn btn-primary btn-sm">
                    <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-1"></iconify-icon> New Investment
                </a>
            </div>

            @if($investmentData['recent_investments'] && count($investmentData['recent_investments']) > 0)
            <div class="card-body p-0">
                <div class="d-none d-lg-block">
                    <div class="table-responsive table-card">
                        <table class="table table-borderless table-hover table-nowrap align-middle mb-0">
                            <thead class="bg-light bg-opacity-50 thead-sm">
                                <tr>
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Date</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($investmentData['recent_investments'] as $investment)
                                <tr>
                                    <td>
                                        <code class="small">{{ Str::limit($investment->transaction_id, 15) }}...</code>
                                    </td>
                                    <td>
                                        <strong class="text-primary">${{ number_format($investment->amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        {{ $investment->created_at->format('d M, Y') }}
                                        <small class="text-muted d-block">{{ $investment->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $investment->status === 'completed' ? 'success' : ($investment->status === 'pending' ? 'warning' : 'danger') }}-subtle text-{{ $investment->status === 'completed' ? 'success' : ($investment->status === 'pending' ? 'warning' : 'danger') }} p-1">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-lg-none p-3">
                    <div class="row g-3">
                        @foreach($investmentData['recent_investments'] as $investment)
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-primary-subtle text-primary">Investment</span>
                                        <span class="badge bg-{{ $investment->status === 'completed' ? 'success' : ($investment->status === 'pending' ? 'warning' : 'danger') }}-subtle text-{{ $investment->status === 'completed' ? 'success' : ($investment->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($investment->status) }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div>
                                            <h6 class="mb-0 text-primary">${{ number_format($investment->amount, 2) }}</h6>
                                            <small class="text-muted">{{ $investment->created_at->format('M d, Y - H:i') }}</small>
                                        </div>
                                        <iconify-icon icon="iconamoon:trend-up-duotone" class="text-primary fs-20"></iconify-icon>
                                    </div>
                                    <code class="small">{{ Str::limit($investment->transaction_id, 25) }}...</code>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @else
            <div class="card-body text-center py-5">
                <iconify-icon icon="iconamoon:folder-open-duotone" class="fs-48 text-muted mb-3"></iconify-icon>
                <h5 class="text-muted">No investments yet</h5>
                <p class="text-muted mb-3">Start investing to grow your portfolio</p>
                <a href="{{ route('bot.index') }}" class="btn btn-primary">
                    <iconify-icon icon="iconamoon:sign-plus-duotone" class="me-1"></iconify-icon> Make Your First Investment
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection
