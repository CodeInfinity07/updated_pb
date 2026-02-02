@extends('admin.layouts.vertical', ['title' => 'Chat with ' . ($conversation->user ? $conversation->user->first_name : 'User'), 'subTitle' => 'Live chat conversation'])

@section('css')
<style>
    .chat-container {
        height: calc(100vh - 300px);
        min-height: 400px;
        display: flex;
        flex-direction: column;
    }
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
    }
    .chat-message {
        margin-bottom: 15px;
        display: flex;
        flex-direction: column;
    }
    .chat-message.user {
        align-items: flex-start;
    }
    .chat-message.admin {
        align-items: flex-end;
    }
    .chat-bubble {
        max-width: 70%;
        padding: 12px 16px;
        border-radius: 16px;
        word-wrap: break-word;
    }
    .chat-message.user .chat-bubble {
        background: white;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 4px;
    }
    .chat-message.admin .chat-bubble {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-meta {
        font-size: 11px;
        color: #999;
        margin-top: 4px;
    }
    .chat-input-area {
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
        background: white;
    }
    .user-info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="iconamoon:arrow-left-2-duotone" class="me-1"></iconify-icon>
                Back to Conversations
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm me-2">
                            <span class="avatar-title bg-white text-primary rounded-circle">
                                {{ $conversation->user ? strtoupper(substr($conversation->user->first_name, 0, 1)) : '?' }}
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $conversation->user ? $conversation->user->first_name . ' ' . $conversation->user->last_name : 'Unknown User' }}</h6>
                            <small class="text-muted">{{ $conversation->user ? $conversation->user->email : '' }}</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" id="statusSelect" style="width: auto;">
                            <option value="open" {{ $conversation->status == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="pending" {{ $conversation->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="closed" {{ $conversation->status == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                </div>
                <div class="chat-container">
                    <div class="chat-messages" id="chatMessages">
                        @foreach($conversation->messages as $msg)
                        <div class="chat-message {{ $msg->sender_type }}">
                            <div class="chat-bubble">{{ $msg->message }}</div>
                            <span class="chat-meta">
                                {{ $msg->sender_name }} - {{ $msg->created_at->format('M d, g:i A') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @if($conversation->status != 'closed')
                    <div class="chat-input-area">
                        <form id="chatForm" class="d-flex gap-2">
                            <input type="text" class="form-control" id="messageInput" placeholder="Type your reply..." maxlength="2000" autofocus>
                            <button type="submit" class="btn btn-primary">
                                <iconify-icon icon="iconamoon:send-duotone"></iconify-icon>
                                Send
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="chat-input-area text-center text-muted">
                        <iconify-icon icon="iconamoon:lock-duotone" class="me-1"></iconify-icon>
                        This conversation is closed
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card user-info-card mb-3">
                <div class="card-body text-center py-4">
                    <div class="avatar-lg mx-auto mb-3">
                        <span class="avatar-title bg-white text-primary rounded-circle fs-3">
                            {{ $conversation->user ? strtoupper(substr($conversation->user->first_name, 0, 1)) : '?' }}
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $conversation->user ? $conversation->user->first_name . ' ' . $conversation->user->last_name : 'Unknown' }}</h5>
                    <p class="mb-0 opacity-75">{{ $conversation->user ? $conversation->user->email : '' }}</p>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="card-title mb-0">User Details</h6>
                </div>
                <div class="card-body">
                    @if($conversation->user)
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Username</td>
                            <td class="text-end fw-medium">{{ $conversation->user->username }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone</td>
                            <td class="text-end fw-medium">{{ $conversation->user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $conversation->user->status == 'active' ? 'success' : 'warning' }}-subtle text-{{ $conversation->user->status == 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($conversation->user->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Joined</td>
                            <td class="text-end fw-medium">{{ $conversation->user->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                    <div class="mt-3">
                        <a href="{{ route('admin.users.show', $conversation->user) }}" class="btn btn-outline-primary btn-sm w-100">
                            <iconify-icon icon="iconamoon:profile-duotone" class="me-1"></iconify-icon>
                            View Full Profile
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            
            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Conversation Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr>
                            <td class="text-muted">Started</td>
                            <td class="text-end fw-medium">{{ $conversation->created_at->format('M d, Y g:i A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Activity</td>
                            <td class="text-end fw-medium">{{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : 'Never' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Total Messages</td>
                            <td class="text-end fw-medium">{{ $conversation->messages->count() }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Assigned To</td>
                            <td class="text-end fw-medium">{{ $conversation->admin ? $conversation->admin->name : 'You' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
const conversationId = {{ $conversation->id }};
let lastMessageId = {{ $conversation->messages->last() ? $conversation->messages->last()->id : 0 }};
const displayedMessageIds = new Set();
let pendingOptimisticMessages = [];

document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
    
    @foreach($conversation->messages as $msg)
    displayedMessageIds.add({{ $msg->id }});
    @endforeach
    
    document.getElementById('chatForm').addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });
    
    document.getElementById('statusSelect').addEventListener('change', function() {
        updateStatus(this.value);
    });
    
    setInterval(refreshMessages, 5000);
});

function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    if (!message) return;
    
    input.value = '';
    
    const tempId = 'temp_' + Date.now();
    pendingOptimisticMessages.push({ tempId, message });
    
    appendOptimisticMessage({
        tempId: tempId,
        sender_type: 'admin',
        sender_name: 'You',
        message: message,
        created_at: new Date().toISOString()
    });
    
    fetch(`/admin/chat/${conversationId}/send`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message: message })
    })
    .then(r => r.json())
    .then(data => {
        if (data.message && data.message.id) {
            displayedMessageIds.add(data.message.id);
            lastMessageId = Math.max(lastMessageId, data.message.id);
            pendingOptimisticMessages = pendingOptimisticMessages.filter(m => m.message !== message);
        }
    });
}

function appendOptimisticMessage(msg) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-message ' + msg.sender_type;
    div.setAttribute('data-temp-id', msg.tempId);
    
    const date = new Date(msg.created_at);
    const time = date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
    
    div.innerHTML = `
        <div class="chat-bubble">${escapeHtml(msg.message)}</div>
        <span class="chat-meta">${msg.sender_name} - ${time}</span>
    `;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function appendMessage(msg) {
    if (displayedMessageIds.has(msg.id)) {
        return;
    }
    displayedMessageIds.add(msg.id);
    
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-message ' + msg.sender_type;
    div.setAttribute('data-message-id', msg.id);
    
    const date = new Date(msg.created_at);
    const time = date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
    
    div.innerHTML = `
        <div class="chat-bubble">${escapeHtml(msg.message)}</div>
        <span class="chat-meta">${msg.sender_name} - ${time}</span>
    `;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateStatus(status) {
    fetch(`/admin/chat/${conversationId}/status`, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: status })
    }).then(() => {
        if (status === 'closed') {
            location.reload();
        }
    });
}

function refreshMessages() {
    fetch(`/admin/chat/${conversationId}/messages`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        data.messages.forEach(msg => {
            if (msg.id > lastMessageId && !displayedMessageIds.has(msg.id)) {
                appendMessage(msg);
                lastMessageId = msg.id;
            }
        });
    });
}
</script>
@endsection
