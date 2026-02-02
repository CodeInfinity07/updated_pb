@extends('admin.layouts.vertical', ['title' => 'Staff Dashboard', 'subTitle' => 'System Overview'])

@section('content')

    {{-- System Health - Collapsible at Top --}}
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom d-flex align-items-center py-3" 
                     role="button" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#systemHealthCollapse" 
                     aria-expanded="true" 
                     aria-controls="systemHealthCollapse">
                    <div class="d-flex align-items-center flex-grow-1">
                        @if($dashboardData['system_health']['status'] === 'healthy')
                            <div class="avatar-sm bg-success bg-opacity-15 rounded-circle d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:check-circle-duotone" class="text-success fs-4"></iconify-icon>
                            </div>
                        @else
                            <div class="avatar-sm bg-warning bg-opacity-15 rounded-circle d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:warning-duotone" class="text-warning fs-4"></iconify-icon>
                            </div>
                        @endif
                        <div>
                            <h5 class="mb-0 fw-semibold">System Health</h5>
                            <small class="text-muted">Last checked: {{ now()->format('M d, Y H:i') }}</small>
                        </div>
                    </div>
                    <span class="badge bg-{{ $dashboardData['system_health']['status'] === 'healthy' ? 'success' : 'warning' }} px-3 py-2 me-3">
                        {{ ucfirst($dashboardData['system_health']['status']) }}
                    </span>
                    <iconify-icon icon="iconamoon:arrow-down-2-duotone" class="fs-4 text-muted collapse-icon"></iconify-icon>
                </div>
                <div class="collapse show" id="systemHealthCollapse">
                    <div class="card-body py-3">
                        <div class="row g-3">
                            <div class="col-6 col-lg-3">
                                <div class="d-flex align-items-center p-3 rounded-3 {{ $dashboardData['system_health']['database']['status'] === 'connected' ? 'bg-success' : 'bg-danger' }} bg-opacity-10">
                                    <iconify-icon icon="material-symbols:database" class="fs-3 {{ $dashboardData['system_health']['database']['status'] === 'connected' ? 'text-success' : 'text-danger' }} me-3"></iconify-icon>
                                    <div>
                                        <div class="fw-semibold">Database</div>
                                        <small class="text-muted">{{ ucfirst($dashboardData['system_health']['database']['status']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                @php
                                    $diskColor = $dashboardData['system_health']['disk_usage'] > 80 ? 'danger' : ($dashboardData['system_health']['disk_usage'] > 60 ? 'warning' : 'success');
                                @endphp
                                <div class="d-flex align-items-center p-3 rounded-3 bg-{{ $diskColor }} bg-opacity-10">
                                    <iconify-icon icon="material-symbols:hard-drive-outline" class="fs-3 text-{{ $diskColor }} me-3"></iconify-icon>
                                    <div>
                                        <div class="fw-semibold">{{ $dashboardData['system_health']['disk_usage'] }}% Used</div>
                                        <small class="text-muted">Disk Space</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="d-flex align-items-center p-3 rounded-3 {{ $dashboardData['system_health']['cache']['status'] === 'active' ? 'bg-success' : 'bg-warning' }} bg-opacity-10">
                                    <iconify-icon icon="material-symbols:flash-on" class="fs-3 {{ $dashboardData['system_health']['cache']['status'] === 'active' ? 'text-success' : 'text-warning' }} me-3"></iconify-icon>
                                    <div>
                                        <div class="fw-semibold">Cache</div>
                                        <small class="text-muted">{{ ucfirst($dashboardData['system_health']['cache']['status']) }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-lg-3">
                                <div class="d-flex align-items-center p-3 rounded-3 bg-info bg-opacity-10">
                                    <iconify-icon icon="iconamoon:clock-duotone" class="fs-3 text-info me-3"></iconify-icon>
                                    <div>
                                        <div class="fw-semibold">{{ $dashboardData['system_health']['uptime'] }}</div>
                                        <small class="text-muted">Uptime</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- User Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="avatar-md bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2">
                        <iconify-icon icon="iconamoon:profile-duotone" class="fs-2 text-primary"></iconify-icon>
                    </div>
                    <h4 class="mb-0 fw-bold">{{ number_format($dashboardData['quick_stats']['registered_users']) }}</h4>
                    <small class="text-muted">Registered Users</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="avatar-md bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2">
                        <iconify-icon icon="iconamoon:profile-duotone" class="fs-2 text-secondary"></iconify-icon>
                    </div>
                    <h4 class="mb-0 fw-bold text-secondary">{{ number_format($dashboardData['quick_stats']['dummy_users']) }}</h4>
                    <small class="text-muted">Excluded from Stats</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="avatar-md bg-success bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2">
                        <iconify-icon icon="iconamoon:briefcase-duotone" class="fs-2 text-success"></iconify-icon>
                    </div>
                    <h4 class="mb-0 fw-bold text-success">{{ number_format($dashboardData['quick_stats']['active_users']) }}</h4>
                    <small class="text-muted">Active Investors</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="avatar-md bg-warning bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2">
                        <iconify-icon icon="iconamoon:profile-duotone" class="fs-2 text-warning"></iconify-icon>
                    </div>
                    <h4 class="mb-0 fw-bold text-warning">{{ number_format($dashboardData['quick_stats']['inactive_users']) }}</h4>
                    <small class="text-muted">Inactive Users</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="avatar-md bg-info bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2">
                        <iconify-icon icon="iconamoon:lightning-1-duotone" class="fs-2 text-info"></iconify-icon>
                    </div>
                    <h4 class="mb-0 fw-bold text-info" id="onlineUsersCount">{{ number_format($dashboardData['quick_stats']['online_users']) }}</h4>
                    <small class="text-muted">Online Now</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <div class="avatar-md bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2">
                        <iconify-icon icon="iconamoon:user-plus-duotone" class="fs-2 text-danger"></iconify-icon>
                    </div>
                    <h4 class="mb-0 fw-bold text-danger">{{ number_format($dashboardData['quick_stats']['today_registrations']) }}</h4>
                    <small class="text-muted">Today's Signups</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Support Tickets Card --}}
    @if(isset($dashboardData['support_stats']))
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                                <iconify-icon icon="iconamoon:comment-dots-duotone" class="text-info fs-4"></iconify-icon>
                            </div>
                            <h5 class="mb-0 fw-semibold">Support Overview</h5>
                        </div>
                        <a href="{{ route('admin.chat.index') }}" class="btn btn-sm btn-outline-primary">
                            <iconify-icon icon="solar:arrow-right-linear"></iconify-icon> View Chats
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="text-center p-3 rounded-3 bg-primary bg-opacity-10">
                                <h4 class="mb-1 fw-bold text-primary">{{ $dashboardData['support_stats']['my_open_chats'] }}</h4>
                                <small class="text-muted">My Open Chats</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="text-center p-3 rounded-3 bg-warning bg-opacity-10">
                                <h4 class="mb-1 fw-bold text-warning">{{ $dashboardData['support_stats']['unassigned_chats'] }}</h4>
                                <small class="text-muted">Unassigned Chats</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="text-center p-3 rounded-3 bg-info bg-opacity-10">
                                <h4 class="mb-1 fw-bold text-info">{{ $dashboardData['support_stats']['open_chats'] }}</h4>
                                <small class="text-muted">Open Chats</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="text-center p-3 rounded-3 bg-secondary bg-opacity-10">
                                <h4 class="mb-1 fw-bold text-secondary">{{ $dashboardData['support_stats']['pending_chats'] }}</h4>
                                <small class="text-muted">Pending Chats</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="text-center p-3 rounded-3 bg-danger bg-opacity-10">
                                <h4 class="mb-1 fw-bold text-danger">{{ $dashboardData['support_stats']['open_tickets'] }}</h4>
                                <small class="text-muted">Open Tickets</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="text-center p-3 rounded-3 bg-success bg-opacity-10">
                                <h4 class="mb-1 fw-bold text-success">{{ $dashboardData['support_stats']['pending_tickets'] }}</h4>
                                <small class="text-muted">Pending Tickets</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                            <iconify-icon icon="iconamoon:sign-in-duotone" class="text-primary fs-4"></iconify-icon>
                        </div>
                        <h5 class="mb-0 fw-semibold">Recent Logins</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="border-0 ps-3">User</th>
                                    <th class="border-0">IP Address</th>
                                    <th class="border-0 pe-3">Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboardData['recent_logins'] as $login)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-primary fw-semibold">{{ strtoupper(substr($login->first_name ?? 'U', 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    @if($login->id)
                                                    <a href="{{ route('admin.users.show', $login->id) }}" class="fw-medium text-primary text-decoration-none">{{ $login->first_name }} {{ $login->last_name }}</a>
                                                    @else
                                                    <div class="fw-medium text-dark">{{ $login->first_name }} {{ $login->last_name }}</div>
                                                    @endif
                                                    <small class="text-muted d-block">{{ $login->username ?? $login->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="bg-light px-2 py-1 rounded small">{{ $login->last_login_ip ?? 'N/A' }}</code>
                                        </td>
                                        <td class="pe-3">
                                            <small class="text-muted">{{ $login->last_login_at ? $login->last_login_at->diffForHumans() : 'N/A' }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-5">
                                            <iconify-icon icon="iconamoon:sign-in-duotone" class="fs-1 d-block mb-2 opacity-50"></iconify-icon>
                                            No recent logins
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom py-3">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-info bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                            <iconify-icon icon="iconamoon:arrow-left-right-duotone" class="text-info fs-4"></iconify-icon>
                        </div>
                        <h5 class="mb-0 fw-semibold">Recent Transactions</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="border-0 ps-3">User</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0 pe-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboardData['recent_transactions'] as $transaction)
                                    <tr>
                                        <td class="ps-3">
                                            @if($transaction->user)
                                            <a href="{{ route('admin.users.show', $transaction->user->id) }}" class="fw-medium text-primary text-decoration-none">{{ $transaction->user->first_name }} {{ $transaction->user->last_name }}</a>
                                            @else
                                            <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $typeConfig = [
                                                    'deposit' => ['color' => 'success', 'icon' => 'arrow-down'],
                                                    'withdrawal' => ['color' => 'danger', 'icon' => 'arrow-up'],
                                                    'commission' => ['color' => 'info', 'icon' => 'gift'],
                                                    'roi' => ['color' => 'primary', 'icon' => 'trending-up'],
                                                    'bonus' => ['color' => 'warning', 'icon' => 'star'],
                                                    'investment' => ['color' => 'secondary', 'icon' => 'briefcase'],
                                                ];
                                                $config = $typeConfig[$transaction->type] ?? ['color' => 'secondary', 'icon' => 'circle'];
                                            @endphp
                                            <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} fw-medium">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold">${{ number_format($transaction->amount, 2) }}</span>
                                        </td>
                                        <td class="pe-3">
                                            @php
                                                $statusConfig = [
                                                    'completed' => 'success',
                                                    'pending' => 'warning',
                                                    'failed' => 'danger',
                                                    'cancelled' => 'secondary',
                                                ];
                                                $statusColor = $statusConfig[$transaction->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                                {{ ucfirst($transaction->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">
                                            <iconify-icon icon="iconamoon:arrow-left-right-duotone" class="fs-1 d-block mb-2 opacity-50"></iconify-icon>
                                            No recent transactions
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3">
                            <iconify-icon icon="iconamoon:profile-circle-duotone" class="text-primary fs-4"></iconify-icon>
                        </div>
                        <h5 class="mb-0 fw-semibold">Recent Users</h5>
                    </div>
                    @if(auth()->user()->hasAdminPermission('users.view'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                            <iconify-icon icon="iconamoon:eye-duotone" class="me-1"></iconify-icon>
                            View All
                        </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="border-0 ps-3">User</th>
                                    <th class="border-0">Email</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">KYC</th>
                                    <th class="border-0">Joined</th>
                                    <th class="border-0 pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dashboardData['recent_users'] as $recentUser)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-primary fw-semibold">{{ strtoupper(substr($recentUser->first_name ?? 'U', 0, 1)) }}</span>
                                                </div>
                                                <div>
                                                    @if($recentUser->id)
                                                    <a href="{{ route('admin.users.show', $recentUser->id) }}" class="fw-medium text-primary text-decoration-none">{{ $recentUser->first_name }} {{ $recentUser->last_name }}</a>
                                                    @else
                                                    <div class="fw-medium text-dark">{{ $recentUser->first_name }} {{ $recentUser->last_name }}</div>
                                                    @endif
                                                    <small class="text-muted d-block">{{ $recentUser->username }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ $recentUser->email }}</span>
                                        </td>
                                        <td>
                                            @if($recentUser->status === 'active')
                                                <span class="badge bg-success-subtle text-success">
                                                    <iconify-icon icon="iconamoon:check-circle-duotone" class="me-1"></iconify-icon>
                                                    Active
                                                </span>
                                            @elseif($recentUser->status === 'blocked')
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <iconify-icon icon="iconamoon:close-circle-duotone" class="me-1"></iconify-icon>
                                                    Blocked
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($recentUser->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $kycStatus = $recentUser->profile->kyc_status ?? 'not_submitted';
                                                $kycConfig = [
                                                    'verified' => ['color' => 'success', 'icon' => 'check-circle-duotone'],
                                                    'pending' => ['color' => 'warning', 'icon' => 'clock-duotone'],
                                                    'rejected' => ['color' => 'danger', 'icon' => 'close-circle-duotone'],
                                                    'not_submitted' => ['color' => 'secondary', 'icon' => 'file-document-duotone'],
                                                ];
                                                $kyc = $kycConfig[$kycStatus] ?? $kycConfig['not_submitted'];
                                            @endphp
                                            <span class="badge bg-{{ $kyc['color'] }}-subtle text-{{ $kyc['color'] }}">
                                                <iconify-icon icon="iconamoon:{{ $kyc['icon'] }}" class="me-1"></iconify-icon>
                                                {{ ucfirst(str_replace('_', ' ', $kycStatus)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $recentUser->created_at->format('M d, Y') }}</small>
                                            <br>
                                            <small class="text-muted opacity-75">{{ $recentUser->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td class="pe-3 text-end">
                                            @if(auth()->user()->hasAdminPermission('users.view'))
                                                <a href="{{ route('admin.users.show', $recentUser) }}" class="btn btn-sm btn-light border d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="iconamoon:eye-duotone"></iconify-icon>
                                                    View
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <iconify-icon icon="iconamoon:profile-circle-duotone" class="fs-1 d-block mb-2 opacity-50"></iconify-icon>
                                            No users found
                                        </td>
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

@push('styles')
<style>
    .collapse-icon {
        transition: transform 0.3s ease;
    }
    [aria-expanded="false"] .collapse-icon {
        transform: rotate(-90deg);
    }
</style>
@endpush

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateOnlineUsers() {
        fetch('{{ route("admin.api.online-users") }}')
            .then(response => response.json())
            .then(data => {
                const countEl = document.getElementById('onlineUsersCount');
                if (countEl) {
                    countEl.textContent = data.count.toLocaleString();
                }
            })
            .catch(error => console.error('Error fetching online users:', error));
    }

    updateOnlineUsers();
    setInterval(updateOnlineUsers, 30000);
});
</script>
@endsection
