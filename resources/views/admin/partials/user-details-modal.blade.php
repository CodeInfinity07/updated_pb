{{-- User Details Modal - Reusable component for viewing user details across admin panel --}}
<div class="modal fade" id="globalUserDetailsModal" tabindex="-1" aria-labelledby="globalUserDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="globalUserDetailsModalLabel">User Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="globalUserModalContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let globalUserDetailsModal = null;

function initGlobalUserDetailsModal() {
    if (typeof bootstrap === 'undefined') {
        setTimeout(initGlobalUserDetailsModal, 50);
        return;
    }
    const modalEl = document.getElementById('globalUserDetailsModal');
    if (modalEl) {
        globalUserDetailsModal = new bootstrap.Modal(modalEl);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    initGlobalUserDetailsModal();
});

function showUserDetails(userId) {
    if (!userId) return;
    
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap not loaded');
        return;
    }
    
    const modalEl = document.getElementById('globalUserDetailsModal');
    const contentEl = document.getElementById('globalUserModalContent');
    
    if (!modalEl || !contentEl) {
        console.error('User details modal not found');
        return;
    }
    
    contentEl.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    if (!globalUserDetailsModal) {
        globalUserDetailsModal = new bootstrap.Modal(modalEl);
    }
    globalUserDetailsModal.show();
    
    fetch(`{{ url('admin/users') }}/${userId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            contentEl.innerHTML = data.html;
        } else {
            contentEl.innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentEl.innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
    });
}
</script>

<style>
.clickable-user {
    color: inherit;
    text-decoration: none;
    cursor: pointer;
    transition: color 0.15s ease-in-out;
}
.clickable-user:hover {
    color: #0d6efd;
    text-decoration: underline;
}
</style>
