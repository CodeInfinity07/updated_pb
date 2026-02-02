@if(getSetting('live_chat_enabled', true))
<style>
    .chat-widget-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .chat-widget-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }
    .chat-widget-btn i {
        font-size: 24px;
        color: white;
    }
    .chat-widget-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    .chat-widget-box {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 380px;
        max-width: calc(100vw - 40px);
        height: 500px;
        max-height: calc(100vh - 120px);
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        z-index: 9998;
        display: none;
        flex-direction: column;
        overflow: hidden;
    }
    .chat-widget-box.open {
        display: flex;
    }
    
    @media (max-width: 768px) {
        .chat-widget-btn {
            bottom: 80px;
        }
        .chat-widget-box {
            bottom: 150px;
            max-height: calc(100vh - 200px);
        }
    }
    .chat-widget-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .chat-widget-header h5 {
        margin: 0;
        font-weight: 600;
    }
    .chat-widget-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }
    .chat-widget-messages {
        flex: 1;
        overflow-y: auto;
        padding: 15px;
        background: #f8f9fa;
    }
    .chat-message {
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
    }
    .chat-message.user {
        align-items: flex-end;
    }
    .chat-message.admin {
        align-items: flex-start;
    }
    .chat-message-bubble {
        max-width: 80%;
        padding: 10px 14px;
        border-radius: 16px;
        word-wrap: break-word;
    }
    .chat-message.user .chat-message-bubble {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }
    .chat-message.admin .chat-message-bubble {
        background: white;
        color: #333;
        border: 1px solid #e9ecef;
        border-bottom-left-radius: 4px;
    }
    .chat-message-time {
        font-size: 11px;
        color: #999;
        margin-top: 4px;
    }
    .chat-widget-input {
        padding: 12px 15px;
        border-top: 1px solid #e9ecef;
        display: flex;
        gap: 10px;
        background: white;
    }
    .chat-widget-input input {
        flex: 1;
        border: 1px solid #e0e0e0;
        border-radius: 24px;
        padding: 10px 16px;
        outline: none;
        font-size: 14px;
    }
    .chat-widget-input input:focus {
        border-color: #667eea;
    }
    .chat-widget-input button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 50%;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .chat-widget-input button:hover {
        transform: scale(1.05);
    }
    .chat-widget-input button i {
        color: white;
        font-size: 18px;
    }
    .chat-welcome {
        text-align: center;
        padding: 40px 20px;
    }
    .chat-welcome i {
        font-size: 48px;
        color: #667eea;
        margin-bottom: 15px;
    }
    .chat-welcome h6 {
        margin-bottom: 10px;
        color: #333;
    }
    .chat-welcome p {
        color: #666;
        font-size: 14px;
        margin-bottom: 20px;
    }
    .chat-welcome button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 24px;
        cursor: pointer;
        font-weight: 500;
    }
    .chat-status {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .chat-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #28a745;
    }
    .chat-status span {
        font-size: 12px;
        opacity: 0.9;
    }
    .chat-closed-notice {
        padding: 16px 20px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        text-align: center;
    }
    .chat-closed-notice p {
        color: #6c757d;
        font-size: 14px;
        margin-bottom: 12px;
    }
    .chat-closed-notice button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 10px 24px;
        border-radius: 24px;
        cursor: pointer;
        font-weight: 500;
    }
    .chat-closed-notice button:hover {
        transform: scale(1.02);
    }
</style>

<button class="chat-widget-btn" id="chatWidgetBtn" title="Chat with Support">
    <i class="bx bx-message-dots"></i>
    <span class="chat-widget-badge" id="chatUnreadBadge" style="display: none;">0</span>
</button>

<div class="chat-widget-box" id="chatWidgetBox">
    <div class="chat-widget-header">
        <div>
            <h5>Support Chat</h5>
            <div class="chat-status">
                <span class="chat-status-dot"></span>
                <span>Online</span>
            </div>
        </div>
        <button class="chat-widget-close" id="chatWidgetClose">&times;</button>
    </div>
    
    <div class="chat-widget-messages" id="chatMessages">
        <div class="chat-welcome" id="chatWelcome">
            <i class="bx bx-support"></i>
            <h6>Welcome to Support</h6>
            <p>How can we help you today? Start a conversation with our team.</p>
            <button onclick="startChat()">Start Chat</button>
        </div>
    </div>
    
    <div class="chat-widget-input" id="chatInputArea" style="display: none;">
        <input type="text" id="chatInput" placeholder="Type your message..." maxlength="2000">
        <button onclick="sendMessage()">
            <i class="bx bx-send"></i>
        </button>
    </div>
    
    <div class="chat-closed-notice" id="chatClosedNotice" style="display: none;">
        <p>This chat has been closed by support.</p>
        <button onclick="startNewChat()">Start New Chat</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatBtn = document.getElementById('chatWidgetBtn');
    const chatBox = document.getElementById('chatWidgetBox');
    const chatClose = document.getElementById('chatWidgetClose');
    const chatInput = document.getElementById('chatInput');
    
    chatBtn.addEventListener('click', function() {
        chatBox.classList.toggle('open');
        if (chatBox.classList.contains('open')) {
            loadConversation();
        }
    });
    
    chatClose.addEventListener('click', function() {
        chatBox.classList.remove('open');
    });
    
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    checkUnreadMessages();
    setInterval(checkUnreadMessages, 30000);
    
    initializeEcho();
});

let currentConversationId = null;
let echoChannel = null;

