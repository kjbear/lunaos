// LunaOS app.js
// WebSocket integration for real-time chat

import './bootstrap';

// Initialize Alpine.js stores for chat state
document.addEventListener('alpine:init', () => {
    // Chat state store for WebSocket-powered UI
    window.Alpine.data('chatStore', () => ({
        messages: [],
        pendingMessage: '',
        isTyping: false,
        isWebSocketConnected: false,
        isConnecting: false,
        selectedMemberId: null,
        chatId: null,
        chatTitle: '',
        
        init() {
            // Check connection status
            this.checkWebSocketStatus();
            
            // Listen for connection events
            window.addEventListener('lunaos:websocket-connected', () => {
                this.isWebSocketConnected = true;
                this.isConnecting = false;
            });
            
            window.addEventListener('lunaos:websocket-disconnected', () => {
                this.isWebSocketConnected = false;
                this.isConnecting = false;
            });
            
            window.addEventListener('lunaos:websocket-connecting', () => {
                this.isConnecting = true;
            });
            
            // Listen for chat messages via WebSocket
            window.addEventListener('lunaos:user-message-sent', (event) => {
                this.addUserMessage(event.detail.message, event.detail.timestamp);
            });
            
            window.addEventListener('lunaos:ai-token-received', (event) => {
                this.addToken(event.detail.token);
            });
            
            window.addEventListener('lunaos:ai-response-complete', (event) => {
                this.completeResponse(event.detail.metadata || event.detail.stats);
            });
        },
        
        subscribeToChat(sessionId) {
            // Use global function from echo.js
            if (typeof window.subscribeToChat === 'function') {
                window.subscribeToChat(sessionId);
            }
        },
        
        leaveChat() {
            if (typeof window.leaveCurrentChat === 'function') {
                window.leaveCurrentChat();
            }
        },
        
        checkWebSocketStatus() {
            if (typeof window.Echo !== 'undefined' && window.Echo.connector) {
                const socket = window.Echo.connector.socket;
                if (socket && socket.readyState === WebSocket.OPEN) {
                    this.isWebSocketConnected = true;
                    this.isConnecting = false;
                } else if (socket && socket.readyState === WebSocket.CONNECTING) {
                    this.isConnecting = true;
                }
            }
        },
        
        addUserMessage(message, timestamp) {
            this.messages.push({
                role: 'user',
                content: message,
                timestamp: timestamp || new Date().toLocaleTimeString(),
                isPending: false
            });
        },
        
        addToken(token) {
            // Find the last assistant message or create a new one
            let lastMessage = this.messages[this.messages.length - 1];
            
            if (!lastMessage || lastMessage.role !== 'assistant') {
                lastMessage = {
                    role: 'assistant',
                    content: '',
                    timestamp: new Date().toLocaleTimeString(),
                    isTyping: true
                };
                this.messages.push(lastMessage);
                // Scroll to bottom
                this.$nextTick(() => this.scrollToBottom());
            }
            
            lastMessage.content += token;
            lastMessage.timestamp = new Date().toLocaleTimeString();
            this.$nextTick(() => this.scrollToBottom());
        },
        
        completeResponse(stats) {
            const lastMessage = this.messages[this.messages.length - 1];
            if (lastMessage && lastMessage.role === 'assistant') {
                lastMessage.isTyping = false;
                lastMessage.metadata = stats;
            }
            this.isTyping = false;
            this.scrollToBottom();
        },
        
        scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        },
        
        async sendMessage(message) {
            if (!message.trim() || !this.selectedMemberId) return;
            
            this.pendingMessage = message;
            this.isTyping = true;
            
            // Add pending user message to UI immediately
            this.addUserMessage(message, new Date().toLocaleTimeString());
            
            // Clear input
            this.pendingMessage = '';
            
            // Dispatch Livewire event to send via WebSocket
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('send-message', {
                    message: message,
                    memberId: this.selectedMemberId,
                    chatId: this.chatId
                });
            }
        }
    }));
});
