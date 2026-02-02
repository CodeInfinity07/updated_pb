{{-- Create this file: resources/views/components/impersonation-banner.blade.php --}}

@php
    $originalAdminId = Session::get('impersonation.original_admin_id');
    $targetUserId = Session::get('impersonation.target_user_id'); 
    $startedAt = Session::get('impersonation.started_at');
    
    $isImpersonating = $originalAdminId && $targetUserId;
    
    if ($isImpersonating) {
        try {
            $originalAdmin = \App\Models\User::find($originalAdminId);
            $currentUser = auth()->user();
            $duration = $startedAt ? now()->diffForHumans($startedAt, true) : 'Unknown';
        } catch (Exception $e) {
            $isImpersonating = false;
        }
    }
@endphp

@if($isImpersonating && isset($originalAdmin) && isset($currentUser))
<div class="impersonation-banner bg-warning text-dark py-2 sticky-top" style="z-index: 1030;">
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2">
            <div class="d-flex flex-column flex-sm-row align-items-center gap-1 gap-sm-2 text-center text-sm-start">
                <div class="d-flex align-items-center">
                    <iconify-icon icon="iconamoon:shield-warning-duotone" class="fs-5 me-1"></iconify-icon>
                    <span class="fw-semibold">IMPERSONATING:</span>
                </div>
                <div>
                    <strong>{{ $currentUser->full_name }}</strong>
                    <span class="text-muted d-none d-sm-inline">({{ $currentUser->email }})</span>
                </div>
                <div class="small text-muted d-none d-md-block">
                    Started {{ $duration }} ago as {{ $originalAdmin->full_name }}
                </div>
            </div>
            <a href="{{ route('admin.impersonation.stop') }}" class="btn btn-dark btn-sm d-flex align-items-center flex-shrink-0">
                <iconify-icon icon="iconamoon:exit-duotone" class="me-1"></iconify-icon>
                Stop
            </a>
        </div>
    </div>
</div>

<style>
.impersonation-banner {
    border-bottom: 2px solid #ffc107;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.impersonation-banner .btn {
    font-size: 0.8rem;
    padding: 0.25rem 0.75rem;
    white-space: nowrap;
}

@media (max-width: 576px) {
    .impersonation-banner {
        font-size: 0.85rem;
        padding: 0.5rem 0;
    }
    
    .impersonation-banner .fw-semibold {
        font-size: 0.75rem;
    }
}
</style>
@endif