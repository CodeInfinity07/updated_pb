@auth
@if(Auth::user()->role === 'user')
<div class="modal fade" id="prizeClaimModal" tabindex="-1" aria-labelledby="prizeClaimModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white border-0 py-2">
                <div class="d-flex align-items-center">
                    <iconify-icon icon="solar:gift-bold-duotone" class="me-2" style="font-size: 1.2rem;"></iconify-icon>
                    <h6 class="modal-title mb-0" id="prizeClaimModalLabel">Congratulations!</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-2">
                <div class="prize-icon mb-2">
                    <iconify-icon icon="solar:cup-star-bold-duotone" class="text-warning" style="font-size: 2.5rem;"></iconify-icon>
                </div>
                <h6 class="mb-1">You have unclaimed prizes!</h6>
                <p class="text-muted small mb-2" id="prizeClaimDescription">You have won prizes from leaderboard competitions.</p>
                
                <div id="prizeClaimList" class="mb-2" style="max-height: 120px; overflow-y: auto;">
                </div>
                
                <div class="bg-light rounded p-2 mb-2">
                    <span class="text-muted small">Total Amount:</span>
                    <h5 class="mb-0 text-success" id="totalPrizeAmount">$0.00</h5>
                </div>
            </div>
            <div class="modal-footer border-0 justify-content-center py-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Later</button>
                <button type="button" class="btn btn-success btn-sm px-3" id="claimAllPrizesBtn" onclick="claimAllPrizes()">
                    <iconify-icon icon="solar:hand-money-bold" class="me-1"></iconify-icon>
                    Claim All
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #prizeClaimModal {
        z-index: 9990 !important;
    }
    #prizeClaimModal .modal-dialog {
        z-index: 9991 !important;
    }
    #prizeClaimModal .modal-content {
        z-index: 9992 !important;
        position: relative;
    }
    #prizeClaimModal .prize-icon {
        animation: bounce 1s ease-in-out infinite;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .prize-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px 15px;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .prize-item:last-child {
        margin-bottom: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for bootstrap to be available before checking prizes
    function waitForBootstrapAndCheck() {
        if (typeof bootstrap === 'undefined') {
            setTimeout(waitForBootstrapAndCheck, 50);
            return;
        }
        checkPendingPrizes();
    }
    waitForBootstrapAndCheck();
});

function checkPendingPrizes() {
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap not loaded');
        return;
    }
    
    fetch('{{ route("user.leaderboards.api.pending-prizes") }}', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.count > 0) {
                displayPrizes(data.prizes, data.total_amount);
                var modal = new bootstrap.Modal(document.getElementById('prizeClaimModal'));
                modal.show();
            }
        })
        .catch(error => {
            console.error('Error checking pending prizes:', error);
        });
}

function displayPrizes(prizes, totalAmount) {
    const listContainer = document.getElementById('prizeClaimList');
    const totalEl = document.getElementById('totalPrizeAmount');
    const descEl = document.getElementById('prizeClaimDescription');
    
    descEl.textContent = `You have ${prizes.length} prize${prizes.length > 1 ? 's' : ''} ready to claim!`;
    totalEl.textContent = '$' + parseFloat(totalAmount).toFixed(2);
    
    let html = '';
    prizes.forEach(prize => {
        html += `
            <div class="prize-item">
                <div>
                    <strong>${prize.leaderboard_name}</strong>
                    <br><small class="text-muted">${prize.position_display}</small>
                </div>
                <div class="text-success fw-bold">${prize.formatted_amount}</div>
            </div>
        `;
    });
    listContainer.innerHTML = html;
}

function claimAllPrizes() {
    const btn = document.getElementById('claimAllPrizesBtn');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Claiming...';
    
    fetch('{{ route("user.leaderboards.api.claim-all-prizes") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('prizeClaimList').innerHTML = `
                <div class="alert alert-success mb-0">
                    <iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon>
                    ${data.message}
                </div>
            `;
            document.getElementById('totalPrizeAmount').textContent = '$0.00';
            btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="me-1"></iconify-icon> Claimed!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
            
            setTimeout(() => {
                var modal = bootstrap.Modal.getInstance(document.getElementById('prizeClaimModal'));
                modal.hide();
                location.reload();
            }, 2000);
        } else {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert(data.message || 'Failed to claim prizes');
        }
    })
    .catch(error => {
        console.error('Error claiming prizes:', error);
        btn.innerHTML = originalText;
        btn.disabled = false;
        alert('Failed to claim prizes. Please try again.');
    });
}
</script>
@endif
@endauth
