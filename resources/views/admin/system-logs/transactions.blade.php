@extends('admin.layouts.vertical', ['title' => $title . ' Logs', 'subTitle' => 'Transaction details and filtering'])

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('admin.system-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-success">{{ number_format($stats['completed']) }}</h4>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-danger">{{ number_format($stats['failed']) }}</h4>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body text-center py-3">
                    <h4 class="mb-0 text-warning">{{ number_format($stats['pending']) }}</h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <h5 class="mb-0">{{ $title }} Log</h5>
                </div>
                <div class="col-md-8">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <a href="?period=today&status={{ $filters['status'] ?? '' }}" 
                           class="btn btn-sm {{ ($filters['period'] ?? '') == 'today' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Today
                        </a>
                        <a href="?period=yesterday&status={{ $filters['status'] ?? '' }}" 
                           class="btn btn-sm {{ ($filters['period'] ?? '') == 'yesterday' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            Yesterday
                        </a>
                        <a href="?period=this_week&status={{ $filters['status'] ?? '' }}" 
                           class="btn btn-sm {{ ($filters['period'] ?? '') == 'this_week' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            This Week
                        </a>
                        <a href="?period=this_month&status={{ $filters['status'] ?? '' }}" 
                           class="btn btn-sm {{ ($filters['period'] ?? '') == 'this_month' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            This Month
                        </a>
                        <a href="?status={{ $filters['status'] ?? '' }}" 
                           class="btn btn-sm {{ empty($filters['period']) && empty($filters['date_from']) ? 'btn-secondary' : 'btn-outline-secondary' }}">
                            All Time
                        </a>
                    </div>
                    <form method="GET" class="row g-2">
                        <input type="hidden" name="period" value="custom">
                        <div class="col-md-4">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="completed" {{ ($filters['status'] ?? '') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="cancelled" {{ ($filters['status'] ?? '') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From" value="{{ $filters['date_from'] ?? '' }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-4">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To" value="{{ $filters['date_to'] ?? '' }}" onchange="this.form.submit()">
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $tx)
                        <tr>
                            <td>
                                <code class="small">{{ $tx->transaction_id }}</code>
                            </td>
                            <td>
                                @if($tx->user)
                                    <a href="{{ route('admin.users.show', $tx->user->id) }}" class="text-decoration-none">
                                        {{ $tx->user->first_name }} {{ $tx->user->last_name }}
                                        <br><small class="text-muted">{{ $tx->user->email }}</small>
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold">${{ number_format($tx->amount, 2) }}</span>
                                <br><small class="text-muted">{{ strtoupper($tx->currency ?? 'USD') }}</small>
                            </td>
                            <td>
                                @php
                                    $statusColor = match($tx->status) {
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'pending' => 'warning',
                                        'cancelled' => 'secondary',
                                        'processing' => 'info',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ ucfirst($tx->status) }}</span>
                            </td>
                            <td>
                                {{ $tx->created_at->format('M d, Y') }}
                                <br><small class="text-muted">{{ $tx->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $tx->description }}">
                                    {{ $tx->description ?? '-' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No {{ strtolower($title) }} found matching your criteria
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($transactions->hasPages())
        <div class="card-footer bg-transparent border-top">
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-muted small">
                    Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                </div>
                <div>
                    {{ $transactions->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