function initializeEcho() {
    if (typeof window.Echo === 'undefined') {
        console.log('Laravel Echo not loaded, using polling fallback');
        setInterval(pollMessages, 5000);
        return;
    }
    
    console.log('Laravel Echo loaded, initializing chat channels...');
    
    const userChannel = window.Echo.private('user.chat.{{ auth()->id() }}');
    
    userChannel.listen('.conversation.updated', (e) => {
        console.log('Conversation updated event received:', e);
        
        if (e.action === 'status_changed' && e.status === 'closed') {
            handleChatClosed();
        }
        
        if (e.unread_count > 0) {
            updateUnreadBadge(e.unread_count);
        }
    });
    
    userChannel.error((error) => {
        console.error('User chat channel error:', error);
    });
}

function subscribeToConversation(conversationId) {
    if (typeof window.Echo === 'undefined') {
        console.log('Echo not available, cannot subscribe to conversation');
        return;
    }
    
    console.log('Subscribing to conversation channel:', conversationId);
    
    if (echoChannel) {
        console.log('Leaving previous channel:', currentConversationId);
        window.Echo.leave('chat.conversation.' + currentConversationId);
    }
    
    echoChannel = window.Echo.private('chat.conversation.' + conversationId);
    
    echoChannel.listen('.new.message', (e) => {
        console.log('New message event received:', e);
        if (e.sender_type === 'admin') {
            appendMessage({
                sender_type: e.sender_type,
                message: e.message,
                created_at: e.created_at
            });
            markMessagesAsRead();
        }
    });
    
    echoChannel.error((error) => {
        console.error('Conversation channel subscription error:', error);
    });
    
    console.log('Subscribed to chat.conversation.' + conversationId);
}

function pollMessages() {
    if (!currentConversationId) return;
    
    fetch('/chat/conversation', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.messages && document.getElementById('chatWidgetBox').classList.contains('open')) {
            showMessages(data.messages);
        }
    })
    .catch(() => {});
}

function markMessagesAsRead() {
    if (!currentConversationId) return;
    fetch('/chat/mark-read', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    }).catch(() => {});
}

function updateUnreadBadge(count) {
    const badge = document.getElementById('chatUnreadBadge');
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'flex';
    } else {
        badge.style.display = 'none';
    }
}

function loadConversation() {
    console.log('Loading conversation...');
    fetch('/chat/conversation', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        console.log('Conversation data received:', data);
        if (data.conversation) {
            currentConversationId = data.conversation.id;
            console.log('Conversation ID:', currentConversationId);
            
            const welcomeEl = document.getElementById('chatWelcome');
            if (welcomeEl) {
                welcomeEl.style.display = 'none';
            }
            
            if (data.messages && data.messages.length > 0) {
                showMessages(data.messages);
            }
            
            if (data.conversation.status === 'closed') {
                document.getElementById('chatInputArea').style.display = 'none';
                document.getElementById('chatClosedNotice').style.display = 'block';
            } else {
                document.getElementById('chatInputArea').style.display = 'flex';
                document.getElementById('chatClosedNotice').style.display = 'none';
                subscribeToConversation(currentConversationId);
                markMessagesAsRead();
            }
        } else {
            console.log('No active conversation found');
        }
    })
    .catch(err => {
        console.error('Error loading conversation:', err);
    });
}

function startChat() {
    console.log('Starting new chat...');
    fetch('/chat/start', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        console.log('Start chat response:', data);
        if (data.conversation) {
            currentConversationId = data.conversation.id;
            console.log('New conversation ID:', currentConversationId);
            
            const welcomeEl = document.getElementById('chatWelcome');
            if (welcomeEl) {
                welcomeEl.style.display = 'none';
            }
            
            document.getElementById('chatMessages').innerHTML = '';
            document.getElementById('chatInputArea').style.display = 'flex';
            document.getElementById('chatInput').focus();
            subscribeToConversation(currentConversationId);
        }
    })
    .catch(err => {
        console.error('Error starting chat:', err);
    });
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    if (!message) return;
    
    input.value = '';
    
    appendMessage({
        sender_type: 'user',
        message: message,
        created_at: new Date().toISOString()
    });
    
    fetch('/chat/send', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ message: message })
    });
}

function showMessages(messages) {
    const container = document.getElementById('chatMessages');
    container.innerHTML = '';
    messages.forEach(msg => appendMessage(msg));
}

function appendMessage(msg) {
    const container = document.getElementById('chatMessages');
    const div = document.createElement('div');
    div.className = 'chat-message ' + msg.sender_type;
    
    const time = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    
    div.innerHTML = `
        <div class="chat-message-bubble">${escapeHtml(msg.message)}</div>
        <span class="chat-message-time">${time}</span>
    `;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function checkUnreadMessages() {
    fetch('/chat/unread', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(data => {
        const badge = document.getElementById('chatUnreadBadge');
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(() => {});
}

function handleChatClosed() {
    console.log('Chat has been closed by admin');
    document.getElementById('chatInputArea').style.display = 'none';
    document.getElementById('chatClosedNotice').style.display = 'block';
    
    if (echoChannel && currentConversationId) {
        window.Echo.leave('chat.conversation.' + currentConversationId);
        echoChannel = null;
    }
    currentConversationId = null;
}

function startNewChat() {
    document.getElementById('chatClosedNotice').style.display = 'none';
    document.getElementById('chatMessages').innerHTML = `
        <div class="chat-welcome" id="chatWelcome">
            <i class="bx bx-support"></i>
            <h6>Welcome to Support</h6>
            <p>How can we help you today? Start a conversation with our team.</p>
            <button onclick="startChat()">Start Chat</button>
        </div>
    `;
}
</script>
@endif
