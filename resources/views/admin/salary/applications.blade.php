@extends('admin.layouts.vertical', ['title' => 'Salary Applications', 'subTitle' => 'Admin'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['total'] }}</h4>
                        <p class="mb-0">Total Applications</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['active'] }}</h4>
                        <p class="mb-0">Active</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['failed'] }}</h4>
                        <p class="mb-0">Failed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $stats['graduated'] }}</h4>
                        <p class="mb-0">Graduated</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Salary Applications</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.salary.index') }}" class="btn btn-outline-secondary btn-sm">
                            <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search user..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="graduated" {{ request('status') == 'graduated' ? 'selected' : '' }}>Graduated</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-centered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Stage</th>
                                <th>Applied</th>
                                <th>Stage Baseline</th>
                                <th>Current Target</th>
                                <th>Months</th>
                                <th>Status</th>
                                <th>Period End</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $app)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $app->user) }}">
                                        {{ $app->user->first_name }} {{ $app->user->last_name }}
                                    </a>
                                    <br><small class="text-muted">{{ $app->user->username }}</small>
                                </td>
                                <td>{{ $app->salaryStage->name }}</td>
                                <td>{{ $app->applied_at->format('M d, Y') }}</td>
                                <td>{{ $app->baseline_team_count }}</td>
                                <td>{{ $app->current_target_team }}</td>
                                <td>{{ $app->months_completed }}</td>
                                <td>
                                    @if($app->status == 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif($app->status == 'failed')
                                        <span class="badge bg-danger">Failed</span>
                                    @else
                                        <span class="badge bg-info">Graduated</span>
                                    @endif
                                </td>
                                <td>{{ $app->current_period_end->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No applications found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
