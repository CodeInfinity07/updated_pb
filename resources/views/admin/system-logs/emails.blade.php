@extends('admin.layouts.vertical', ['title' => 'Email Logs', 'subTitle' => 'Email delivery monitoring'])

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
                    <h4 class="mb-0 text-success">{{ number_format($stats['sent']) }}</h4>
                    <small class="text-muted">Sent</small>
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
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="mb-0">Email Delivery Log</h5>
                </div>
                <div class="col-md-8">
                    <form method="GET" class="row g-2">
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="sent" {{ ($filters['status'] ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
                                <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ ($filters['type'] ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From" value="{{ $filters['date_from'] ?? '' }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To" value="{{ $filters['date_to'] ?? '' }}" onchange="this.form.submit()">
                        </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" class="form-control" placeholder="Search email or subject..." value="{{ $filters['search'] ?? '' }}">
                                <button type="submit" class="btn btn-primary"><i class="ri-search-line"></i></button>
                            </div>
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
                            <th>Recipient</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>
                                <span>{{ $log->recipient_email }}</span>
                                @if($log->user)
                                <br><small class="text-muted">
                                    <a href="{{ route('admin.users.show', $log->user->id) }}" class="text-decoration-none">
                                        {{ $log->user->first_name }} {{ $log->user->last_name }}
                                    </a>
                                </small>
                                @endif
                            </td>
                            <td>
                                <span class="text-truncate d-inline-block" style="max-width: 250px;" title="{{ $log->subject }}">
                                    {{ $log->subject }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">{{ $log->getTypeLabel() }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->getStatusColor() }}">{{ ucfirst($log->status) }}</span>
                            </td>
                            <td>
                                {{ $log->created_at->format('M d, Y') }}
                                <br><small class="text-muted">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td>
                                @if($log->error_message)
                                <span class="text-danger text-truncate d-inline-block" style="max-width: 200px;" title="{{ $log->error_message }}">
                                    {{ Str::limit($log->error_message, 50) }}
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <iconify-icon icon="iconamoon:send-duotone" class="fs-1 mb-2 d-block mx-auto opacity-50"></iconify-icon>
                                No email logs found
                                <br><small>Email logs will appear here once emails are sent through the system</small>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer bg-transparent border-top">
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-muted small">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} entries
                </div>
                <div>
                    {{ $logs->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
