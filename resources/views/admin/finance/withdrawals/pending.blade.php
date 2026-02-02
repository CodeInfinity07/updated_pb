@extends('admin.layouts.vertical', ['title' => 'Pending Withdrawals', 'subTitle' => 'Finance'])

@section('content')

<div class="row">
    <div class="col-12">
        {{-- Summary Cards --}}
        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-20">
                                    <i class="bx bx-time-five"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Pending</p>
                            <h4 class="mb-0">{{ $summaryData['total_pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-20">
                                    <i class="bx bx-up-arrow-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Total Amount</p>
                            <h4 class="mb-0">${{ number_format($summaryData['total_amount'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-info-subtle text-info rounded-circle fs-20">
                                    <i class="bx bx-calendar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Today's Pending</p>
                            <h4 class="mb-0">{{ $summaryData['today_pending'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="avatar-sm">
                                <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                                    <i class="bx bx-dollar"></i>
                                </div>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <p class="text-muted mb-1">Today's Amount</p>
                            <h4 class="mb-0">${{ number_format($summaryData['today_amount'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            {{-- Header with Filters --}}
            <div class="d-flex card-header justify-content-between align-items-center">
                <h4 class="card-title mb-0">Pending Withdrawals</h4>
                <div class="flex-shrink-0">
                    <div class="d-flex gap-2 flex-wrap">
                        <input type="text" class="form-control form-control-sm" placeholder="Search..." id="searchInput" style="min-width: 200px;">
                        <select class="form-select form-select-sm" onchange="handleDateRangeChange(this.value)" id="predefinedDateRange">
                            @foreach($filterOptions['date_ranges'] as $key => $label)
                            <option value="{{ $key }}" {{ $key == '30' ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                            <option value="custom">Custom Range</option>
                        </select>
                        <input type="text" id="customDateRange" class="form-control form-control-sm" placeholder="Select custom dates" style="min-width: 200px; display: none;">
                        <button type="button" class="btn btn-sm btn-outline-danger" id="clearCustomDateFilter" style="display: none;">
                            <i class="bx bx-x"></i>
                            <span class="d-none d-sm-inline ms-1">Clear</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Bulk Actions Toolbar --}}
            <div class="card-body py-2 bg-light border-bottom" id="bulkActionsToolbar" style="display: none;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted"><strong id="selectedCount">0</strong> withdrawal(s) selected</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success btn-sm" onclick="bulkApprove()">
                            <i class="bx bx-check-circle me-1"></i> Approve Selected
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkReject()">
                            <i class="bx bx-x-circle me-1"></i> Reject Selected
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearSelection()">
                            <i class="bx bx-x me-1"></i> Clear Selection
                        </button>
                    </div>
                </div>
            </div>

            {{-- Withdrawals Table --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0" style="min-width: 950px;">
                        <thead class="bg-light bg-opacity-50 thead-sm">
                            <tr>
                                <th scope="col" style="width: 40px;">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                                    </div>
                                </th>
                                <th scope="col" style="min-width: 180px;">User</th>
                                <th scope="col" style="min-width: 120px;">Transaction ID</th>
                                <th scope="col" style="min-width: 100px;">Amount</th>
                                <th scope="col" style="min-width: 160px;">Crypto Address</th>
                                <th scope="col" style="min-width: 120px;">Requested At</th>
                                <th scope="col" style="min-width: 80px;">Status</th>
                                <th scope="col" style="min-width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="withdrawalsTableBody">
                            @if($pendingWithdrawals->count() > 0)
                                @foreach($pendingWithdrawals as $withdrawal)
                                <tr data-withdrawal-id="{{ $withdrawal->id }}">
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input withdrawal-checkbox" type="checkbox" value="{{ $withdrawal->id }}" onchange="updateBulkSelection()">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm rounded-circle bg-primary me-2">
                                                <span class="avatar-title text-white">{{ $withdrawal->user ? $withdrawal->user->initials : 'U' }}</span>
                                            </div>
                                            <div>
                                                @if($withdrawal->user)
                                                <h6 class="mb-0 text-truncate" style="max-width: 150px;"><a href="javascript:void(0)" class="clickable-user" onclick="showUserDetails('{{ $withdrawal->user->id }}')">{{ $withdrawal->user->full_name }}</a></h6>
                                                @else
                                                <h6 class="mb-0 text-truncate" style="max-width: 150px;">Unknown User</h6>
                                                @endif
                                                <small class="text-muted text-truncate d-block" style="max-width: 150px;">{{ $withdrawal->user ? $withdrawal->user->email : 'Unknown' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="small text-break">{{ Str::limit($withdrawal->transaction_id, 12) }}</code>
                                    </td>
                                    <td>
                                        <strong class="text-danger">-{{ $withdrawal->formatted_amount }}</strong>
                                    </td>
                                    <td>
                                        <code class="small text-break" style="word-break: break-all;">{{ $withdrawal->crypto_address ? Str::limit($withdrawal->crypto_address, 18) : 'N/A' }}</code>
                                    </td>
                                    <td>
                                        {{ $withdrawal->created_at->format('d M, y') }}
                                        <small class="text-muted d-block">{{ $withdrawal->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 flex-nowrap">
                                            <button class="btn btn-sm btn-success" onclick="updateTransactionStatus('{{ $withdrawal->id }}', 'completed')" title="Approve">
                                                <i class="bx bx-check-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="updateTransactionStatus('{{ $withdrawal->id }}', 'failed')" title="Reject">
                                                <i class="bx bx-x-circle"></i>
                                            </button>
                                            <button class="btn btn-sm btn-info" onclick="showDetails('{{ $withdrawal->id }}')" title="View Details">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <button class="btn btn-sm btn-secondary" onclick="showStatusModal('{{ $withdrawal->id }}', 'pending')" title="Change Status">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bx bx-check-circle fs-1 text-success mb-3 d-block"></i>
                                        <h6 class="text-muted">No Pending Withdrawals</h6>
                                        <p class="text-muted mb-0">All withdrawals have been processed!</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination Container --}}
            <div id="withdrawalsPaginationContainer">
                @if($pendingWithdrawals->hasPages())
                <div class="card-footer border-top border-light">
                    <div class="align-items-center justify-content-between row text-center text-sm-start">
                        <div class="col-sm">
                            <div class="text-muted">
                                Showing
                                <span class="fw-semibold text-body">{{ $pendingWithdrawals->firstItem() }}</span>
                                to
                                <span class="fw-semibold text-body">{{ $pendingWithdrawals->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $pendingWithdrawals->total() }}</span>
                                Pending Withdrawals
                            </div>
                        </div>
                        <div class="col-sm-auto mt-3 mt-sm-0">
                            <ul class="pagination pagination-boxed pagination-sm mb-0 justify-content-center">
                                @if ($pendingWithdrawals->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bx bxs-chevron-left"></i></span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $pendingWithdrawals->previousPageUrl() }}"><i class="bx bxs-chevron-left"></i></a>
                                    </li>
                                @endif

                                @php
                                    $currentPage = $pendingWithdrawals->currentPage();
                                    $lastPage = $pendingWithdrawals->lastPage();
                                    $pagesToShow = [];
                                    
                                    if ($lastPage <= 7) {
                                        $pagesToShow = range(1, $lastPage);
                                    } else {
                                        $pagesToShow[] = 1;
                                        if ($currentPage > 4) $pagesToShow[] = '...';
                                        
                                        $start = max(2, $currentPage - 1);
                                        $end = min($lastPage - 1, $currentPage + 1);
                                        
                                        if ($currentPage <= 4) {
                                            $start = 2;
                                            $end = min(6, $lastPage - 1);
                                        }
                                        
                                        if ($currentPage >= $lastPage - 3) {
                                            $start = max(2, $lastPage - 5);
                                            $end = $lastPage - 1;
                                        }
                                        
                                        for ($i = $start; $i <= $end; $i++) {
                                            $pagesToShow[] = $i;
                                        }
                                        
                                        if ($currentPage < $lastPage - 3) $pagesToShow[] = '...';
                                        $pagesToShow[] = $lastPage;
                                    }
                                @endphp

                                @foreach($pagesToShow as $page)
                                    @if($page === '...')
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @elseif($page == $currentPage)
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $pendingWithdrawals->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                @if ($pendingWithdrawals->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $pendingWithdrawals->nextPageUrl() }}"><i class="bx bxs-chevron-right"></i></a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="bx bxs-chevron-right"></i></span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Transaction Status Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Withdrawal Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <input type="hidden" id="transactionId">
                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <div id="transactionIdDisplay" class="form-control-plaintext fw-semibold"></div>
                    </div>
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">New Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="newStatus" required>
                            <option value="">Select Status</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="statusNotes" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Details Modal --}}
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdrawal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
{{-- Flatpickr CSS and JS --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Store current filters
let currentFilters = {
    search: '',
    date_range: '30',
    start_date: '',
    end_date: '',
    per_page: 25,
    page: 1
};

let customDatePicker;
let searchTimeout;

document.addEventListener('DOMContentLoaded', function() {
    initializeDatePicker();
    initializeSearch();
});

// Initialize custom date range picker
function initializeDatePicker() {
    const predefinedSelect = document.getElementById('predefinedDateRange');
    const customInput = document.getElementById('customDateRange');
    const clearBtn = document.getElementById('clearCustomDateFilter');
    
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
                clearBtn.style.display = 'inline-block';
                loadPendingWithdrawals();
            }
        }
    });
    
    clearBtn.addEventListener('click', function() {
        customDatePicker.clear();
        currentFilters.start_date = '';
        currentFilters.end_date = '';
        currentFilters.date_range = '30';
        currentFilters.page = 1;
        this.style.display = 'none';
        customInput.style.display = 'none';
        predefinedSelect.value = '30';
        loadPendingWithdrawals();
    });
}

// Initialize search
function initializeSearch() {
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentFilters.search = e.target.value;
            currentFilters.page = 1;
            loadPendingWithdrawals();
        }, 500);
    });
}

function handleDateRangeChange(value) {
    const customInput = document.getElementById('customDateRange');
    const clearBtn = document.getElementById('clearCustomDateFilter');
    
    if (value === 'custom') {
        customInput.style.display = 'block';
        customDatePicker.open();
    } else {
        customInput.style.display = 'none';
        clearBtn.style.display = 'none';
        currentFilters.start_date = '';
        currentFilters.end_date = '';
        currentFilters.date_range = value;
        currentFilters.page = 1;
        loadPendingWithdrawals();
    }
}

function loadPendingWithdrawalsPage(page) {
    currentFilters.page = page;
    loadPendingWithdrawals();
}

function loadPendingWithdrawals() {
    const tableBody = document.getElementById('withdrawalsTableBody');
    const paginationContainer = document.getElementById('withdrawalsPaginationContainer');
    const tableWrapper = tableBody?.closest('.table-responsive');
    
    if (!tableBody) return;
    
    // Show loading overlay
    let loadingOverlay = document.getElementById('withdrawalsLoadingOverlay');
    
    if (!loadingOverlay) {
        loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'withdrawalsLoadingOverlay';
        loadingOverlay.className = 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        loadingOverlay.style.cssText = 'background: rgba(255, 255, 255, 0.9); z-index: 10; min-height: 400px; border-radius: 8px;';
        loadingOverlay.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading pending withdrawals...</p>
            </div>
        `;
        
        if (tableWrapper) {
            tableWrapper.style.position = 'relative';
            tableWrapper.appendChild(loadingOverlay);
        }
    } else {
        loadingOverlay.classList.remove('d-none');
    }
    
    if (paginationContainer) {
        paginationContainer.style.opacity = '0.5';
    }
    
    // Build query params
    const params = new URLSearchParams();
    if (currentFilters.search) params.append('search', currentFilters.search);
    if (currentFilters.date_range && currentFilters.date_range !== 'custom') params.append('date_range', currentFilters.date_range);
    if (currentFilters.start_date) params.append('start_date', currentFilters.start_date);
    if (currentFilters.end_date) params.append('end_date', currentFilters.end_date);
    if (currentFilters.per_page) params.append('per_page', currentFilters.per_page);
    if (currentFilters.page) params.append('page', currentFilters.page);
    
    // Make AJAX request
    fetch(`{{ route('admin.finance.withdrawals.pending.filter-ajax') }}?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                tableBody.innerHTML = data.html;
                
                if (paginationContainer) {
                    paginationContainer.innerHTML = data.pagination;
                    paginationContainer.style.opacity = '1';
                }
                
                if (loadingOverlay) {
                    loadingOverlay.classList.add('d-none');
                }
                
                if (currentFilters.page > 1) {
                    tableWrapper?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                
                if (currentFilters.page === 1) {
                    showAlert(`Showing ${data.count} of ${data.total} pending withdrawal(s)`, 'success');
                }
            } else {
                if (loadingOverlay) {
                    loadingOverlay.classList.add('d-none');
                }
                showAlert('Failed to load withdrawals', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            if (loadingOverlay) {
                loadingOverlay.classList.add('d-none');
            }
            
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <iconify-icon icon="iconamoon:alert-circle-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                        <h6 class="text-danger">Error Loading Withdrawals</h6>
                        <p class="text-muted mb-0">Please try again.</p>
                    </td>
                </tr>
            `;
            showAlert('Failed to load withdrawals', 'danger');
        });
}

function showDetails(transactionId) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const content = document.getElementById('modalContent');
    
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    fetch(`{{ url('admin/finance/transactions') }}/${transactionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = data.html;
            } else {
                content.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Failed to load withdrawal details</div>';
        });
}

function updateTransactionStatus(transactionId, status) {
    const statusText = status.charAt(0).toUpperCase() + status.slice(1);
    if (confirm(`Are you sure you want to mark this withdrawal as ${statusText}?`)) {
        fetch(`{{ url('admin/finance/transactions') }}/${transactionId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success) {
                loadPendingWithdrawals();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Failed to update withdrawal status', 'danger');
        });
    }
}

function showStatusModal(transactionId, currentStatus) {
    document.getElementById('transactionId').value = transactionId;
    document.getElementById('transactionIdDisplay').textContent = transactionId;
    
    new bootstrap.Modal(document.getElementById('statusModal')).show();
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const transactionId = document.getElementById('transactionId').value;
    const newStatus = document.getElementById('newStatus').value;
    const notes = document.getElementById('statusNotes').value;
    
    fetch(`{{ url('admin/finance/transactions') }}/${transactionId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            status: newStatus,
            notes: notes
        })
    })
    .then(response => response.json())
    .then(data => {
        bootstrap.Modal.getInstance(document.getElementById('statusModal')).hide();
        showAlert(data.message, data.success ? 'success' : 'danger');
        if (data.success) {
            loadPendingWithdrawals();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Failed to update withdrawal status', 'danger');
    });
});

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

document.getElementById('statusModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('statusForm').reset();
});

// Bulk Selection Functions
let selectedWithdrawals = [];
let isProcessingBulk = false;

// Use event delegation for checkbox changes (handles dynamically loaded content)
document.addEventListener('change', function(e) {
    if (e.target.classList.contains('withdrawal-checkbox')) {
        updateBulkSelection();
    }
    if (e.target.id === 'selectAllCheckbox') {
        toggleSelectAll(e.target);
    }
});

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateBulkSelection();
}

function updateBulkSelection() {
    // Don't update selection during bulk processing
    if (isProcessingBulk) return;
    
    const checkboxes = document.querySelectorAll('.withdrawal-checkbox:checked');
    selectedWithdrawals = Array.from(checkboxes).map(cb => cb.value);
    
    const toolbar = document.getElementById('bulkActionsToolbar');
    const countEl = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const allCheckboxes = document.querySelectorAll('.withdrawal-checkbox');
    
    countEl.textContent = selectedWithdrawals.length;
    
    if (selectedWithdrawals.length > 0) {
        toolbar.style.display = 'block';
    } else {
        toolbar.style.display = 'none';
    }
    
    // Update select all checkbox state
    if (allCheckboxes.length > 0 && selectAllCheckbox) {
        selectAllCheckbox.checked = selectedWithdrawals.length === allCheckboxes.length;
        selectAllCheckbox.indeterminate = selectedWithdrawals.length > 0 && selectedWithdrawals.length < allCheckboxes.length;
    }
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.withdrawal-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    selectedWithdrawals = [];
    document.getElementById('bulkActionsToolbar').style.display = 'none';
    document.getElementById('selectedCount').textContent = '0';
}

function bulkApprove() {
    if (selectedWithdrawals.length === 0) {
        Swal.fire('Warning', 'No withdrawals selected', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Approve Selected Withdrawals?',
        html: `You are about to approve <strong>${selectedWithdrawals.length}</strong> withdrawal(s).<br><br>
               <span class="text-danger"><i class="bx bx-error-circle me-1"></i>This will send cryptocurrency to the users' wallets via Plisio.</span>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Approve All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            processBulkAction('completed');
        }
    });
}

function bulkReject() {
    if (selectedWithdrawals.length === 0) {
        Swal.fire('Warning', 'No withdrawals selected', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Reject Selected Withdrawals?',
        html: `You are about to reject <strong>${selectedWithdrawals.length}</strong> withdrawal(s).<br><br>
               The funds will be returned to users' balances.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Reject All',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            processBulkAction('failed');
        }
    });
}

async function processBulkAction(status) {
    const statusText = status === 'completed' ? 'Approving' : 'Rejecting';
    const withdrawalsToProcess = [...selectedWithdrawals]; // Copy array before processing
    
    isProcessingBulk = true;
    
    Swal.fire({
        title: `${statusText} Withdrawals...`,
        html: `Processing <strong>0</strong> of <strong>${withdrawalsToProcess.length}</strong> withdrawals...`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    let successCount = 0;
    let failCount = 0;
    const errors = [];
    
    for (let i = 0; i < withdrawalsToProcess.length; i++) {
        const withdrawalId = withdrawalsToProcess[i];
        
        Swal.update({
            html: `Processing <strong>${i + 1}</strong> of <strong>${withdrawalsToProcess.length}</strong> withdrawals...`
        });
        
        try {
            const response = await fetch(`/admin/finance/transactions/${withdrawalId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: status,
                    notes: `Bulk ${status === 'completed' ? 'approval' : 'rejection'} by admin`
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                successCount++;
            } else {
                failCount++;
                errors.push(`ID ${withdrawalId}: ${data.message || 'Unknown error'}`);
            }
        } catch (error) {
            failCount++;
            errors.push(`ID ${withdrawalId}: ${error.message}`);
        }
    }
    
    isProcessingBulk = false;
    
    // Show results
    if (failCount === 0) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            html: `Successfully ${status === 'completed' ? 'approved' : 'rejected'} <strong>${successCount}</strong> withdrawal(s).`,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        let errorHtml = `<strong>${successCount}</strong> succeeded, <strong>${failCount}</strong> failed.`;
        if (errors.length > 0) {
            errorHtml += `<br><br><small class="text-muted">Errors:<br>${errors.slice(0, 5).join('<br>')}</small>`;
            if (errors.length > 5) {
                errorHtml += `<br><small class="text-muted">...and ${errors.length - 5} more</small>`;
            }
        }
        
        Swal.fire({
            icon: 'warning',
            title: 'Partial Success',
            html: errorHtml
        });
    }
    
    // Clear selection and reload table via AJAX
    selectedWithdrawals = [];
    document.getElementById('bulkActionsToolbar').style.display = 'none';
    document.getElementById('selectedCount').textContent = '0';
    
    // Also reset select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }
    
    // Reload table via AJAX
    if (successCount > 0) {
        setTimeout(() => {
            loadPendingWithdrawals();
        }, 500);
    }
}
</script>

<style>
.table-responsive {
    min-height: 400px;
    position: relative;
}

#withdrawalsLoadingOverlay.d-none {
    display: none !important;
}

#withdrawalsPaginationContainer {
    transition: opacity 0.2s ease;
}

.avatar-sm {
    width: 2rem;
    height: 2rem;
    font-size: 0.8rem;
}

.avatar-title {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

code {
    background-color: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    word-break: break-all;
}
</style>
@endsection