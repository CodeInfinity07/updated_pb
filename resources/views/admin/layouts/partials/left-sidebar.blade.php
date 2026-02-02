<!-- ADMIN Sidebar - Styled like User Sidebar -->
@php
    $user = Auth::user();
@endphp

<div class="main-nav">
    <!-- Sidebar Logo -->
    <div class="logo-box">
        <a href="#" class="logo-dark">
            <img src="/images/logo-dark.png" class="logo-sm" alt="logo sm" />
        </a>

        <a href="#" class="logo-light">
            <img src="/images/logo-light.png" class="logo-sm" alt="logo sm" />
        </a>
    </div>

    <!-- Menu Toggle Button (sm-hover) -->
    <button type="button" class="button-sm-hover" aria-label="Show Full Sidebar">
        <iconify-icon icon="iconamoon:arrow-left-4-square-duotone" class="button-sm-hover-icon"></iconify-icon>
    </button>

    <div class="scrollbar" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">

            @if($user->admin_role_id !== 1)
            <li class="nav-item mb-2">
                <a class="nav-link bg-primary bg-opacity-10 rounded" href="{{ route('dashboard') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="text-primary"></iconify-icon>
                    </span>
                    <span class="nav-text text-primary fw-medium"> Back to My Account </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('dashboard.view'))
            <li class="menu-title">Dashboard</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols-light:overview-key-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Overview </span>
                </a>
            </li>

            @endif

            @if($user->hasAdminPermission('budget.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dashboard.budget') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:calculator-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Budget </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('analytics.view'))
            <li class="menu-title">Reports</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.analytics') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="hugeicons:analytics-03"></iconify-icon>
                    </span>
                    <span class="nav-text"> Analytics </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.reports.login.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="icon-park-outline:sales-report"></iconify-icon>
                    </span>
                    <span class="nav-text"> Logins </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('logs.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.system-logs.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:file-document-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> System Logs </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('users.view'))
            <li class="menu-title">User Management</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:users-group-two-rounded-linear"></iconify-icon>
                    </span>
                    <span class="nav-text"> All Users </span>
                    <span class="badge bg-primary badge-pill text-end">{{ \App\Models\User::count() }}</span>
                </a>
            </li>

            @if($user->hasAdminPermission('users.create'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.users.create') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="tabler:users-plus"></iconify-icon>
                    </span>
                    <span class="nav-text"> Add New User </span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.staff.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="solar:shield-user-linear"></iconify-icon>
                    </span>
                    <span class="nav-text"> Staff Management </span>
                    <span class="badge bg-info badge-pill text-end">{{ \App\Models\User::staff()->count() }}</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.blocked-users.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="mage:user-cross"></iconify-icon>
                    </span>
                    <span class="nav-text"> Blocked Users </span>
                    <span
                        class="badge bg-danger badge-pill text-end">{{ \App\Models\User::where('status', 'blocked')->count() }}</span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('kyc.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.kyc.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="arcticons:laokyc"></iconify-icon>
                    </span>
                    <span class="nav-text"> KYC Management </span>
                    @php
                        $pendingKyc = \App\Models\User::whereHas('profile', function ($q) {
                            $q->where('kyc_status', 'pending');
                        })->count();
                    @endphp
                    @if($pendingKyc > 0)
                        <span class="badge bg-warning badge-pill text-end">{{ $pendingKyc }}</span>
                    @endif
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('users.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dummy-users.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="mdi:account-off"></iconify-icon>
                    </span>
                    <span class="nav-text"> Dummy Users </span>
                    <span class="badge bg-secondary badge-pill text-end">{{ \App\Models\User::where('excluded_from_stats', true)->count() }}</span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('investments.view') || $user->hasAdminPermission('withdrawals.view') || $user->hasAdminPermission('deposits.view'))
            <li class="menu-title">Financial Management</li>

            @if($user->hasAdminPermission('investments.view') || $user->hasAdminPermission('withdrawals.view') || $user->hasAdminPermission('deposits.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.finance.transactions.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:invoice-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> All Transactions </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('investments.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.investment.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:package-2-sharp"></iconify-icon>
                    </span>
                    <span class="nav-text"> Packages & Plans </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('withdrawals.approve'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.finance.withdrawals.pending') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:clock-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Pending Approvals </span>
                    @php
                        $pendingTransactions = \App\Models\Transaction::where('status', 'pending')
                            ->where('type', 'withdrawal')
                            ->count();
                    @endphp
                    <span class="badge bg-danger badge-pill text-end">{{ $pendingTransactions }}</span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('deposits.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.finance.wallets.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:wallet-sharp"></iconify-icon>
                    </span>
                    <span class="nav-text"> Wallet Management </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.finance.cryptocurrencies.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:send-money-rounded"></iconify-icon>
                    </span>
                    <span class="nav-text"> Currency Management </span>
                </a>
            </li>
            @endif
            @endif

            @if($user->hasAdminPermission('settings.view'))
            <li class="menu-title">System Management</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.settings.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:settings-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> System Settings </span>
                </a>
            </li>

            @if($user->hasAdminPermission('email.settings'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.email-settings.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:email-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Email Settings </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('push.send'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.notifications.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:notification-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Notifications </span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.pixels.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="mdi:google-ads"></iconify-icon>
                    </span>
                    <span class="nav-text"> Tracking Pixels </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.maintenance.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="ix:maintenance"></iconify-icon>
                    </span>
                    <span class="nav-text"> Maintenance Mode </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="#">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:backup-rounded"></iconify-icon>
                    </span>
                    <span class="nav-text"> Backup & Restore </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('roles.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.roles.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:shield-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Role Management </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('crm.view'))
            <li class="menu-title">CRM System</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.crm.dashboard') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="cib:civicrm"></iconify-icon>
                    </span>
                    <span class="nav-text"> CRM Dashboard </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.crm.leads.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="simple-icons:googleads"></iconify-icon>
                    </span>
                    <span class="nav-text"> All Leads </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.crm.forms.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:forms-add-on"></iconify-icon>
                    </span>
                    <span class="nav-text"> Forms Management </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.crm.followups.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="arcticons:chieffollow"></iconify-icon>
                    </span>
                    <span class="nav-text"> Follow-ups </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.crm.assignments.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:assignment-turned-in-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> Assignments </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('commission.view'))
            <li class="menu-title">Referral System</li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.referrals.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="icon-park-twotone:peoples-two"></iconify-icon>
                    </span>
                    <span class="nav-text"> Referrals List </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.referrals.overview') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:leaderboard-outline"></iconify-icon>
                    </span>
                    <span class="nav-text"> 10-Level Breakdown </span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.referrals.tree') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:account-tree-outline-rounded"></iconify-icon>
                    </span>
                    <span class="nav-text"> Referral Tree </span>
                </a>
            </li>

            @if($user->hasAdminPermission('commission.manage'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.referrals.commission.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="carbon:deploy-rules"></iconify-icon>
                    </span>
                    <span class="nav-text"> Commission Rules </span>
                </a>
            </li>
            @endif
            @endif

            @if($user->hasAdminPermission('announcements.view') || $user->hasAdminPermission('email.send') || $user->hasAdminPermission('leaderboards.view') || $user->hasAdminPermission('push.send') || $user->hasAdminPermission('salary.view'))
            <li class="menu-title">Communication</li>
            @endif

            @if($user->hasAdminPermission('announcements.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.announcements.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="mingcute:announcement-line"></iconify-icon>
                    </span>
                    <span class="nav-text"> Announcements </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('email.send'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.mass-email.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:attach-email-outline-sharp"></iconify-icon>
                    </span>
                    <span class="nav-text"> Mass Email </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('leaderboards.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.leaderboards.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconoir:leaderboard-star"></iconify-icon>
                    </span>
                    <span class="nav-text"> Leaderboards </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.leaderboards.pending-prizes') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="mdi:trophy-award"></iconify-icon>
                    </span>
                    <span class="nav-text"> Pending Prizes </span>
                    @php
                        $pendingPrizes = \App\Models\LeaderboardPosition::prizePending()->count();
                    @endphp
                    @if($pendingPrizes > 0)
                        <span class="badge bg-warning badge-pill text-end">{{ $pendingPrizes }}</span>
                    @endif
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('salary.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.salary.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="mdi:cash-multiple"></iconify-icon>
                    </span>
                    <span class="nav-text"> Monthly Salary </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('ranks.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.ranks.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="akar-icons:trophy"></iconify-icon>
                    </span>
                    <span class="nav-text"> Rank & Reward </span>
                    @php
                        $pendingRankRewards = \App\Models\UserRank::where('reward_paid', false)->count();
                    @endphp
                    @if($pendingRankRewards > 0)
                        <span class="badge bg-warning badge-pill text-end">{{ $pendingRankRewards }}</span>
                    @endif
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('push.send'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.push.index') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="emojione-monotone:pushpin"></iconify-icon>
                    </span>
                    <span class="nav-text"> Push Notifications </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('users.view') || $user->hasAdminPermission('support.view') || $user->hasAdminPermission('support.chat'))
            <li class="menu-title">Quick Actions</li>

            @if($user->hasAdminPermission('users.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.impersonation.index') }}" onclick="viewAsUser()">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:eye-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> View as User </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('users.impersonate'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.impersonation.history') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:history-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Impersonation Logs </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('support.view'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.support.index') }}" onclick="viewAsUser()">
                    <span class="nav-icon">
                        <iconify-icon icon="material-symbols:contact-support-outline-rounded"></iconify-icon>
                    </span>
                    <span class="nav-text"> Support </span>
                </a>
            </li>

            @if($user->hasAdminPermission('support.chat'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.chat.index') }}" onclick="viewAsUser()">
                    <span class="nav-icon">
                        <iconify-icon icon="iconamoon:comment-dots-duotone"></iconify-icon>
                    </span>
                    <span class="nav-text"> Live Chat </span>
                </a>
            </li>
            @endif

            @if($user->hasAdminPermission('support.staff_chats'))
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.chat.staff-chats') }}">
                    <span class="nav-icon">
                        <iconify-icon icon="streamline:code-analysis"></iconify-icon>
                    </span>
                    <span class="nav-text"> Chat Analysis </span>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.faq.index') }}" onclick="viewAsUser()">
                    <span class="nav-icon">
                        <iconify-icon icon="streamline-ultimate:contact-us-faq"></iconify-icon>
                    </span>
                    <span class="nav-text"> FAQ </span>
                </a>
            </li>
            @endif
            @endif

        </ul>
    </div>
</div>

@section('script')
    <script>
        function viewAsUser() {
            // Functionality to switch to user view
            console.log('Switching to user view...');
        }

        function toggleMaintenanceMode() {
            if (confirm('Are you sure you want to toggle maintenance mode?')) {
                // Add AJAX call to toggle maintenance mode
                console.log('Toggling maintenance mode...');
            }
        }

        function clearCache() {
            if (confirm('Are you sure you want to clear the application cache?')) {
                // Add AJAX call to clear cache
                console.log('Clearing cache...');
            }
        }
    </script>
@endsection