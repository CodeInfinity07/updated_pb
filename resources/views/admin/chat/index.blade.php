@extends('admin.layouts.vertical', ['title' => 'Live Chat Support', 'subTitle' => 'Manage customer chat conversations'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-lg bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                <iconify-icon icon="iconamoon:comment-dots-duotone" class="fs-2 text-primary"></iconify-icon>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">Live Chat Support</h4>
                                <p class="text-muted mb-0 small">Respond to customer messages in real-time</p>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="d-flex align-items-center gap-2 px-3 py-2 rounded-pill {{ $staffStats['can_accept_new'] ? 'bg-success-subtle' : 'bg-danger-subtle' }}">
                                <span class="status-dot {{ $staffStats['can_accept_new'] ? 'bg-success' : 'bg-danger' }}"></span>
                                <span class="fw-semibold {{ $staffStats['can_accept_new'] ? 'text-success' : 'text-danger' }}">
                                    {{ $staffStats['open_chats'] }}/{{ $staffStats['max_chats'] }} Slots Used
                                </span>
                            </div>
                            @if($isSuperAdmin)
                            <a href="{{ route('admin.chat.staff-chats') }}" class="btn btn-soft-info">
                                <iconify-icon icon="iconamoon:profile-group-duotone" class="me-1"></iconify-icon>
                                Staff Overview
                            </a>
                            @endif
                            <button type="button" class="btn btn-soft-secondary" onclick="location.reload()">
                                <iconify-icon icon="iconamoon:restart-duotone"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary-subtle rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="iconamoon:comment-duotone" class="text-primary fs-4"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">{{ $isSuperAdmin ? 'Total Chats' : 'My Total' }}</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-success-subtle rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="iconamoon:sign-in-duotone" class="text-success fs-4"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">{{ $isSuperAdmin ? 'Open' : 'My Open' }}</p>
                            <h4 class="mb-0 fw-bold text-success">{{ $stats['open'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning-subtle rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="iconamoon:clock-duotone" class="text-warning fs-4"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">{{ $isSuperAdmin ? 'Pending' : 'My Pending' }}</p>
                            <h4 class="mb-0 fw-bold text-warning">{{ $stats['pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-danger-subtle rounded-circle me-3 d-flex align-items-center justify-content-center">
                            <iconify-icon icon="iconamoon:attention-circle-duotone" class="text-danger fs-4"></iconify-icon>
                        </div>
                        <div>
                            <p class="text-muted mb-1 small">{{ $isSuperAdmin ? 'Unassigned' : 'Available' }}</p>
                            <h4 class="mb-0 fw-bold text-danger">{{ $stats['unassigned'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active d-flex align-items-center gap-2" data-bs-toggle="tab" href="#myChats" role="tab">
                                <iconify-icon icon="iconamoon:profile-duotone"></iconify-icon>
                                My Chats
                                <span class="badge bg-primary rounded-pill">{{ $chatData['my_chats']->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-center gap-2" data-bs-toggle="tab" href="#availableChats" role="tab">
                                <iconify-icon icon="iconamoon:comment-add-duotone"></iconify-icon>
                                Available
                                @if($chatData['available_chats']->count() > 0)
                                <span class="badge bg-warning text-dark rounded-pill">{{ $chatData['available_chats']->count() }}</span>
                                @else
                                <span class="badge bg-secondary rounded-pill">0</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="myChats" role="tabpanel">
                            @if($chatData['my_chats']->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($chatData['my_chats'] as $conv)
                                <a href="{{ route('admin.chat.show', $conv) }}" class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="avatar-md bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                <span class="fw-bold text-primary">
                                                    {{ $conv->user ? strtoupper(substr($conv->user->first_name, 0, 1) . substr($conv->user->last_name, 0, 1)) : '??' }}
                                                </span>
                                            </div>
                                            @php $unread = $conv->unreadMessagesForAdmin(); @endphp
                                            @if($unread > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{ $unread }}
                                            </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="mb-0 fw-semibold text-truncate">
                                                    {{ $conv->user ? $conv->user->first_name . ' ' . $conv->user->last_name : 'Unknown User' }}
                                                </h6>
                                                <small class="text-muted flex-shrink-0 ms-2">
                                                    {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '' }}
                                                </small>
                                            </div>
                                            <p class="mb-1 text-muted small text-truncate">
                                                @if($conv->latestMessage)
                                                    {{ Str::limit($conv->latestMessage->message, 60) }}
                                                @else
                                                    <em>No messages yet</em>
                                                @endif
                                            </p>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($conv->status == 'open')
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">
                                                        <iconify-icon icon="iconamoon:sign-in-duotone" class="me-1"></iconify-icon>Open
                                                    </span>
                                                @elseif($conv->status == 'pending')
                                                    <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1">
                                                        <iconify-icon icon="iconamoon:clock-duotone" class="me-1"></iconify-icon>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-1">
                                                        <iconify-icon icon="iconamoon:sign-minus-circle-duotone" class="me-1"></iconify-icon>Closed
                                                    </span>
                                                @endif
                                                <small class="text-muted">{{ $conv->user ? $conv->user->email : '' }}</small>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <iconify-icon icon="iconamoon:arrow-right-2-duotone" class="text-muted fs-4"></iconify-icon>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-5">
                                <div class="avatar-lg bg-light rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="iconamoon:comment-dots-duotone" class="fs-1 text-muted"></iconify-icon>
                                </div>
                                <h6 class="text-muted">No Active Chats</h6>
                                <p class="text-muted small mb-0">Chats assigned to you will appear here</p>
                            </div>
                            @endif
                        </div>

                        <div class="tab-pane fade" id="availableChats" role="tabpanel">
                            @if(!$staffStats['can_accept_new'] && !$isSuperAdmin)
                            <div class="alert alert-warning m-3 d-flex align-items-center gap-2">
                                <iconify-icon icon="iconamoon:lock-duotone" class="fs-4"></iconify-icon>
                                <div>
                                    <strong>Chat Limit Reached</strong>
                                    <p class="mb-0 small">You have 3 active chats. Close some conversations to accept new ones.</p>
                                </div>
                            </div>
                            @endif

                            @if(($staffStats['can_accept_new'] || $isSuperAdmin) && $chatData['available_chats']->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($chatData['available_chats'] as $conv)
                                <a href="{{ route('admin.chat.show', $conv) }}" class="list-group-item list-group-item-action py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="position-relative">
                                            <div class="avatar-md bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                                                <span class="fw-bold text-warning">
                                                    {{ $conv->user ? strtoupper(substr($conv->user->first_name, 0, 1) . substr($conv->user->last_name, 0, 1)) : '??' }}
                                                </span>
                                            </div>
                                            @php $unread = $conv->unreadMessagesForAdmin(); @endphp
                                            @if($unread > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{ $unread }}
                                            </span>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="mb-0 fw-semibold text-truncate">
                                                    {{ $conv->user ? $conv->user->first_name . ' ' . $conv->user->last_name : 'Unknown User' }}
                                                </h6>
                                                <small class="text-muted flex-shrink-0 ms-2">
                                                    {{ $conv->last_message_at ? $conv->last_message_at->diffForHumans(null, true) : '' }}
                                                </small>
                                            </div>
                                            <p class="mb-1 text-muted small text-truncate">
                                                @if($conv->latestMessage)
                                                    {{ Str::limit($conv->latestMessage->message, 60) }}
                                                @else
                                                    <em>No messages yet</em>
                                                @endif
                                            </p>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1">
                                                    <iconify-icon icon="iconamoon:attention-circle-duotone" class="me-1"></iconify-icon>Unassigned
                                                </span>
                                                <small class="text-muted">{{ $conv->user ? $conv->user->email : '' }}</small>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="btn btn-sm btn-success">
                                                <iconify-icon icon="iconamoon:check-duotone" class="me-1"></iconify-icon>
                                                Claim
                                            </span>
                                        </div>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                            @elseif($staffStats['can_accept_new'] || $isSuperAdmin)
                            <div class="text-center py-5">
                                <div class="avatar-lg bg-success-subtle rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="iconamoon:check-circle-duotone" class="fs-1 text-success"></iconify-icon>
                                </div>
                                <h6 class="text-muted">All Caught Up!</h6>
                                <p class="text-muted small mb-0">No unassigned chats waiting for response</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.avatar-sm {
    width: 36px;
    height: 36px;
}
.avatar-md {
    width: 48px;
    height: 48px;
}
.avatar-lg {
    width: 56px;
    height: 56px;
}
.min-width-0 {
    min-width: 0;
}
.btn-soft-info {
    color: #0dcaf0;
    background-color: rgba(13, 202, 240, 0.1);
    border: none;
}
.btn-soft-info:hover {
    color: #fff;
    background-color: #0dcaf0;
}
.btn-soft-secondary {
    color: #6c757d;
    background-color: rgba(108, 117, 125, 0.1);
    border: none;
}
.btn-soft-secondary:hover {
    color: #fff;
    background-color: #6c757d;
}
.list-group-item-action:hover {
    background-color: #f8f9fa;
}
.nav-tabs .nav-link {
    border: none;
    color: #6c757d;
    padding: 1rem 1.25rem;
    font-weight: 500;
}
.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 2px solid #0d6efd;
    background: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    border-color: transparent;
    color: #0d6efd;
}
</style>
@endsection
