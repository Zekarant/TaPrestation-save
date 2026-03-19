// Messaging JavaScript
document.addEventListener('DOMContentLoaded', function() {
    initMessaging();
});

function initMessaging() {
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    const messagesContainer = document.querySelector('.messages-container');
    const conversationItems = document.querySelectorAll('.conversation-item');

    // Auto-scroll to bottom of messages
    if (messagesContainer) {
        scrollToBottom(messagesContainer);
    }

    // Send message on button click
    if (sendButton) {
        sendButton.addEventListener('click', sendMessage);
    }

    // Send message on Enter key
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Enable/disable send button based on input
        messageInput.addEventListener('input', function() {
            if (sendButton) {
                sendButton.disabled = this.value.trim() === '';
            }
        });
    }

    // Conversation selection
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            const conversationId = this.dataset.conversationId;
            if (conversationId) {
                window.location.href = `/messages/${conversationId}`;
            }
        });
    });
}

function sendMessage() {
    const messageInput = document.getElementById('message-input');
    const conversationId = document.getElementById('conversation-id')?.value;
    const content = messageInput?.value.trim();

    if (!content || !conversationId) return;

    // Disable input while sending
    messageInput.disabled = true;
    const sendButton = document.getElementById('send-button');
    if (sendButton) sendButton.disabled = true;

    // Send via AJAX
    fetch('/messages/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify({
            conversation_id: conversationId,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add message to UI
            addMessageToUI(data.message);
            messageInput.value = '';
        } else {
            alert(data.error || 'Erreur lors de l\'envoi du message');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erreur lors de l\'envoi du message');
    })
    .finally(() => {
        messageInput.disabled = false;
        if (sendButton) sendButton.disabled = false;
        messageInput.focus();
    });
}

function addMessageToUI(message) {
    const messagesContainer = document.querySelector('.messages-container');
    if (!messagesContainer) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${message.is_mine ? 'sent' : 'received'}`;
    messageDiv.innerHTML = `
        <div class="message-content">
            <div class="message-text">${escapeHtml(message.content)}</div>
            <div class="message-time">${message.created_at}</div>
        </div>
    `;

    messagesContainer.appendChild(messageDiv);
    scrollToBottom(messagesContainer);
}

function scrollToBottom(container) {
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Mark messages as read when viewing
function markAsRead(conversationId) {
    fetch(`/messages/${conversationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        }
    }).catch(error => console.error('Error marking as read:', error));
}

// Poll for new messages (simple polling, can be replaced with WebSockets)
let pollingInterval = null;

function startPolling(conversationId) {
    if (pollingInterval) clearInterval(pollingInterval);
    
    pollingInterval = setInterval(() => {
        checkNewMessages(conversationId);
    }, 5000); // Check every 5 seconds
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
}

function checkNewMessages(conversationId) {
    const lastMessageId = document.querySelector('.message:last-child')?.dataset?.messageId || 0;
    
    fetch(`/messages/${conversationId}/new?after=${lastMessageId}`)
        .then(response => response.json())
        .then(data => {
            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(message => addMessageToUI(message));
            }
        })
        .catch(error => console.error('Polling error:', error));
}

// Export functions for use in other scripts
window.Messaging = {
    init: initMessaging,
    send: sendMessage,
    startPolling: startPolling,
    stopPolling: stopPolling,
    markAsRead: markAsRead
};
