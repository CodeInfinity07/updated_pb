@extends('admin.layouts.vertical', ['title' => 'Staff Chat Dashboard', 'subTitle' => 'Support'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $totalStats['total_staff'] }}</h4>
                        <p class="mb-0">Support Staff</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h4 class="text-dark mb-1">{{ $totalStats['total_open_chats'] }}</h4>
                        <p class="mb-0">Open Chats</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $totalStats['total_handled_today'] }}</h4>
                        <p class="mb-0">Closed Today</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $totalStats['unassigned_chats'] }}</h4>
                        <p class="mb-0">Unassigned</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Staff Chat Performance</h4>
                    <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary btn-sm">
                        <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back to Chats
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>Staff Member</th>
                                <th class="text-center">Open Chats</th>
                                <th class="text-center">Closed Today</th>
                                <th class="text-center">Total Handled</th>
                                <th class="text-center">Avg Handle Time</th>
                                <th class="text-center">Last Active</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($staffStats as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <span class="text-primary fw-semibold">{{ strtoupper(substr($item['admin']->first_name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $item['admin']->first_name }} {{ $item['admin']->last_name }}</div>
                                                <small class="text-muted">{{ $item['admin']->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $item['stats']['open_chats'] >= 3 ? 'bg-danger' : 'bg-primary' }}">
                                            {{ $item['stats']['open_chats'] }}/3
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $item['stats']['closed_today'] }}</td>
                                    <td class="text-center">{{ number_format($item['stats']['total_handled']) }}</td>
                                    <td class="text-center">
                                        @if($item['stats']['average_response_time'])
                                            {{ gmdate('H:i:s', $item['stats']['average_response_time']) }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item['stats']['last_active_at'])
                                            {{ \Carbon\Carbon::parse($item['stats']['last_active_at'])->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.chat.staff-history', $item['admin']->id) }}" class="btn btn-sm btn-outline-primary">
                                            <iconify-icon icon="solar:eye-linear"></iconify-icon> View History
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No staff members found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
