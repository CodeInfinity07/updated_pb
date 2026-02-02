@extends('admin.layouts.vertical', ['title' => 'Staff Chat History', 'subTitle' => 'Support'])

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                            <span class="text-primary fw-bold fs-4">{{ strtoupper(substr($staff->first_name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <h4 class="mb-1">{{ $staff->first_name }} {{ $staff->last_name }}</h4>
                            <p class="mb-0 text-muted">{{ $staff->email }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.chat.staff-chats') }}" class="btn btn-outline-secondary">
                        <iconify-icon icon="solar:arrow-left-linear"></iconify-icon> Back to Staff List
                    </a>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-3 mb-4">
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $staffChatStats['open_chats'] }}/{{ $staffChatStats['max_chats'] }}</h4>
                        <p class="mb-0">Active Chats</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ $staffChatStats['closed_today'] }}</h4>
                        <p class="mb-0">Closed Today</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <h4 class="text-white mb-1">{{ number_format($staffChatStats['total_handled']) }}</h4>
                        <p class="mb-0">Total Handled</p>
                    </div>
                </div>
            </div>
            <div class="flex-fill" style="min-width: 150px;">
                <div class="card bg-warning text-dark h-100">
                    <div class="card-body">
                        <h4 class="text-dark mb-1">
                            @if($staffChatStats['average_response_time'])
                                {{ gmdate('H:i:s', $staffChatStats['average_response_time']) }}
                            @else
                                N/A
                            @endif
                        </h4>
                        <p class="mb-0">Avg Handle Time</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Chat History</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th>User</th>
                                <th>Subject</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Assigned At</th>
                                <th class="text-center">Closed At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($conversations as $conversation)
                                <tr>
                                    <td>
                                        @if($conversation->user)
                                            <div class="fw-medium">{{ $conversation->user->first_name }} {{ $conversation->user->last_name }}</div>
                                            <small class="text-muted">{{ $conversation->user->email }}</small>
                                        @else
                                            <span class="text-muted">Deleted User</span>
                                        @endif
                                    </td>
                                    <td>{{ $conversation->subject ?? 'No Subject' }}</td>
                                    <td class="text-center">
                                        @php
                                            $statusColors = ['open' => 'primary', 'pending' => 'warning', 'closed' => 'success'];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$conversation->status] ?? 'secondary' }}">
                                            {{ ucfirst($conversation->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ $conversation->assigned_at ? $conversation->assigned_at->format('M d, Y H:i') : 'N/A' }}
                                    </td>
                                    <td class="text-center">
                                        {{ $conversation->closed_at ? $conversation->closed_at->format('M d, Y H:i') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.chat.show', $conversation->id) }}" class="btn btn-sm btn-outline-primary">
                                            <iconify-icon icon="solar:eye-linear"></iconify-icon> View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No chat history found</td>
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
