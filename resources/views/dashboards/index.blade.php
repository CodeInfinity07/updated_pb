@extends('layouts.vertical', ['title' => 'Dashboard', 'subTitle' => 'Home'])

@section('content')

    {{-- Welcome Greeting --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0 me-3">
                    <div class="avatar-md rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center">
                        <iconify-icon icon="ph:hand-waving-duotone" class="fs-28 text-primary"></iconify-icon>
                    </div>
                </div>
                <div>
                    <h4 class="mb-1 fw-semibold">Hi, {{ auth()->user()->first_name ?: auth()->user()->username }}!</h4>
                    <p class="text-muted mb-0 small">Welcome back to your dashboard</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Phone Verification Warning --}}
    @if(!auth()->user()->profile->phone_verified)
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center" role="alert">
                    <iconify-icon icon="iconamoon:warning-duotone" class="fs-20 me-2"></iconify-icon>
                    <div class="flex-grow-1">
                        <strong>Phone Verification Required!</strong>
                        Your phone number is not verified. Please verify your phone number to access all features.
                        <a href="{{ route('phone.verify') }}" class="alert-link ms-2">Verify Now</a>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    {{-- KYC Verification Warning --}}
    @if(auth()->user()->profile->kyc_status !== 'verified')
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                    <iconify-icon icon="iconamoon:profile-duotone" class="fs-20 me-2"></iconify-icon>
                    <div class="flex-grow-1">
                        <strong>KYC Verification
                            {{ auth()->user()->profile->kyc_status === 'pending' ? 'Pending' : 'Required' }}!</strong>
                        @if(auth()->user()->profile->kyc_status === 'under_review' || auth()->user()->profile->kyc_status === 'submitted')
                            Your KYC verification is under review. You'll be notified once approved.
                        @else
                            Complete your KYC verification to unlock all platform features.
                            <a href="{{ route('kyc.index') }}" class="alert-link ms-2">Complete KYC</a>
                        @endif
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    {{-- Main Balance Card --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <h6 class="text-muted mb-1">Available Balance</h6>
                            <h2 class="mb-0 fw-bold text-primary">
                                ${{ number_format($dashboardData['available_balance'] ?? 0, 2) }}
                            </h2>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('wallets.deposit.wallet') }}" class="btn btn-success">
                                Deposit
                            </a>
                            <a href="{{ route('wallets.withdraw.wallet') }}"  class="btn btn-warning">
                                Withdraw
                            </a>
                        </div>
                    </div>

                    {{-- Quick Stats Row --}}
                    <div class="row g-3">
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center p-2 bg-success-subtle rounded">
                                <div>
                                    <div class="fw-semibold">${{ number_format($dashboardData['total_earnings'] ?? 0, 2) }}
                                    </div>
                                    <small class="text-muted">Total Earnings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center p-2 bg-info-subtle rounded">
                                <div>
                                    <div class="fw-semibold">${{ number_format($dashboardData['today_earnings'] ?? 0, 2) }}
                                    </div>
                                    <small class="text-muted">Today's Earnings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center p-2 bg-primary-subtle rounded">
                                <div>
                                    <div class="fw-semibold">
                                        ${{ number_format($dashboardData['total_investments'] ?? 0, 2) }}</div>
                                    <small class="text-muted">Total Investments</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="d-flex align-items-center p-2 bg-warning-subtle rounded">
                                <div>
                                    <div class="fw-semibold">$5.00</div>
                                    <small class="text-muted">Mystery Box</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Expiry Multiplier Status --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-{{ $dashboardData['expiry_qualification']['qualifies'] ? 'success' : 'warning' }}">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-lg">
                                <span class="avatar-title bg-{{ $dashboardData['expiry_qualification']['qualifies'] ? 'success' : 'warning' }}-subtle text-{{ $dashboardData['expiry_qualification']['qualifies'] ? 'success' : 'warning' }} rounded-circle">
                                    <iconify-icon icon="{{ $dashboardData['expiry_qualification']['qualifies'] ? 'iconamoon:check-circle-1-duotone' : 'iconamoon:clock-duotone' }}" class="fs-32"></iconify-icon>
                                </span>
                            </div>
                            <div>
                                <h5 class="mb-1">Account Multiplier Status</h5>
                                <h3 class="mb-0 fw-bold text-{{ $dashboardData['expiry_qualification']['qualifies'] ? 'success' : 'warning' }}">
                                    {{ $dashboardData['expiry_qualification']['current_multiplier'] }}x Earnings Cap
                                </h3>
                                <small class="text-muted">
                                    @if($dashboardData['expiry_qualification']['qualifies'])
                                        You qualify for the maximum 6x earnings multiplier!
                                    @else
                                        Upgrade to <strong>6x</strong> by meeting referral requirements below
                                    @endif
                                </small>
                            </div>
                        </div>
                        @if(!$dashboardData['expiry_qualification']['qualifies'])
                            <a href="{{ route('referrals.index') }}" class="btn btn-outline-warning">
                                View Referrals
                            </a>
                        @endif
                    </div>

                    @if($dashboardData['expiry_qualification']['qualifies'] && !empty($dashboardData['expiry_qualification']['qualified_by']))
                        <hr class="my-3">
                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="mb-3">
                                    <iconify-icon icon="iconamoon:check-circle-1-duotone" class="me-1 text-success"></iconify-icon>
                                    Qualification Completed Via:
                                </h6>
                            </div>
                            @foreach($dashboardData['expiry_qualification']['qualified_by'] as $qualification)
                            <div class="col-md-6">
                                <div class="p-3 border rounded border-success bg-success-subtle">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0">
                                            <iconify-icon icon="{{ $qualification['option'] == 1 ? 'iconamoon:profile-circle-duotone' : 'iconamoon:git-branch-duotone' }}" class="me-1"></iconify-icon>
                                            Option {{ $qualification['option'] }}: {{ $qualification['name'] }}
                                        </h6>
                                        <span class="badge bg-success">Completed</span>
                                    </div>
                                    <small class="text-success d-block">
                                        {{ $qualification['description'] }}
                                    </small>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!$dashboardData['expiry_qualification']['qualifies'])
                        <hr class="my-3">
                        <div class="row g-3">
                            {{-- Option 1: Direct Referrals with Min Investment --}}
                            <div class="col-md-6">
                                <div class="p-3 border rounded {{ $dashboardData['expiry_qualification']['option_1']['met'] ? 'border-success bg-success-subtle' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0">
                                            <iconify-icon icon="iconamoon:profile-circle-duotone" class="me-1"></iconify-icon>
                                            Option 1: Direct Referrals
                                        </h6>
                                        @if($dashboardData['expiry_qualification']['option_1']['met'])
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        Each referral must have ${{ number_format($dashboardData['expiry_qualification']['option_1']['min_investment'] ?? 50, 0) }}+ invested
                                    </small>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 8px;">
                                            @php
                                                $progress1 = min(100, ($dashboardData['expiry_qualification']['option_1']['current'] / max(1, $dashboardData['expiry_qualification']['option_1']['required'])) * 100);
                                            @endphp
                                            <div class="progress-bar bg-{{ $dashboardData['expiry_qualification']['option_1']['met'] ? 'success' : 'warning' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $progress1 }}%"></div>
                                        </div>
                                        <span class="fw-semibold">
                                            {{ $dashboardData['expiry_qualification']['option_1']['current'] }}/{{ $dashboardData['expiry_qualification']['option_1']['required'] }}
                                        </span>
                                    </div>
                                    @if(!$dashboardData['expiry_qualification']['option_1']['met'])
                                        <small class="text-muted">
                                            Need {{ $dashboardData['expiry_qualification']['option_1']['required'] - $dashboardData['expiry_qualification']['option_1']['current'] }} more qualified referrals
                                        </small>
                                    @endif
                                </div>
                            </div>

                            {{-- Option 2: Tiered Referrals --}}
                            <div class="col-md-6">
                                <div class="p-3 border rounded {{ $dashboardData['expiry_qualification']['option_2']['all_met'] ? 'border-success bg-success-subtle' : '' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6 class="mb-0">
                                            <iconify-icon icon="iconamoon:git-branch-duotone" class="me-1"></iconify-icon>
                                            Option 2: Tiered Referrals
                                        </h6>
                                        @if($dashboardData['expiry_qualification']['option_2']['all_met'])
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($dashboardData['expiry_qualification']['option_2']['levels'] as $level => $data)
                                            <div class="d-flex align-items-center gap-1 px-2 py-1 rounded {{ $data['met'] ? 'bg-success-subtle text-success' : 'bg-light' }}" style="font-size: 0.8rem;">
                                                <span>L{{ $level }}:</span>
                                                <span class="fw-semibold">{{ $data['current'] }}/{{ $data['required'] }}</span>
                                                @if($data['met'])
                                                    <iconify-icon icon="iconamoon:check-duotone" class="text-success"></iconify-icon>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards Row --}}
    <div class="row mb-4">
        {{-- Deposits Summary --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        Deposits
                    </h5>
                    <a href="{{ route('transactions.index') }}?type=deposit" class="btn btn-sm btn-outline-primary">View
                        All</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success mb-1">${{ number_format($dashboardData['last_deposit'] ?? 0, 2) }}
                                </h4>
                                <small class="text-muted">Last Deposit</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-success mb-1">${{ number_format($dashboardData['total_deposits'] ?? 0, 2) }}
                                </h4>
                                <small class="text-muted">Total Deposits</small>
                            </div>
                        </div>
                    </div>
                    @if(isset($dashboardData['recent_deposits']) && count($dashboardData['recent_deposits']) > 0)
                        <div class="mt-3">
                            <h6 class="mb-2">Recent Deposits</h6>
                            @foreach($dashboardData['recent_deposits'] as $deposit)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold">${{ number_format($deposit->amount, 2) }}</div>
                                        <small class="text-muted">{{ $deposit->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <span
                                        class="badge bg-{{ $deposit->status === 'completed' ? 'success' : ($deposit->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($deposit->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Withdrawals Summary --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        Withdrawals
                    </h5>
                    <a href="{{ route('transactions.index') }}?type=withdrawal" class="btn btn-sm btn-outline-primary">View
                        All</a>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning mb-1">
                                    ${{ number_format($dashboardData['pending_withdrawals'] ?? 0, 2) }}</h4>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 border rounded">
                                <h4 class="text-warning mb-1">
                                    ${{ number_format($dashboardData['total_withdrawals'] ?? 0, 2) }}</h4>
                                <small class="text-muted">Total</small>
                            </div>
                        </div>
                    </div>
                    @if(isset($dashboardData['recent_withdrawals']) && count($dashboardData['recent_withdrawals']) > 0)
                        <div class="mt-3">
                            <h6 class="mb-2">Recent Withdrawals</h6>
                            @foreach($dashboardData['recent_withdrawals'] as $withdrawal)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold">${{ number_format($withdrawal->amount, 2) }}</div>
                                        <small class="text-muted">{{ $withdrawal->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <span
                                        class="badge bg-{{ $withdrawal->status === 'completed' ? 'success' : ($withdrawal->status === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($withdrawal->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Referral Tree Component - MOBILE RESPONSIVE --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        <iconify-icon icon="iconamoon:hierarchy-duotone" class="me-2"></iconify-icon>
                        <span id="treeTitleText">Referral Tree</span>
                    </h5>
                    <button id="showAllTiers" class="btn btn-sm btn-outline-primary d-none">
                        Show All Levels
                    </button>
                </div>

                <div class="card-body">
                    {{-- Loading State --}}
                    <div id="loadingState" class="text-center py-5">
                        <div class="spinner-border text-primary me-2" role="status"></div>
                        <span class="text-muted">Loading referral tree...</span>
                    </div>

                    {{-- Error State --}}
                    <div id="errorState" class="d-none">
                        <div class="alert alert-danger text-center">
                            <div class="fw-medium mb-2 text-danger">Error loading referral tree</div>
                            <div class="text-danger fs-14 mb-3" id="errorMessage"></div>
                            <button id="retryButton" class="btn btn-danger btn-sm">Try Again</button>
                        </div>
                    </div>

                    {{-- Main Content --}}
                    <div id="mainContent" class="d-none">
                        {{-- Level Selector & Status Filters Row --}}
                        <div class="row g-3 mb-4">
                            {{-- Level Dropdown Selector --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold mb-2">
                                    <iconify-icon icon="iconamoon:layers-duotone" class="me-1"></iconify-icon>
                                    Filter by Level
                                </label>
                                <select id="levelSelector" class="form-select">
                                    <option value="">All Levels (L1-L10)</option>
                                    <option value="1">Level 1 (L1) - Direct</option>
                                    <option value="2">L1-L2</option>
                                    <option value="3">L1-L3</option>
                                    <option value="4">L1-L4</option>
                                    <option value="5">L1-L5</option>
                                    <option value="6">L1-L6</option>
                                    <option value="7">L1-L7</option>
                                    <option value="8">L1-L8</option>
                                    <option value="9">L1-L9</option>
                                    <option value="10">L1-L10</option>
                                </select>
                            </div>
                            
                            {{-- Status Filter Buttons --}}
                            <div class="col-12 col-md-8">
                                <label class="form-label small fw-semibold mb-2">
                                    <iconify-icon icon="iconamoon:filter-duotone" class="me-1"></iconify-icon>
                                    Filter by Status
                                </label>
                                <div class="btn-group w-100" role="group">
                                    <button class="btn btn-outline-primary status-filter active flex-fill" data-status="all">
                                        All
                                    </button>
                                    <button class="btn btn-outline-success status-filter flex-fill" data-status="active">
                                        Active
                                    </button>
                                    <button class="btn btn-outline-danger status-filter flex-fill" data-status="inactive">
                                        Inactive
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Summary Stats Row --}}
                        <div class="row g-3 mb-4">
                            <div class="col-4">
                                <div class="card bg-primary-subtle border-0 h-100">
                                    <div class="card-body p-2 p-md-3 text-center">
                                        <div class="text-primary fw-semibold mb-1 fs-12 fs-md-14">Total</div>
                                        <h5 class="text-dark fw-bold mb-0" id="totalMembers">0</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card bg-success-subtle border-0 h-100">
                                    <div class="card-body p-2 p-md-3 text-center">
                                        <div class="text-success fw-semibold mb-1 fs-12 fs-md-14">Active</div>
                                        <h5 class="text-dark fw-bold mb-0" id="totalActive">0</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="card bg-danger-subtle border-0 h-100">
                                    <div class="card-body p-2 p-md-3 text-center">
                                        <div class="text-danger fw-semibold mb-1 fs-12 fs-md-14">Inactive</div>
                                        <h5 class="text-dark fw-bold mb-0" id="totalInactive">0</h5>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- User Count Display --}}
                        <div class="mb-3">
                            <span class="text-muted small" id="displayedUserCount">Showing 0 users</span>
                        </div>

                        {{-- Tree Structure Container - Mobile Enhanced --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="card bg-light-subtle border">
                                    <div class="card-body p-2 p-md-3" style="max-height: 500px; overflow-y: auto;">
                                        {{-- Back Navigation Header (shown when drilling into children) --}}
                                        <div id="backNavHeader" class="d-none mb-3">
                                            <button id="backNavBtn" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-1">
                                                <iconify-icon icon="iconamoon:arrow-left-2-duotone" style="font-size: 16px;"></iconify-icon>
                                                <span id="backNavText">Back</span>
                                            </button>
                                        </div>

                                        {{-- Tree Nodes Container --}}
                                        <div id="treeContainer" class="mobile-tree">
                                            <!-- Tree nodes will be dynamically generated here -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Instructions --}}
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="p-3 bg-light-subtle rounded text-muted">
                                    <iconify-icon icon="iconamoon:bulb-duotone" class="me-2"></iconify-icon>
                                    <span id="instructionText" class="fs-14">
                                        Use the Level dropdown to filter by depth • Click on members to expand their referrals • Use status filters to show active/inactive members.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Empty State --}}
                    <div id="emptyState" class="d-none text-center py-5">
                        <iconify-icon icon="iconamoon:hierarchy-duotone" class="fs-48 text-muted mb-3"></iconify-icon>
                        <p class="fs-14 text-muted">No referral data available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Referral Commission & Investments Row --}}
    <div class="row mb-4">
        {{-- Referral Commission Summary --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        Commissions
                    </h5>
                    <a href="{{ route('referrals.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-info mb-1">${{ number_format($dashboardData['total_referral_earnings'] ?? 0, 2) }}
                        </h3>
                        <small class="text-muted">Total Commission Earned</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-warning-subtle rounded">
                                <div class="fw-semibold">${{ number_format($dashboardData['pending_commissions'] ?? 0, 2) }}
                                </div>
                                <small class="text-muted">Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-info-subtle rounded">
                                <div class="fw-semibold">{{ $dashboardData['total_referrals'] ?? 0 }}</div>
                                <small class="text-muted">Referrals</small>
                            </div>
                        </div>
                    </div>
                    @if(isset($dashboardData['referral_link']))
                        <div class="mt-3">
                            <label class="form-label small">Referral Link</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="referralLink"
                                    value="{{ $dashboardData['referral_link'] }}" readonly>
                                <button class="btn btn-outline-secondary" onclick="copyReferralLink()">
                                    <iconify-icon icon="iconamoon:copy-duotone"></iconify-icon>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Investment Summary --}}
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">
                        Investments
                    </h5>
                    <a href="{{ route('user.investments') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary mb-1">${{ number_format($dashboardData['total_investments'] ?? 0, 2) }}</h3>
                        <small class="text-muted">Total Invested</small>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-2 bg-success-subtle rounded">
                                <div class="fw-semibold">{{ $dashboardData['active_investments'] ?? 0 }}</div>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-2 bg-primary-subtle rounded">
                                <div class="fw-semibold">${{ number_format($dashboardData['investment_returns'] ?? 0, 2) }}
                                </div>
                                <small class="text-muted">Returns</small>
                            </div>
                        </div>
                    </div>
                    @if(isset($dashboardData['recent_investments']) && count($dashboardData['recent_investments']) > 0)
                        <div class="mt-3">
                            <h6 class="mb-2">Recent Investments</h6>
                            @foreach($dashboardData['recent_investments'] as $investment)
                                <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                    <div>
                                        <div class="fw-semibold">${{ number_format($investment->amount, 2) }}</div>
                                        <small class="text-muted">{{ $investment->created_at->format('M d, Y') }}</small>
                                    </div>
                                    <span
                                        class="badge bg-{{ $investment->status === 'active' ? 'success' : ($investment->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($investment->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity & Quick Actions --}}
    <div class="row">
        {{-- Recent Transactions --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Activity</h5>
                    <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                @if(isset($dashboardData['recent_transactions']) && count($dashboardData['recent_transactions']) > 0)
                    <div class="card-body p-0">
                        {{-- Desktop Table View --}}
                        <div class="d-none d-lg-block">
                            <div class="table-responsive table-card">
                                <table class="table table-borderless table-hover table-nowrap align-middle mb-0">
                                    <thead class="bg-light bg-opacity-50 thead-sm">
                                        <tr>
                                            <th scope="col">Transaction ID</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Amount</th>
                                            <th scope="col">Timestamp</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($dashboardData['recent_transactions'] as $transaction)
                                            <tr>
                                                <td>
                                                    <code class="small">{{ Str::limit($transaction->transaction_id, 15) }}...</code>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }}-subtle text-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }} p-1">
                                                        {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                    </span>
                                                    @if(in_array($transaction->type, ['commission', 'profit_share']) && $transaction->description)
                                                        <div class="small text-muted mt-1 text-truncate" style="max-width: 180px;" title="{{ $transaction->description }}">
                                                            <iconify-icon icon="iconamoon:link-duotone" class="me-1"></iconify-icon>
                                                            {{ Str::limit($transaction->description, 25) }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong
                                                        class="{{ $transaction->type === 'withdrawal' ? 'text-danger' : 'text-success' }}">
                                                        {{ $transaction->type === 'withdrawal' ? '-' : '+' }}{{ $transaction->formatted_amount }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    {{ $transaction->created_at->format('d M, y') }}
                                                    <small
                                                        class="text-muted d-block">{{ $transaction->created_at->format('h:i:s A') }}</small>
                                                </td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : ($transaction->status === 'failed' ? 'danger' : 'secondary')) }}-subtle text-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : ($transaction->status === 'failed' ? 'danger' : 'secondary')) }} p-1">
                                                        {{ ucfirst($transaction->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Mobile Card View --}}
                        <div class="d-lg-none p-3">
                            <div class="row g-3">
                                @foreach($dashboardData['recent_transactions'] as $transaction)
                                    <div class="col-12">
                                        <div class="card transaction-card border">
                                            <div class="card-body p-3">
                                                {{-- Header Row --}}
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div class="d-flex gap-2">
                                                        <span
                                                            class="badge bg-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }}-subtle text-{{ $transaction->type === 'deposit' ? 'success' : ($transaction->type === 'withdrawal' ? 'warning' : 'info') }}">
                                                            {{ ucfirst($transaction->type) }}
                                                        </span>
                                                        <span
                                                            class="badge bg-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : ($transaction->status === 'failed' ? 'danger' : 'secondary')) }}-subtle text-{{ $transaction->status === 'completed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : ($transaction->status === 'failed' ? 'danger' : 'secondary')) }}">
                                                            {{ ucfirst($transaction->status) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Amount Row --}}
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <div>
                                                        <h6
                                                            class="mb-0 {{ $transaction->type === 'withdrawal' ? 'text-danger' : 'text-success' }}">
                                                            {{ $transaction->type === 'withdrawal' ? '-' : '+' }}{{ $transaction->formatted_amount }}
                                                        </h6>
                                                        <small
                                                            class="text-muted">{{ $transaction->created_at->format('M d, Y • H:i') }}</small>
                                                    </div>
                                                </div>

                                                @if(in_array($transaction->type, ['commission', 'profit_share']) && $transaction->description)
                                                    <div class="mb-2">
                                                        <small class="text-info">
                                                            <iconify-icon icon="iconamoon:link-duotone" class="me-1"></iconify-icon>
                                                            {{ $transaction->description }}
                                                        </small>
                                                    </div>
                                                @endif

                                                {{-- Transaction ID --}}
                                                <div class="d-flex align-items-center">
                                                    <code
                                                        class="small flex-grow-1">{{ Str::limit($transaction->transaction_id, 20) }}...</code>
                                                    <button class="btn btn-sm btn-outline-secondary ms-2"
                                                        onclick="copyText('{{ $transaction->transaction_id }}')">
                                                        <iconify-icon icon="iconamoon:copy-duotone"></iconify-icon>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card-body">
                        <div class="text-center py-4">
                            <iconify-icon icon="iconamoon:history-duotone" class="fs-1 text-muted"></iconify-icon>
                            <h6 class="text-muted mt-2">No recent transactions</h6>
                            <p class="text-muted">Your transaction history will appear here</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="{{ route('wallets.deposit.wallet') }}" class="btn btn-success btn-sm">
                                    Deposit
                                </a>
                                <a href="{{ route('wallets.withdraw.wallet') }}" class="btn btn-warning btn-sm">
                                    Withdraw
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function copyReferralLink() {
            const referralLink = document.getElementById('referralLink');
            referralLink.select();
            referralLink.setSelectionRange(0, 99999); // For mobile devices

            if (navigator.clipboard) {
                navigator.clipboard.writeText(referralLink.value).then(() => {
                    showAlert('Referral link copied to clipboard!', 'success');
                });
            } else {
                document.execCommand('copy');
                showAlert('Referral link copied to clipboard!', 'success');
            }
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alertDiv.innerHTML = `
                            ${message}
                            <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
                        `;

            document.body.appendChild(alertDiv);

            setTimeout(() => {
                if (alertDiv.parentNode) alertDiv.remove();
            }, 4000);
        }

        /**
         * User Announcement System
         * Handles displaying announcements to users as modals
         */

        class AnnouncementManager {
            constructor() {
                this.currentAnnouncementIndex = 0;
                this.pendingAnnouncements = [];
                this.isShowing = false;
                this.csrfToken = this.getCSRFToken();

                // Initialize when DOM is ready
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', () => this.init());
                } else {
                    this.init();
                }
            }

            /**
             * Initialize the announcement manager
             */
            init() {
                // Set up global function for middleware to call
                window.checkUserAnnouncements = (announcements) => {
                    this.handleAnnouncements(announcements);
                };

                // Also check for announcements via API as fallback
                this.checkForAnnouncements();
            }

            /**
             * Get CSRF token from meta tag or form
             */
            getCSRFToken() {
                const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (tokenMeta) {
                    return tokenMeta.getAttribute('content');
                }

                const tokenInput = document.querySelector('input[name="_token"]');
                if (tokenInput) {
                    return tokenInput.value;
                }

                return null;
            }

            /**
             * Check for pending announcements via API
             */
            async checkForAnnouncements() {
                try {
                    const response = await fetch('/api/user/announcements/pending', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success && data.announcements.length > 0) {
                            this.handleAnnouncements(data.announcements);
                        }
                    }
                } catch (error) {
                    console.error('Failed to check for announcements:', error);
                }
            }

            /**
             * Handle announcements from middleware or API
             */
            handleAnnouncements(announcements) {
                if (!announcements || announcements.length === 0) {
                    return;
                }

                // Store announcements sorted by priority
                this.pendingAnnouncements = announcements.sort((a, b) => a.priority - b.priority);

                // Show first announcement after a short delay
                setTimeout(() => {
                    this.showNextAnnouncement();
                }, 1000);
            }

            /**
             * Show the next pending announcement
             */
            showNextAnnouncement() {
                if (this.isShowing || this.currentAnnouncementIndex >= this.pendingAnnouncements.length) {
                    return;
                }

                const announcement = this.pendingAnnouncements[this.currentAnnouncementIndex];
                this.showAnnouncement(announcement);
            }

            /**
             * Show a specific announcement
             */
            showAnnouncement(announcement) {
                if (this.isShowing) {
                    return;
                }

                this.isShowing = true;

                // Create modal HTML
                const modalHTML = this.createModalHTML(announcement);

                // Append to body
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                // Get modal element and show it
                const modalElement = document.getElementById(`announcementModal${announcement.id}`);
                const modal = new bootstrap.Modal(modalElement, {
                    backdrop: announcement.is_dismissible ? true : 'static',
                    keyboard: announcement.is_dismissible
                });

                // Set up event listeners
                this.setupModalEvents(modalElement, modal, announcement);

                // Show the modal
                modal.show();
            }

            /**
             * Create modal HTML for announcement
             */
            createModalHTML(announcement) {
                const typeColors = {
                    'info': 'primary',
                    'success': 'success',
                    'warning': 'warning',
                    'danger': 'danger'
                };

                const color = typeColors[announcement.type] || 'primary';
                const dismissible = announcement.is_dismissible;
                const imageUrl = announcement.image_url || null;
                const hasImage = imageUrl && imageUrl.length > 0;

                const shareButtons = `
                    <div class="share-buttons">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="shareAnnouncement(${announcement.id}, 'facebook')" title="Share on Facebook">
                            <iconify-icon icon="mdi:facebook"></iconify-icon>
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="shareAnnouncement(${announcement.id}, 'twitter')" title="Share on Twitter">
                            <iconify-icon icon="mdi:twitter"></iconify-icon>
                        </button>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="shareAnnouncement(${announcement.id}, 'whatsapp')" title="Share on WhatsApp">
                            <iconify-icon icon="mdi:whatsapp"></iconify-icon>
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="shareAnnouncement(${announcement.id}, 'copy')" title="Copy Link">
                            <iconify-icon icon="mdi:content-copy"></iconify-icon>
                        </button>
                    </div>`;

                if (hasImage) {
                    return `
                        <div class="modal fade announcement-modal" id="announcementModal${announcement.id}" tabindex="-1" 
                             data-bs-backdrop="${dismissible ? 'true' : 'static'}" 
                             data-bs-keyboard="${dismissible ? 'true' : 'false'}">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content border-0 shadow-lg overflow-hidden">
                                    <div class="position-relative">
                                        ${dismissible ? `
                                        <button type="button" class="btn-close position-absolute bg-white rounded-circle p-2" data-bs-dismiss="modal" style="top: 10px; right: 10px; z-index: 10;"></button>
                                        ` : ''}
                                        <img src="${imageUrl}" alt="${this.escapeHtml(announcement.title)}" class="img-fluid w-100">
                                    </div>
                                </div>
                            </div>
                        </div>`;
                }

                return `
                    <div class="modal fade announcement-modal" id="announcementModal${announcement.id}" tabindex="-1" 
                         data-bs-backdrop="${dismissible ? 'true' : 'static'}" 
                         data-bs-keyboard="${dismissible ? 'true' : 'false'}">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow-lg">
                                <div class="modal-header bg-${color} text-white border-0">
                                    <h5 class="modal-title d-flex align-items-center">
                                        <iconify-icon icon="${announcement.type_icon}" class="me-2 fs-4"></iconify-icon>
                                        ${this.escapeHtml(announcement.title)}
                                    </h5>
                                    ${dismissible ? `
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    ` : ''}
                                </div>
                                <div class="modal-body">
                                    <div class="announcement-content">
                                        ${this.formatContent(announcement.content)}
                                    </div>
                                </div>
                                <div class="modal-footer border-0 justify-content-between">
                                    ${shareButtons}
                                    <div>
                                        ${announcement.button_link ? `
                                        <a href="${announcement.button_link}" 
                                           class="btn btn-${color} px-4" 
                                           onclick="window.announcementManager.markAsViewed(${announcement.id})">
                                            ${this.escapeHtml(announcement.button_text)}
                                        </a>
                                        ` : `
                                        <button type="button" 
                                                class="btn btn-${color} px-4" 
                                                onclick="window.announcementManager.markAsViewed(${announcement.id})" 
                                                data-bs-dismiss="modal">
                                            ${this.escapeHtml(announcement.button_text)}
                                        </button>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }

            /**
             * Set up modal event listeners
             */
            setupModalEvents(modalElement, modal, announcement) {
                // Handle modal hide event (add swipe up animation class)
                modalElement.addEventListener('hide.bs.modal', () => {
                    modalElement.classList.add('hiding');
                });
                
                // Handle modal hidden event
                modalElement.addEventListener('hidden.bs.modal', () => {
                    // Mark as viewed when modal is closed
                    this.markAsViewed(announcement.id);

                    // Remove modal element from DOM
                    modalElement.remove();
                    
                    // Explicitly remove any lingering backdrops
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('modal-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('padding-right');

                    // Reset state and show next announcement
                    this.isShowing = false;
                    this.currentAnnouncementIndex++;

                    // Show next announcement after short delay
                    setTimeout(() => {
                        this.showNextAnnouncement();
                    }, 500);
                });

                // Handle close button clicks
                const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
                closeButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        this.markAsViewed(announcement.id);
                    });
                });

                // Handle ESC key if dismissible
                if (announcement.is_dismissible) {
                    modalElement.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') {
                            this.markAsViewed(announcement.id);
                        }
                    });
                }
            }

            /**
             * Mark announcement as viewed
             */
            async markAsViewed(announcementId) {
                try {
                    const response = await fetch(`/api/user/announcements/${announcementId}/viewed`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        console.warn('Failed to mark announcement as viewed:', announcementId);
                    }
                } catch (error) {
                    console.error('Error marking announcement as viewed:', error);
                }
            }

            /**
             * Escape HTML to prevent XSS
             */
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            /**
             * Format announcement content
             */
            formatContent(content) {
                // Convert line breaks to <br> tags
                const escaped = this.escapeHtml(content);
                return escaped.replace(/\n/g, '<br>');
            }

            /**
             * Show announcement history modal
             */
            async showHistory() {
                try {
                    const response = await fetch('/api/user/announcements/history', {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': this.csrfToken
                        },
                        credentials: 'same-origin'
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.success) {
                            this.displayHistoryModal(data.announcements, data.pagination);
                        }
                    }
                } catch (error) {
                    console.error('Failed to load announcement history:', error);
                }
            }

            /**
             * Display history modal
             */
            displayHistoryModal(announcements, pagination) {
                const modalHTML = `
                            <div class="modal fade" id="announcementHistoryModal" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <iconify-icon icon="iconamoon:history-duotone" class="me-2"></iconify-icon>
                                                Announcement History
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            ${announcements.length > 0 ? `
                                            <div class="list-group">
                                                ${announcements.map(announcement => `
                                                <div class="list-group-item">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1 d-flex align-items-center">
                                                                <iconify-icon icon="${announcement.type_icon}" class="me-2"></iconify-icon>
                                                                ${this.escapeHtml(announcement.title)}
                                                            </h6>
                                                            <p class="mb-1">${this.formatContent(announcement.content.substring(0, 100))}${announcement.content.length > 100 ? '...' : ''}</p>
                                                            <small class="text-muted">${announcement.created_at}</small>
                                                        </div>
                                                        <div class="text-end ms-3">
                                                            <span class="badge ${announcement.type_badge_class}">${announcement.type}</span>
                                                            ${announcement.has_viewed ? `
                                                            <div class="small text-success mt-1">
                                                                <iconify-icon icon="iconamoon:profile-duotone" class="me-1"></iconify-icon>
                                                                Viewed ${announcement.viewed_ago}
                                                            </div>
                                                            ` : `
                                                            <div class="small text-muted mt-1">
                                                                <iconify-icon icon="iconamoon:circle-duotone" class="me-1"></iconify-icon>
                                                                Not viewed
                                                            </div>
                                                            `}
                                                        </div>
                                                    </div>
                                                </div>
                                                `).join('')}
                                            </div>
                                            ${pagination.total > pagination.per_page ? `
                                            <div class="text-center mt-3">
                                                <small class="text-muted">
                                                    Showing ${announcements.length} of ${pagination.total} announcements
                                                </small>
                                            </div>
                                            ` : ''}
                                            ` : `
                                            <div class="text-center py-4">
                                                <iconify-icon icon="iconamoon:notification-duotone" class="fs-1 text-muted mb-3"></iconify-icon>
                                                <h6 class="text-muted">No announcements yet</h6>
                                                <p class="text-muted small">You'll see announcements here when they're available.</p>
                                            </div>
                                            `}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            `;

                // Remove existing history modal if any
                const existingModal = document.getElementById('announcementHistoryModal');
                if (existingModal) {
                    existingModal.remove();
                }

                // Add new modal
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('announcementHistoryModal'));
                modal.show();

                // Clean up when hidden
                document.getElementById('announcementHistoryModal').addEventListener('hidden.bs.modal', function () {
                    this.remove();
                });
            }
        }

        // Share announcement function
        function shareAnnouncement(announcementId, platform) {
            const title = document.querySelector(`#announcementModal${announcementId} h5`)?.textContent || 'Check out this announcement!';
            const url = window.location.origin;
            const text = title;
            
            let shareUrl = '';
            
            switch(platform) {
                case 'facebook':
                    shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                    break;
                case 'twitter':
                    shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`;
                    break;
                case 'whatsapp':
                    shareUrl = `https://wa.me/?text=${encodeURIComponent(text + ' ' + url)}`;
                    break;
                case 'copy':
                    navigator.clipboard.writeText(url).then(() => {
                        alert('Link copied to clipboard!');
                    }).catch(() => {
                        alert('Failed to copy link');
                    });
                    return;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        }
        
        // Make it available globally
        window.shareAnnouncement = shareAnnouncement;

        // Initialize announcement manager
        window.announcementManager = new AnnouncementManager();

        // Refresh dashboard data every 30 seconds
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                // You can add AJAX calls here to refresh specific sections
                console.log('Dashboard auto-refresh');
            }
        }, 30000);
    </script>

    <script>
        class ReferralTreeManager {
            constructor() {
                this.originalTreeData = {};
                this.filteredTreeData = {};
                this.expandedNodes = new Set();
                this.focusedTier = null;
                this.statusFilter = 'all';
                this.stickyUser = null;
                this.loading = true;
                this.error = null;
                
                // Navigation state (simple stack for back navigation)
                this.currentViewUserId = null;
                this.navigationStack = []; // Stack of {id, name} for back navigation

                this.init();
            }

            init() {
                this.setupEventListeners();
                this.loadReferralTree();
            }

            setupEventListeners() {
                // Level dropdown selector
                document.getElementById('levelSelector')?.addEventListener('change', (e) => {
                    const level = e.target.value ? parseInt(e.target.value) : null;
                    this.setFocusedLevel(level);
                });

                // Status filter buttons
                document.querySelectorAll('.status-filter').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const status = btn.dataset.status;
                        this.setStatusFilter(status);
                    });
                });

                // Show all levels button
                document.getElementById('showAllTiers')?.addEventListener('click', () => {
                    document.getElementById('levelSelector').value = '';
                    this.setFocusedLevel(null);
                });

                // Back navigation button
                document.getElementById('backNavBtn')?.addEventListener('click', () => {
                    this.navigateBack();
                });

                // Retry button
                document.getElementById('retryButton')?.addEventListener('click', () => {
                    this.loadReferralTree();
                });
            }

            async loadReferralTree() {
                try {
                    this.setLoading(true);
                    this.setError(null);

                    const response = await fetch('/referrals/tree-data');
                    const result = await response.json();

                    if (result.success) {
                        this.originalTreeData = result.data || {};
                        console.log('Loaded tree data:', this.originalTreeData);
                        
                        // Initialize navigation to root user
                        const rootUser = Object.values(this.originalTreeData).find(user => user && user.isRoot);
                        if (rootUser) {
                            this.currentViewUserId = rootUser.id;
                            this.navigationStack = []; // Empty stack = at root
                        }
                        
                        this.applyFilters();
                    } else {
                        throw new Error(result.message || 'Failed to fetch referral tree');
                    }
                } catch (err) {
                    console.error('Error loading referral tree:', err);
                    this.setError(err.message);
                } finally {
                    this.setLoading(false);
                }
            }

            // Navigate into a user's children
            navigateTo(userId) {
                const user = this.originalTreeData[`user_${userId}`];
                if (!user) return;
                
                // Prevent duplicate navigation to same user
                if (this.currentViewUserId === userId) return;
                
                // Check if user has children
                if (!user.children || user.children.length === 0) {
                    this.showToast('This user has no referrals to view', 'info');
                    return;
                }
                
                // Push current user to stack before navigating
                const currentUser = this.originalTreeData[`user_${this.currentViewUserId}`];
                this.navigationStack.push({ 
                    id: this.currentViewUserId, 
                    name: currentUser ? (currentUser.name || currentUser.username || 'You') : 'You'
                });
                
                this.currentViewUserId = userId;
                this.renderTree();
                this.updateBackNav();
            }

            // Navigate back one level
            navigateBack() {
                if (this.navigationStack.length === 0) return;
                
                const previous = this.navigationStack.pop();
                this.currentViewUserId = previous.id;
                this.renderTree();
                this.updateBackNav();
            }

            // Update the back navigation header
            updateBackNav() {
                const header = document.getElementById('backNavHeader');
                const backText = document.getElementById('backNavText');
                
                if (this.navigationStack.length > 0) {
                    header?.classList.remove('d-none');
                    if (backText) backText.textContent = 'Back';
                } else {
                    header?.classList.add('d-none');
                }
            }

            setLoading(loading) {
                this.loading = loading;
                document.getElementById('loadingState').classList.toggle('d-none', !loading);
                document.getElementById('mainContent').classList.toggle('d-none', loading || this.error);
            }

            setError(error) {
                this.error = error;
                if (error) {
                    document.getElementById('errorMessage').textContent = error;
                    document.getElementById('errorState').classList.remove('d-none');
                } else {
                    document.getElementById('errorState').classList.add('d-none');
                }
                document.getElementById('mainContent').classList.toggle('d-none', !!error || this.loading);
            }

            setFocusedLevel(level) {
                console.log('Setting focused level to:', level);
                this.focusedTier = level;
                this.expandedNodes.clear();

                // Update title and button
                const titleText = level ? (level === 1 ? 'Level 1 (L1)' : `L1-L${level}`) : 'Referral Tree';
                const statusText = this.statusFilter !== 'all' ? ` - ${this.statusFilter} only` : '';
                document.getElementById('treeTitleText').textContent = titleText + statusText;
                document.getElementById('showAllTiers').classList.toggle('d-none', !level);

                this.applyFilters();
                this.updateInstructions();
            }

            setStatusFilter(status) {
                console.log('Setting status filter to:', status);
                this.statusFilter = status;

                // Update UI
                document.querySelectorAll('.status-filter').forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.status === status);
                });

                this.applyFilters();
                this.updateInstructions();
            }

            applyFilters() {
                console.log('Applying filters - Focused Tier:', this.focusedTier, 'Status Filter:', this.statusFilter);
                this.filteredTreeData = this.filterTreeData();
                console.log('Filtered data result:', this.filteredTreeData);
                this.renderTree();
                this.updateStats();
            }

            filterTreeData() {
                const filtered = {};

                // Always include root user
                const rootUser = Object.values(this.originalTreeData).find(user => user && user.isRoot);
                if (!rootUser) {
                    console.warn('No root user found');
                    return filtered;
                }

                // If no specific tier is focused, use simple filtering
                if (!this.focusedTier) {
                    return this.filterByStatusOnly();
                }

                // NEW LOGIC: Only show L1 branches that have depth >= selected level
                console.log(`Filtering branches with depth >= ${this.focusedTier}`);

                // Step 1: Get all L1 (direct) referrals
                const l1Referrals = (rootUser.children || []).map(childId => 
                    this.originalTreeData[`user_${childId}`]
                ).filter(u => u);

                console.log(`Found ${l1Referrals.length} L1 referrals`);

                // Step 2: Calculate max depth for each L1 branch
                const qualifyingL1Ids = [];
                l1Referrals.forEach(l1User => {
                    const maxDepth = this.calculateBranchMaxDepth(l1User.id);
                    console.log(`L1 user ${l1User.name} has max depth: ${maxDepth}`);
                    if (maxDepth >= this.focusedTier) {
                        qualifyingL1Ids.push(l1User.id);
                    }
                });

                console.log(`${qualifyingL1Ids.length} L1 branches qualify with depth >= ${this.focusedTier}`);

                if (qualifyingL1Ids.length === 0) {
                    // No qualifying branches, only include root with no children
                    filtered[`user_${rootUser.id}`] = { ...rootUser, children: [] };
                    return filtered;
                }

                // Step 3: Include root with only qualifying L1 children
                filtered[`user_${rootUser.id}`] = { 
                    ...rootUser, 
                    children: qualifyingL1Ids 
                };

                // Step 4: For each qualifying branch, include users up to the selected level
                qualifyingL1Ids.forEach(l1Id => {
                    this.collectBranchUsers(l1Id, this.focusedTier, filtered);
                });

                // Step 5: Apply status filter
                if (this.statusFilter !== 'all') {
                    this.applyStatusFilterToTree(filtered, rootUser.id);
                }

                return filtered;
            }

            // Calculate the maximum depth of a branch starting from a user
            calculateBranchMaxDepth(userId) {
                const user = this.originalTreeData[`user_${userId}`];
                if (!user) return 0;

                const userLevel = parseInt(user.tier) || 1;
                
                if (!user.children || user.children.length === 0) {
                    return userLevel;
                }

                let maxChildDepth = userLevel;
                user.children.forEach(childId => {
                    const childDepth = this.calculateBranchMaxDepth(childId);
                    if (childDepth > maxChildDepth) {
                        maxChildDepth = childDepth;
                    }
                });

                return maxChildDepth;
            }

            // Collect all users in a branch up to maxLevel
            collectBranchUsers(userId, maxLevel, filtered) {
                const user = this.originalTreeData[`user_${userId}`];
                if (!user) return;

                const userLevel = parseInt(user.tier) || 1;
                if (userLevel > maxLevel) return;

                // Add user to filtered with filtered children
                const filteredChildren = (user.children || []).filter(childId => {
                    const child = this.originalTreeData[`user_${childId}`];
                    return child && parseInt(child.tier) <= maxLevel;
                });

                filtered[`user_${userId}`] = { 
                    ...user, 
                    children: filteredChildren 
                };

                // Recursively collect children
                filteredChildren.forEach(childId => {
                    this.collectBranchUsers(childId, maxLevel, filtered);
                });
            }

            // Apply status filter to already filtered tree
            applyStatusFilterToTree(filtered, rootId) {
                const toRemove = [];

                Object.entries(filtered).forEach(([key, user]) => {
                    if (user.id === rootId) return; // Never remove root

                    const matchesStatus = this.statusFilter === 'all' ||
                        (this.statusFilter === 'active' && user.status === 'active') ||
                        (this.statusFilter === 'inactive' && user.status !== 'active');

                    if (!matchesStatus) {
                        toRemove.push(key);
                    }
                });

                // Remove non-matching users
                toRemove.forEach(key => delete filtered[key]);

                // Clean up children arrays
                Object.values(filtered).forEach(user => {
                    if (user.children && user.children.length > 0) {
                        user.children = user.children.filter(childId => filtered[`user_${childId}`]);
                    }
                });
            }

            // Simple status-only filtering (when no level is focused)
            filterByStatusOnly() {
                const filtered = {};

                // Always include root user
                const rootUser = Object.values(this.originalTreeData).find(user => user && user.isRoot);
                if (rootUser) {
                    filtered[`user_${rootUser.id}`] = { ...rootUser, children: [...(rootUser.children || [])] };
                }

                // Include all users that match status filter (up to level 10)
                Object.entries(this.originalTreeData).forEach(([key, user]) => {
                    if (!user || user.isRoot) return;

                    const userLevel = parseInt(user.tier);
                    if (userLevel > 10) return; // Limit to level 10

                    // Apply status filter
                    if (this.statusFilter === 'all') {
                        filtered[key] = { ...user, children: [...(user.children || [])] };
                    }
                    else if (this.statusFilter === 'active' && user.status === 'active') {
                        filtered[key] = { ...user, children: [...(user.children || [])] };
                    }
                    else if (this.statusFilter === 'inactive' && user.status !== 'active') {
                        filtered[key] = { ...user, children: [...(user.children || [])] };
                    }
                });

                // Clean up children arrays
                Object.values(filtered).forEach(user => {
                    if (user.children && user.children.length > 0) {
                        user.children = user.children.filter(childId => filtered[`user_${childId}`]);
                    }
                });

                return filtered;
            }

            // Trace ancestry from a user back to root
            traceAncestry(userId) {
                const ancestry = [];
                let currentUserId = userId;

                // Add the target user itself
                ancestry.push(currentUserId);

                // Trace back to root
                while (currentUserId) {
                    const currentUser = this.originalTreeData[`user_${currentUserId}`];
                    if (!currentUser || currentUser.isRoot) {
                        if (currentUser && currentUser.isRoot) {
                            ancestry.push(currentUserId); // Include root
                        }
                        break;
                    }

                    // Find parent (sponsor)
                    let parentId = null;
                    Object.values(this.originalTreeData).forEach(user => {
                        if (user && user.children && user.children.includes(currentUserId)) {
                            parentId = user.id;
                        }
                    });

                    if (parentId) {
                        ancestry.push(parentId);
                        currentUserId = parentId;
                    } else {
                        break;
                    }
                }

                console.log(`Ancestry for user ${userId}:`, ancestry);
                return ancestry;
            }

            calculateStats() {
                const stats = {
                    totals: { total: 0, active: 0 },
                    byLevel: {}
                };

                // Initialize stats for all 10 levels
                for (let i = 1; i <= 10; i++) {
                    stats.byLevel[i] = { total: 0, active: 0 };
                }

                // Calculate from original data, not filtered
                Object.values(this.originalTreeData).forEach(user => {
                    if (user && !user.isRoot) {
                        const level = parseInt(user.tier);
                        if (level >= 1 && level <= 10) {
                            stats.byLevel[level].total++;
                            stats.totals.total++;
                            if (user.status === 'active') {
                                stats.byLevel[level].active++;
                                stats.totals.active++;
                            }
                        }
                    }
                });

                return stats;
            }

            updateStats() {
                const stats = this.calculateStats();

                // Update totals display
                const totalMembersElement = document.getElementById('totalMembers');
                const totalActiveElement = document.getElementById('totalActive');
                const totalInactiveElement = document.getElementById('totalInactive');

                if (totalMembersElement) totalMembersElement.textContent = stats.totals.total;
                if (totalActiveElement) totalActiveElement.textContent = stats.totals.active;
                if (totalInactiveElement) totalInactiveElement.textContent = stats.totals.total - stats.totals.active;
            }

            updateInstructions() {
                const status = this.statusFilter;
                let instructionText;

                if (status !== 'all') {
                    instructionText = `Showing ${status} referrals only. Click on a user to view their referrals.`;
                } else {
                    instructionText = 'Use Level dropdown to filter • Click on a user to drill into their referrals • Use Back button to return.';
                }

                const instructionElement = document.getElementById('instructionText');
                if (instructionElement) instructionElement.textContent = instructionText;
            }

            renderTree() {
                const container = document.getElementById('treeContainer');
                if (!container) return;

                // Use filtered data for level filtering
                const dataSource = this.focusedTier ? this.filteredTreeData : this.originalTreeData;

                // Get the current view user
                let currentUser = dataSource[`user_${this.currentViewUserId}`];
                
                if (!currentUser) {
                    // Fallback to root user from filtered data
                    const rootUser = Object.values(dataSource).find(user => user && user.isRoot);
                    if (rootUser) {
                        this.currentViewUserId = rootUser.id;
                        this.navigationStack = [];
                        currentUser = rootUser;
                    } else {
                        container.innerHTML = `<div class="text-center py-4 text-muted">${this.getFilterMessage()}</div>`;
                        return;
                    }
                }

                // Update back navigation
                this.updateBackNav();

                // Get direct children of current user from filtered data
                const children = currentUser.children || [];
                
                // Filter children to only include those in filtered data
                const filteredChildren = children.filter(childId => dataSource[`user_${childId}`]);
                
                if (filteredChildren.length === 0) {
                    container.innerHTML = `
                        <div class="tree-content">
                            <div class="text-center py-4 text-muted">
                                <iconify-icon icon="iconamoon:hierarchy-duotone" class="fs-24 mb-2 d-block"></iconify-icon>
                                <p class="mb-0 fs-14">${this.focusedTier ? this.getFilterMessage() : 'No referrals at this level'}</p>
                            </div>
                        </div>
                    `;
                    this.updateDisplayedCount(0);
                    return;
                }

                // Render only direct children (flat list, full width)
                let html = '<div class="tree-content">';
                let visibleCount = 0;
                
                filteredChildren.forEach(childId => {
                    const child = dataSource[`user_${childId}`];
                    if (child) {
                        // Apply status filter (additional safety check)
                        if (this.statusFilter === 'active' && child.status !== 'active') return;
                        if (this.statusFilter === 'inactive' && child.status === 'active') return;
                        
                        html += this.renderChildCard(child);
                        visibleCount++;
                    }
                });
                
                html += '</div>';
                container.innerHTML = html;
                
                // Update displayed user count
                this.updateDisplayedCount(visibleCount);
            }
            
            updateDisplayedCount(count) {
                const displayedCountElement = document.getElementById('displayedUserCount');
                if (displayedCountElement) {
                    displayedCountElement.textContent = `Showing ${count} user${count !== 1 ? 's' : ''}`;
                }
            }

            // Render a single child row (simple: name + level badge + arrow)
            renderChildCard(user) {
                // Check if user has children in the filtered data
                const dataSource = this.focusedTier ? this.filteredTreeData : this.originalTreeData;
                const filteredChildrenCount = (user.children || []).filter(childId => dataSource[`user_${childId}`]).length;
                const hasChildren = filteredChildrenCount > 0;
                
                const levelBadge = `<span class="badge bg-${this.getLevelColor(user.tier)}-subtle text-${this.getLevelColor(user.tier)} fs-12">L${user.tier}</span>`;
                const statusDot = user.status === 'active' 
                    ? '<span class="status-dot bg-success"></span>' 
                    : '<span class="status-dot bg-danger"></span>';

                return `
                    <div class="referral-row d-flex align-items-center justify-content-between p-2 border-bottom ${hasChildren ? 'cursor-pointer' : ''}"
                         ${hasChildren ? `onclick="window.treeManager.navigateTo(${user.id})"` : ''}>
                        <div class="d-flex align-items-center min-w-0">
                            ${statusDot}
                            <span class="text-truncate fw-medium ms-2">${this.escapeHtml(user.name)}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            ${levelBadge}
                            <button class="btn tree-info-btn" 
                                    onclick="event.stopPropagation(); window.treeManager.showUserDetails(${user.id})" 
                                    title="View details">
                                <iconify-icon icon="iconamoon:information-circle-duotone"></iconify-icon>
                            </button>
                            ${hasChildren ? '<iconify-icon icon="iconamoon:arrow-right-2-duotone" class="text-muted fs-18"></iconify-icon>' : ''}
                        </div>
                    </div>
                `;
            }

            getFilterMessage() {
                if (this.focusedTier && this.statusFilter !== 'all') {
                    return `No ${this.statusFilter} members found at Level ${this.focusedTier} (L${this.focusedTier})`;
                } else if (this.focusedTier) {
                    return `No members found at Level ${this.focusedTier} (L${this.focusedTier})`;
                } else if (this.statusFilter !== 'all') {
                    return `No ${this.statusFilter} members found`;
                }
                return 'No members found';
            }

            renderUser(user, depth = 0) {
                if (!user) return '';

                const hasChildren = user.children && user.children.length > 0;
                const isExpanded = this.expandedNodes.has(user.id);
                const isRoot = user.isRoot;

                const statusIcon = user.status === 'active'
                    ? '<iconify-icon icon="iconamoon:profile-duotone" class="text-success"></iconify-icon>'
                    : '<iconify-icon icon="iconamoon:profile-duotone" class="text-danger"></iconify-icon>';

                const userIcon = isRoot
                    ? '<iconify-icon icon="iconamoon:profile-duotone" class="text-primary"></iconify-icon>'
                    : statusIcon;

                const levelBadge = !isRoot
                    ? `<span class="badge bg-${this.getLevelColor(user.tier)}-subtle text-${this.getLevelColor(user.tier)} tree-level-badge">L${user.tier}</span>`
                    : '';

                const childrenCount = hasChildren ? user.children.length : 0;
                const activeChildren = hasChildren ?
                    user.children.filter(childId => {
                        const child = this.filteredTreeData[`user_${childId}`];
                        return child && child.status === 'active';
                    }).length : 0;

                const expandIcon = hasChildren
                    ? `<iconify-icon icon="material-symbols-light:keyboard-double-arrow-down-rounded" class="tree-expand-icon"></iconify-icon>`
                    : '<iconify-icon icon="material-symbols:circle" class="tree-leaf-icon"></iconify-icon>';

                // Responsive margin calculation - supports up to 10 levels
                const marginClass = `tree-depth-${Math.min(depth, 10)}`;

                // Mobile-responsive tree node layout with reorganized mobile structure
                let html = `
                    <div class="tree-node ${marginClass}" data-user-id="${user.id}" data-depth="${depth}">
                        <div class="tree-node-content ${isRoot ? 'tree-node-root' : ''}" 
                             style="cursor: ${hasChildren ? 'pointer' : 'default'};" 
                             ${hasChildren ? `onclick="window.treeManager.toggleNode(${user.id})"` : ''}>

                            <div class="d-flex align-items-center w-100">
                                <!-- User Icon Column (Mobile: just icon, Desktop: expand + icon) -->
                                <div class="tree-icons-container me-1 me-sm-2">
                                    <!-- Mobile: User Icon Only -->
                                    <div class="d-sm-none tree-user-icon-mobile">
                                        ${userIcon}
                                    </div>
                                    
                                    <!-- Desktop: Expand + User Icon -->
                                    <div class="d-none d-sm-flex align-items-center gap-2 tree-icons-desktop">
                                        <div class="tree-expand-container">
                                            ${expandIcon}
                                        </div>
                                        <div class="tree-user-icon">
                                            ${userIcon}
                                        </div>
                                    </div>
                                </div>

                                <!-- User Info - Takes Most Available Space -->
                                <div class="flex-grow-1 min-w-0 me-1 me-sm-2">
                                    <!-- Mobile Layout: Name + Level Badge, Username below -->
                                    <div class="d-sm-none tree-user-info-mobile">
                                        <div class="d-flex align-items-center gap-1">
                                            <div class="tree-user-name" title="${this.escapeHtml(user.name)}">
                                                ${this.escapeHtml(user.name)}
                                            </div>
                                            ${levelBadge}
                                        </div>
                                        <div class="tree-user-username" title="@${this.escapeHtml(user.username)}">
                                            @${this.escapeHtml(user.username)}
                                        </div>
                                    </div>
                                    
                                    <!-- Desktop Layout: Name + Badge, then Email + Username -->
                                    <div class="d-none d-sm-block tree-user-info-desktop">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="tree-user-name" title="${this.escapeHtml(user.name)}">
                                                ${this.escapeHtml(user.name)}
                                            </span>
                                            ${levelBadge}
                                        </div>
                                        <div class="tree-user-details">
                                            <div class="tree-user-email d-none d-md-block" title="${this.escapeHtml(user.email)}">
                                                ${this.escapeHtml(user.email)}
                                            </div>
                                            <div class="tree-user-username" title="@${this.escapeHtml(user.username)}">
                                                @${this.escapeHtml(user.username)}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Info Button + Arrow (visible on all screens) -->
                                <div class="tree-actions-column">
                                    <!-- Stats (Desktop only) -->
                                    <div class="d-none d-sm-block tree-stats text-center mb-1">
                                        ${isRoot ? `
                                            <div class="d-md-none">
                                                <small class="text-success fw-bold">${Object.values(this.filteredTreeData).filter(u => u && !u.isRoot && u.status === 'active').length}</small>
                                                <small class="text-muted">/${Object.keys(this.filteredTreeData).length - 1}</small>
                                            </div>
                                            <div class="d-none d-md-block">
                                                <small class="text-muted d-block">Active: ${Object.values(this.filteredTreeData).filter(u => u && !u.isRoot && u.status === 'active').length}</small>
                                                <small class="text-muted d-block">Total: ${Object.keys(this.filteredTreeData).length - 1}</small>
                                            </div>
                                        ` : hasChildren ? `
                                            <small class="text-success fw-bold">${activeChildren}</small>
                                            <small class="text-muted">/${childrenCount}</small>
                                        ` : ''}
                                    </div>
                                    
                                    <!-- Info Button + Arrow Row -->
                                    <div class="d-flex align-items-center gap-1">
                                        <button class="btn tree-info-btn" 
                                                onclick="event.stopPropagation(); window.treeManager.showUserDetails(${user.id})" 
                                                title="View details">
                                            <iconify-icon icon="iconamoon:information-circle-duotone"></iconify-icon>
                                        </button>
                                        ${hasChildren ? `
                                            <div class="tree-expand-arrow">
                                                <iconify-icon icon="material-symbols:chevron-right-rounded" class="tree-arrow-icon"></iconify-icon>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                `;

                // Render children if expanded
                if (isExpanded && hasChildren) {
                    html += '<div class="tree-children">';
                    user.children.forEach(childId => {
                        const child = this.filteredTreeData[`user_${childId}`];
                        if (child) {
                            html += this.renderUser(child, depth + 1);
                        }
                    });
                    html += '</div>';
                }

                html += '</div>';
                return html;
            }

            toggleNode(nodeId) {
                if (this.expandedNodes.has(nodeId)) {
                    this.expandedNodes.delete(nodeId);
                } else {
                    this.expandedNodes.add(nodeId);
                }
                this.renderTree();
            }

            showUserDetails(userId) {
                const user = this.originalTreeData[`user_${userId}`] || this.filteredTreeData[`user_${userId}`];
                if (!user) return;

                // Check if this is a T-1 user (tier 1 and not root)
                const isT1User = !user.isRoot && parseInt(user.tier) === 1;

                // For T-1 users, show phone; for others, show email
                const contactLabel = isT1User ? 'Phone' : 'Email';
                const contactValue = isT1User ? user.phone : user.email;
                const contactDisplay = contactValue ? this.escapeHtml(contactValue) : '<span class="text-muted">Not provided</span>';

                // Handle level - extract numeric value if it's formatted, otherwise use as-is
                let userLevel = 0;
                if (user.level !== undefined && user.level !== null) {
                    if (typeof user.level === 'string') {
                        const match = user.level.match(/\d+/);
                        userLevel = match ? parseInt(match[0]) : 0;
                    } else {
                        userLevel = parseInt(user.level) || 0;
                    }
                }

                const levelDisplay = `TL - ${userLevel}`;
                const levelBadgeClass = this.getLevelBadgeClass(userLevel);

                // Get active investments count
                const activeInvestments = user.active_investments !== undefined ? user.active_investments : 0;

                const modalContent = `
            <div class="modal fade" id="userDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <iconify-icon icon="${user.isRoot ? 'iconamoon:profile-duotone' : user.status === 'active' ? 'iconamoon:profile-duotone' : 'iconamoon:profile-duotone'}" class="me-2"></iconify-icon>
                                ${this.escapeHtml(user.name)}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <strong class="fs-14">Status:</strong>
                                    <div class="text-${user.status === 'active' ? 'success' : 'danger'} fs-14">${user.status === 'active' ? 'Active Member' : 'Inactive Member'}</div>
                                </div>
                                <div class="col-6">
                                    <strong class="fs-14">Username:</strong>
                                    <div class="fs-14">@${this.escapeHtml(user.username)}</div>
                                </div>
                                <div class="col-6">
                                    <strong class="fs-14">${contactLabel}:</strong>
                                    <div class="fs-14">${contactDisplay}</div>
                                </div>
                                <div class="col-6">
                                    <strong class="fs-14">Joined:</strong>
                                    <div class="fs-14">${new Date(user.created_at).toLocaleDateString()}</div>
                                </div>
                                <div class="col-6">
                                    <strong class="fs-14">Direct Referrals:</strong>
                                    <div class="text-primary fw-medium fs-14">${user.children ? user.children.length : 0}</div>
                                </div>
                                <div class="col-6">
                                    <strong class="fs-14">Total Invested:</strong>
                                    <div class="text-success fw-medium fs-14">$${this.formatNumber(user.deposits)}</div>
                                </div>
                                <div class="col-6">
                                    <strong class="fs-14">Active Investments:</strong>
                                    <div class="text-info fw-medium fs-14">${activeInvestments}</div>
                                </div>
                                ${user.sponsorName ? `
                                <div class="col-6">
                                    <strong class="fs-14">Sponsor:</strong>
                                    <div class="fs-14">${this.escapeHtml(user.sponsorName)}</div>
                                </div>
                                ` : ''}
                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

                // Remove existing modal
                const existingModal = document.getElementById('userDetailsModal');
                if (existingModal) existingModal.remove();

                // Add and show new modal
                document.body.insertAdjacentHTML('beforeend', modalContent);
                const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
                modal.show();

                // Clean up on hide
                document.getElementById('userDetailsModal').addEventListener('hidden.bs.modal', function () {
                    this.remove();
                });
            }

            getLevelBadgeClass(level) {
                const levelInt = parseInt(level) || 0;

                if (levelInt === 0) return 'bg-secondary';
                if (levelInt <= 2) return 'bg-primary';
                if (levelInt <= 4) return 'bg-success';
                if (levelInt <= 6) return 'bg-warning';
                if (levelInt <= 8) return 'bg-danger';
                return 'bg-dark'; // For very high levels
            }

            // Also update your formatNumber method if it doesn't exist
            formatNumber(number) {
                if (!number) return '0.00';
                return parseFloat(number).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }
            getLevelColor(level) {
                const levelNum = parseInt(level) || 1;
                const colors = { 
                    1: 'primary', 2: 'success', 3: 'info', 4: 'warning', 5: 'danger',
                    6: 'primary', 7: 'success', 8: 'info', 9: 'warning', 10: 'danger'
                };
                return colors[levelNum] || 'secondary';
            }

            setStickyUser(user) {
                this.stickyUser = user;
                const stickyHeader = document.getElementById('stickyHeader');

                if (user && stickyHeader) {
                    document.getElementById('stickyUserName').textContent = `Viewing: ${user.name}`;
                    const levelBadgeEl = document.getElementById('stickyUserTier');

                    if (user.isRoot) {
                        levelBadgeEl.style.display = 'none';
                    } else {
                        levelBadgeEl.textContent = `L${user.tier}`;
                        levelBadgeEl.className = `badge bg-${this.getLevelColor(user.tier)}-subtle text-${this.getLevelColor(user.tier)} fs-11`;
                        levelBadgeEl.style.display = 'inline-block';
                    }

                    const icon = user.isRoot ? 'iconamoon:profile-duotone' :
                        user.status === 'active' ? 'iconamoon:profile-duotone' :
                            'iconamoon:profile-duotone';

                    document.getElementById('stickyUserIcon').setAttribute('icon', icon);
                    stickyHeader.classList.remove('d-none');
                } else if (stickyHeader) {
                    stickyHeader.classList.add('d-none');
                }
            }

            escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            formatNumber(number) {
                if (!number) return '0.00';
                return parseFloat(number).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            showToast(message, type = 'info') {
                const alertClass = type === 'success' ? 'alert-success' :
                    type === 'error' ? 'alert-danger' : 'alert-info';

                const toast = document.createElement('div');
                toast.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                toast.innerHTML = `
                                ${this.escapeHtml(message)}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            `;

                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 5000);
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', function () {
            window.treeManager = new ReferralTreeManager();
        });

        // Toggle transaction details function for mobile
        function toggleDetails(transactionId) {
            const detailsElement = document.getElementById(`details-${transactionId}`);
            const chevronElement = document.getElementById(`chevron-${transactionId}`);
            
            if (detailsElement.classList.contains('show')) {
                detailsElement.classList.remove('show');
                chevronElement.setAttribute('icon', 'iconamoon:eye-duotone');
            } else {
                detailsElement.classList.add('show');
                chevronElement.setAttribute('icon', 'iconamoon:eye-off-duotone');
            }
        }

        // Copy text function for mobile
        function copyText(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showAlert('Copied to clipboard!', 'success');
                });
            } else {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showAlert('Copied to clipboard!', 'success');
            }
        }
    </script>

    <style>
        /* Status Dot */
        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        /* Referral Row Styles */
        .referral-row {
            transition: background-color 0.15s ease;
        }

        .referral-row.cursor-pointer:hover {
            background-color: #f8f9fa;
        }

        .referral-row:last-child {
            border-bottom: none !important;
        }

        /* Tree Content Container */
        .tree-content {
            width: 100%;
        }

        /* Legacy Tree Styles (kept for compatibility) */
        .tree-node {
            margin-bottom: 0.5rem;
        }

        .tree-node-content {
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            min-height: 3rem;
            padding: 0.5rem;
        }

        .tree-node-content:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .tree-node-root {
            background-color: #e7f3ff !important;
            border-color: #007bff !important;
            border-width: 2px !important;
        }

        /* Tree Icons Container - Mobile vs Desktop Layouts */
        .tree-icons-container {
            flex-shrink: 0;
        }

        /* Mobile: User Icon Only */
        .tree-user-icon-mobile {
            width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        /* Actions Column (Info + Arrow) - Works on all screen sizes */
        .tree-actions-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-shrink: 0;
            min-width: 3rem;
        }

        .tree-expand-arrow {
            width: 1.75rem;
            height: 1.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .tree-arrow-icon {
            font-size: 1.5rem;
            transition: transform 0.2s ease;
        }

        .tree-node-content:hover .tree-arrow-icon {
            color: #007bff;
        }

        @media (max-width: 575.98px) {
            .tree-actions-column {
                min-width: 2.5rem;
            }
            
            .tree-arrow-icon {
                font-size: 1.25rem;
            }
        }

        /* Desktop: Horizontal Icon Layout */
        .tree-icons-desktop {
            /* Keep existing desktop styles */
        }

        .tree-expand-container {
            width: 1.25rem;
            height: 1.25rem;
            min-width: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
        }

        .tree-expand-icon {
            font-size: 0.625rem;
            color: #6c757d;
        }

        .tree-leaf-icon {
            font-size: 0.375rem;
            color: #adb5bd;
        }

        .tree-user-icon {
            width: 1.25rem;
            min-width: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
        }

        @media (min-width: 576px) {
            .tree-expand-container {
                width: 1.25rem;
                height: 1.25rem;
                min-width: 1.25rem;
            }
            
            .tree-expand-icon {
                font-size: 0.75rem;
            }
            
            .tree-user-icon {
                width: 1.5rem;
                min-width: 1.5rem;
                font-size: 1rem;
            }
            
            .tree-node-content {
                padding: 0.75rem;
                min-height: 3.5rem;
            }
        }

        @media (min-width: 768px) {
            .tree-user-icon {
                width: 2rem;
                min-width: 2rem;
                font-size: 1.125rem;
            }
            
            .tree-node-content {
                padding: 1rem;
                min-height: 4rem;
            }
        }

        /* Tree User Information - Mobile vs Desktop Layouts */
        
        /* Mobile Layout: Name above, Username below */
        .tree-user-info-mobile {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        .tree-user-info-mobile .tree-user-name {
            font-weight: 600;
            color: #212529;
            font-size: 0.75rem;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 8rem; /* About 12-15 characters on mobile */
        }

        .tree-user-info-mobile .tree-user-username {
            font-size: 0.65rem;
            color: #6c757d;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 7rem; /* About 10-12 characters on mobile */
        }

        /* Desktop Layout: Original structure */
        .tree-user-info-desktop .tree-user-name {
            font-weight: 600;
            color: #212529;
            font-size: 0.875rem;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 12rem; /* About 18-22 characters */
        }

        .tree-user-info-desktop .tree-user-email {
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 14rem;
        }

        .tree-user-info-desktop .tree-user-username {
            font-size: 0.75rem;
            color: #6c757d;
            line-height: 1.2;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 10rem; /* About 15-18 characters */
        }

        @media (min-width: 768px) {
            .tree-user-info-desktop .tree-user-name {
                max-width: none; /* No truncation on desktop */
                font-size: 0.9rem;
            }
            
            .tree-user-info-desktop .tree-user-email {
                max-width: none;
                font-size: 0.8rem;
            }
            
            .tree-user-info-desktop .tree-user-username {
                max-width: none;
                font-size: 0.8rem;
            }
        }

        /* Tree Action Column - Stats, Info Button, Tier Badge */
        .tree-action-column {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 2rem;
        }

        /* Tree Stats - Positioned above Info Button */
        .tree-stats {
            text-align: center;
            min-width: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .tree-stats small {
            font-size: 0.6rem;
            line-height: 1.1;
            display: inline;
        }

        /* Tree Info Button - NO BACKGROUND, Just Icon */
        .tree-info-button {
            display: flex;
            justify-content: center;
        }

        .tree-info-btn {
            width: 1.75rem;
            height: 1.75rem;
            min-width: 1.75rem;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            background: transparent !important;
            border: none !important;
            color: #007bff;
            transition: color 0.2s ease;
        }

        .tree-info-btn:hover {
            color: #007bff !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
        }

        .tree-info-btn:focus {
            color: #007bff !important;
            background: transparent !important;
            border: none !important;
            box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25) !important;
        }

        .tree-info-btn:active {
            color: #0056b3 !important;
            background: transparent !important;
            border: none !important;
            transform: scale(0.95);
        }

        /* Tier Badge Mobile - Below Info Button */
        .tree-tier-mobile {
            margin-top: 0.25rem;
            display: flex;
            justify-content: center;
        }

        .tree-tier-mobile .tree-tier-badge {
            font-size: 0.5rem !important;
            padding: 0.1rem 0.2rem;
            line-height: 1;
            border-radius: 0.2rem;
        }

        /* Responsive Adjustments */
        @media (min-width: 576px) {
            .tree-action-column {
                min-width: 2.5rem;
            }
            
            .tree-stats {
                min-width: 2rem;
                margin-bottom: 0.5rem;
            }
            
            .tree-stats small {
                font-size: 0.7rem;
            }
            
            .tree-info-btn {
                width: 2rem;
                height: 2rem;
                min-width: 2rem;
                font-size: 1.1rem;
            }

            /* Desktop tier badge styling */
            .tree-tier-badge {
                font-size: 0.6rem !important;
                padding: 0.125rem 0.25rem;
            }
        }

        @media (min-width: 768px) {
            .tree-action-column {
                min-width: 3rem;
            }
            
            .tree-stats {
                min-width: 2.5rem;
            }
            
            .tree-stats small {
                font-size: 0.75rem;
            }
            
            .tree-info-btn {
                width: 2.25rem;
                height: 2.25rem;
                min-width: 2.25rem;
                font-size: 1.25rem;
            }

            /* Desktop tier badge styling */
            .tree-tier-badge {
                font-size: 0.7rem !important;
                padding: 0.15rem 0.3rem;
            }
        }

        /* Ensure minimum usable width on very small screens */
        @media (max-width: 350px) {
            .tree-user-info-mobile .tree-user-name {
                max-width: 6rem; /* Still allow 8-10 characters */
                font-size: 0.7rem;
            }
            
            .tree-user-info-mobile .tree-user-username {
                max-width: 5rem; /* About 7-8 characters */
                font-size: 0.6rem;
            }
            
            .tree-info-btn {
                width: 1.5rem;
                height: 1.5rem;
                min-width: 1.5rem;
                font-size: 0.85rem;
            }

            .tree-tier-mobile .tree-tier-badge {
                font-size: 0.45rem !important;
                padding: 0.05rem 0.15rem;
            }
        }

        /* Tree Children Container */
        .tree-children {
            position: relative;
            margin-top: 0.5rem;
        }

        /* Flexbox Layout - Ensures proper space distribution */
        .tree-node-content .d-flex {
            gap: 0.25rem;
        }

        @media (min-width: 576px) {
            .tree-node-content .d-flex {
                gap: 0.5rem;
            }
        }

        @media (min-width: 768px) {
            .tree-node-content .d-flex {
                gap: 0.75rem;
            }
        }

        /* Ensure user info gets most of the available space */
        .tree-node-content .flex-grow-1 {
            min-width: 0; /* Allow shrinking */
            flex: 1 1 auto; /* Take available space */
        }

        /* Tree User Details Container */
        .tree-user-details {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        @media (min-width: 768px) {
            .tree-user-details {
                gap: 0.2rem;
            }
        }

        /* Hover Effects */
        @media (hover: hover) {
            .tree-node-content:hover .tree-expand-container {
                border-color: #007bff;
                background-color: #f8f9fa;
            }
            
            .tree-node-content:hover .tree-info-btn {
                background-color: #e9ecef;
                border-color: #adb5bd;
            }
        }

        /* Touch Devices */
        @media (hover: none) {
            .tree-node-content:active {
                background-color: #f8f9fa;
                transform: scale(0.98);
            }
        }

        /* Accessibility */
        .tree-info-btn:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }

        /* Ensure usability on very small screens - no horizontal scroll */
        @media (max-width: 350px) {
            .tree-node-content {
                width: 100%;
                overflow: hidden;
            }
            
            .tree-user-name {
                max-width: 5rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .tree-user-username {
                max-width: 4rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            
            .tree-info-btn {
                width: 1.25rem;
                height: 1.25rem;
                min-width: 1.25rem;
                font-size: 0.55rem;
            }
        }

        /* Prevent horizontal overflow on tree container */
        #treeContainer {
            overflow-x: hidden;
            width: 100%;
        }
        
        .tree-content {
            overflow-x: hidden;
            width: 100%;
        }

        /* Level badge styling */
        .tree-level-badge {
            font-size: 0.65rem;
            padding: 0.15rem 0.35rem;
            white-space: nowrap;
        }

        .tree-level-mobile {
            text-align: center;
        }

        /* High Contrast Mode */
        @media (prefers-contrast: high) {
            .tree-node-content {
                border-width: 2px;
            }
            
            .tree-expand-container {
                border-width: 2px;
            }
        }

        /* Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            .tree-node-content {
                transition: none;
            }
            
            .tree-node-content:hover {
                transform: none;
            }
        }

        /* Enhanced hover effects for mobile */
        .hover-bg-light:hover,
        .tree-node:hover {
            background-color: rgba(0, 0, 0, 0.03) !important;
        }

        /* Touch-friendly buttons */
        @media (max-width: 768px) {
            .btn-sm {
                min-height: 32px;
                padding: 0.375rem 0.75rem;
            }
            
            .tier-card {
                transition: all 0.2s ease;
            }
            
            .tier-card:active {
                transform: scale(0.98);
            }
            
            .status-filter {
                transition: all 0.2s ease;
            }
            
            .status-filter:active {
                transform: scale(0.98);
            }
        }

        /* Responsive improvements */
        @media (max-width: 768px) {
            .d-flex.gap-2 {
                flex-direction: column;
                gap: 0.5rem !important;
            }

            .card-title {
                font-size: 1rem;
            }

            h2 {
                font-size: 1.5rem;
            }

            .btn {
                font-size: 0.875rem;
                padding: 0.5rem 1rem;
            }

            /* Tree specific mobile optimizations */
            .mobile-tree .tree-node {
                border-radius: 8px;
            }

            .mobile-tree .d-flex.gap-2 {
                gap: 0.5rem !important;
                flex-direction: row;
            }

            .mobile-tree .flex-grow-1 {
                min-width: 0;
            }
        }

        /* Enhanced card styling */
        .card {
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        /* Balance card special styling */
        .border-primary {
            border: 2px solid #007bff !important;
        }

        /* Stats cards */
        .bg-success-subtle {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .bg-info-subtle {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .bg-primary-subtle {
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-danger-subtle {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }

        /* Table improvements */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        /* Icon sizing */
        .fs-24 {
            font-size: 1.5rem;
        }

        /* Badge improvements */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }

        /* Quick actions styling */
        .d-grid .btn {
            margin-bottom: 0.5rem;
        }

        .d-grid .btn:last-child {
            margin-bottom: 0;
        }

        /* Alert positioning */
        .position-fixed {
            z-index: 1060 !important;
        }

        /* Announcement modal styling */
        .announcement-modal {
            z-index: 9990 !important;
        }
        .announcement-modal .modal-dialog {
            z-index: 9991 !important;
        }
        .announcement-modal .modal-content {
            border-radius: 12px;
            overflow: hidden;
            z-index: 9992 !important;
            position: relative;
        }
        .announcement-modal + .modal-backdrop,
        .modal-backdrop.show {
            z-index: 9980 !important;
        }

        .announcement-modal .modal-header {
            padding: 1.5rem;
            border-bottom: none;
        }

        .announcement-modal .modal-body {
            padding: 1.5rem;
        }

        .announcement-modal .modal-footer {
            padding: 1.5rem;
            border-top: none;
        }

        .announcement-modal .announcement-content {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .announcement-modal .btn-lg {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
        }

        .announcement-modal .modal-title {
            font-weight: 600;
        }

        .announcement-modal .modal-dialog {
            max-width: 500px;
        }

        /* Zoom In Animation for modal (opening) */
        .announcement-modal.fade .modal-dialog {
            transform: scale(0.5);
            opacity: 0;
            transition: transform 0.4s ease-out, opacity 0.3s ease-out;
        }

        .announcement-modal.show .modal-dialog {
            transform: scale(1);
            opacity: 1;
        }
        
        /* Swipe Up Animation for modal (closing) */
        .announcement-modal.fade.hiding .modal-dialog {
            transform: translateY(-100vh) !important;
            opacity: 0;
            transition: transform 0.4s ease-in, opacity 0.3s ease-in !important;
        }

        /* Transaction card styling */
        .transaction-card {
            transition: all 0.2s ease;
        }

        .transaction-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Collapse animation */
        .collapse {
            transition: height 0.35s ease;
        }

        /* Additional mobile optimizations */
        @media (max-width: 576px) {
            .announcement-modal .modal-dialog {
                margin: 1rem;
                max-width: none;
            }

            .announcement-modal .modal-header,
            .announcement-modal .modal-body,
            .announcement-modal .modal-footer {
                padding: 1rem;
            }

            .announcement-modal .announcement-content {
                font-size: 1rem;
            }

            /* Tighter spacing for mobile tree */
            .mobile-tree .tree-node .d-flex {
                min-height: 48px;
                padding: 0.5rem;
            }

            /* Mobile-friendly buttons */
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.775rem;
            }

            /* Compact tier cards on mobile */
            .tier-card .card-body {
                padding: 0.75rem;
            }

            /* Stack buttons differently on very small screens */
            .status-filter {
                font-size: 0.875rem;
                padding: 0.5rem;
            }
        }

        /* Ultra-small screens (< 400px) */
        @media (max-width: 400px) {
            .mobile-tree .text-truncate {
                max-width: 80px;
            }

            .fs-14 {
                font-size: 13px !important;
            }

            .fs-12 {
                font-size: 11px !important;
            }

            .btn {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }

        /* Sticky header improvements */
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        /* Enhanced tree node styling */
        .tree-node .d-flex.hover-bg-light {
            transition: background-color 0.2s ease;
            border-radius: 8px;
        }

        .tree-node:hover .hover-bg-light {
            background-color: rgba(13, 110, 253, 0.05) !important;
        }

        /* Better button styling */
        .btn-light {
            background-color: #f8f9fa;
            border-color: #f8f9fa;
        }

        .btn-light:hover {
            background-color: #e9ecef;
            border-color: #e9ecef;
        }

        /* Improved badge styling */
        .badge.bg-primary-subtle {
            color: #0a58ca !important;
            background-color: rgba(13, 110, 253, 0.1) !important;
        }

        .badge.bg-success-subtle {
            color: #0f5132 !important;
            background-color: rgba(25, 135, 84, 0.1) !important;
        }

        .badge.bg-info-subtle {
            color: #055160 !important;
            background-color: rgba(13, 202, 240, 0.1) !important;
        }

        .badge.bg-warning-subtle {
            color: #664d03 !important;
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .badge.bg-danger-subtle {
            color: #842029 !important;
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
    </style>
@endsection

@section('vite_scripts')
    @vite(['resources/js/pages/dashboard.enhanced.js'])
@endsection