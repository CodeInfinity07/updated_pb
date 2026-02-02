@extends('admin.layouts.vertical', ['title' => 'Impersonation Logs', 'subTitle' => 'View Admin Impersonation History'])

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="mb-1 text-dark">Impersonation Logs</h4>
                            <p class="text-muted mb-0">Track who impersonated whom and when</p>
                        </div>
                        <a href="{{ route('admin.impersonation.index') }}" class="btn btn-outline-primary btn-sm">
                            <iconify-icon icon="iconamoon:eye-duotone" class="me-1"></iconify-icon>
                            View as User
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:history-duotone" class="text-primary mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Total Logs</h6>
                    <h5 class="mb-0 fw-bold" id="statTotalLogs">{{ number_format($statistics['total_logs']) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:calendar-duotone" class="text-success mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Today</h6>
                    <h5 class="mb-0 fw-bold" id="statTodayLogs">{{ number_format($statistics['today_logs']) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:player-play-duotone" class="text-warning mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Active Sessions</h6>
                    <h5 class="mb-0 fw-bold" id="statActiveSessions">{{ number_format($statistics['active_sessions']) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <iconify-icon icon="iconamoon:profile-duotone" class="text-info mb-2" style="font-size: 2rem;"></iconify-icon>
                    <h6 class="text-muted mb-1">Unique Admins</h6>
                    <h5 class="mb-0 fw-bold" id="statUniqueAdmins">{{ number_format($statistics['unique_admins']) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Filters</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Search</label>
                            <input type="text" id="searchInput" class="form-control" placeholder="Admin or user name, email...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Admin</label>
                            <select id="adminFilter" class="form-select">
                                <option value="">All Admins</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}">
                                        {{ $admin->first_name }} {{ $admin->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Date Range</label>
                            <select id="dateRangeFilter" class="form-select" onchange="handleDateRangeChange(this.value)">
                                @foreach($dateRanges as $key => $label)
                                <option value="{{ $key }}" {{ $dateRange == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div class="col-md-3" id="customDateContainer" style="display: none;">
                            <label class="form-label fw-semibold">Custom Dates</label>
                            <input type="text" id="customDateRange" class="form-control" placeholder="Select date range">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="resetFilters()">
                                <iconify-icon icon="iconamoon:close-duotone" class="me-1"></iconify-icon>
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Impersonation Records</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Admin</th>
                                    <th>Impersonated User</th>
                                    <th>Started At</th>
                                    <th>Ended At</th>
                                    <th class="text-center">Duration</th>
                                    <th>IP Address</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                @if($logs->count() > 0)
                                    @foreach($logs as $log)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <div class="fw-semibold">{{ $log->admin->first_name ?? '' }} {{ $log->admin->last_name ?? '' }}</div>
                                                    <small class="text-muted">{{ $log->admin->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    @if($log->impersonatedUser)
                                                    <a href="javascript:void(0)" class="clickable-user fw-semibold" onclick="showUserDetails('{{ $log->impersonatedUser->id }}')">{{ $log->impersonatedUser->first_name ?? '' }} {{ $log->impersonatedUser->last_name ?? '' }}</a>
                                                    @else
                                                    <div class="fw-semibold">Unknown User</div>
                                                    @endif
                                                    <small class="text-muted d-block">{{ $log->impersonatedUser->username ?? 'N/A' }} - {{ $log->impersonatedUser->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $log->started_at->format('M d, Y H:i:s') }}</td>
                                        <td>
                                            @if($log->ended_at)
                                                {{ $log->ended_at->format('M d, Y H:i:s') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary">{{ $log->formatted_duration }}</span>
                                        </td>
                                        <td>
                                            <code>{{ $log->ip_address ?? 'N/A' }}</code>
                                        </td>
                                        <td class="text-center">
                                            @if($log->ended_at)
                                                <span class="badge bg-success">Completed</span>
                                            @else
                                                <span class="badge bg-warning">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <iconify-icon icon="iconamoon:history-duotone" class="text-muted" style="font-size: 4rem;"></iconify-icon>
                                            <h5 class="mt-3 text-muted">No Impersonation Logs</h5>
                                            <p class="text-muted">No impersonation sessions have been recorded yet</p>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="paginationContainer">
                    @if($logs->hasPages())
                    <div class="card-footer bg-white border-top">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted">
                                Showing <span class="fw-semibold">{{ $logs->firstItem() }}</span>
                                to <span class="fw-semibold">{{ $logs->lastItem() }}</span>
                                of <span class="fw-semibold">{{ $logs->total() }}</span> records
                            </div>
                            {{ $logs->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
let currentFilters = {
    search: '',
    admin_id: '',
    date_range: '{{ $dateRange }}',
    start_date: '',
    end_date: '',
    per_page: 20,
    page: 1
};

let customDatePicker;
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    initializeDatePicker();
    initializeSearch();
    initializeAdminFilter();
});

function initializeDatePicker() {
    customDatePicker = flatpickr("#customDateRange", {
        mode: "range",
        dateFormat: "Y-m-d",
        maxDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            if (selectedDates.length === 2) {
                currentFilters.start_date = flatpickr.formatDate(selectedDates[0], "Y-m-d");
                currentFilters.end_date = flatpickr.formatDate(selectedDates[1], "Y-m-d");
                currentFilters.date_range = 'custom';
                currentFilters.page = 1;
                loadHistoryLogs();
            }
        }
    });
}

function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = e.target.value;
            currentFilters.page = 1;
            loadHistoryLogs();
        }, 500);
    });
}

function initializeAdminFilter() {
    document.getElementById('adminFilter').addEventListener('change', function() {
        currentFilters.admin_id = this.value;
        currentFilters.page = 1;
        loadHistoryLogs();
    });
}

function handleDateRangeChange(value) {
    const customContainer = document.getElementById('customDateContainer');
    
    if (value === 'custom') {
        customContainer.style.display = 'block';
        customDatePicker.open();
    } else {
        customContainer.style.display = 'none';
        currentFilters.start_date = '';
        currentFilters.end_date = '';
        currentFilters.date_range = value;
        currentFilters.page = 1;
        loadHistoryLogs();
    }
}

function loadHistoryPage(page) {
    currentFilters.page = page;
    loadHistoryLogs();
}

function loadHistoryLogs() {
    const tableBody = document.getElementById('historyTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    
    const params = new URLSearchParams();
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.admin_id) params.append('admin_id', currentFilters.admin_id);
    if (currentFilters.date_range && currentFilters.date_range !== 'custom') params.append('date_range', currentFilters.date_range);
    if (currentFilters.start_date) params.append('start_date', currentFilters.start_date);
    if (currentFilters.end_date) params.append('end_date', currentFilters.end_date);
    if (currentFilters.per_page) params.append('per_page', currentFilters.per_page);
    if (currentFilters.page) params.append('page', currentFilters.page);
    
    fetch(`{{ route('admin.impersonation.history.filter') }}?${params.toString()}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            tableBody.innerHTML = data.html;
            paginationContainer.innerHTML = data.pagination;
            
            if (data.statistics) {
                document.getElementById('statTotalLogs').textContent = data.statistics.total_logs.toLocaleString();
                document.getElementById('statTodayLogs').textContent = data.statistics.today_logs.toLocaleString();
                document.getElementById('statActiveSessions').textContent = data.statistics.active_sessions.toLocaleString();
                document.getElementById('statUniqueAdmins').textContent = data.statistics.unique_admins.toLocaleString();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function resetFilters() {
    currentFilters = {
        search: '',
        admin_id: '',
        date_range: '7',
        start_date: '',
        end_date: '',
        per_page: 20,
        page: 1
    };
    
    document.getElementById('searchInput').value = '';
    document.getElementById('adminFilter').value = '';
    document.getElementById('dateRangeFilter').value = '7';
    document.getElementById('customDateContainer').style.display = 'none';
    if (customDatePicker) customDatePicker.clear();
    
    loadHistoryLogs();
}
</script>
@endsection
