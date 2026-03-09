// LunaOS Echo Configuration
// WebSocket connection setup for Laravel Reverb

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally for Laravel Echo
window.Pusher = Pusher;

// Connection status tracking
window.lunaosWebSocketStatus = 'connecting';

function setConnectionStatus(status) {
    window.lunaosWebSocketStatus = status;
    console.log(`🔄 WebSocket status: ${status}`);
    window.dispatchEvent(new CustomEvent(`lunaos:websocket-${status}`));
}

// Get configuration from Vite env or use defaults
const reverbConfig = {
    key: import.meta.env.VITE_REVERB_APP_KEY || 'hkgpejpfqogi6gg45kra',
    host: import.meta.env.VITE_REVERB_HOST || 'localhost',
    port: parseInt(import.meta.env.VITE_REVERB_PORT) || 8080,
    scheme: import.meta.env.VITE_REVERB_SCHEME || 'http'
};

console.log('Reverb config:', reverbConfig);

// Initialize Echo with Reverb configuration
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: reverbConfig.key,
    wsHost: reverbConfig.host,
    wsPort: reverbConfig.port,
    wssPort: reverbConfig.port,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

console.log('Echo initialized:', window.Echo);

// Monitor connection state via Pusher connector
// Reverb uses Pusher protocol under the hood
if (window.Echo.connector && window.Echo.connector.pusher) {
    const pusher = window.Echo.connector.pusher;
    
    // Set initial status
    setConnectionStatus('connecting');
    
    pusher.connection.bind('connected', () => {
        console.log('✅ WebSocket connected!');
        setConnectionStatus('connected');
        
        // Also check socket if available
        if (window.Echo.connector.socket) {
            console.log('Socket readyState:', window.Echo.connector.socket.readyState, '(1=OPEN)');
        }
    });
    
    pusher.connection.bind('connecting', () => {
        console.log('🔄 WebSocket connecting...');
        setConnectionStatus('connecting');
    });
    
    pusher.connection.bind('disconnected', () => {
        console.log('❌ WebSocket disconnected');
        setConnectionStatus('disconnected');
    });
    
    pusher.connection.bind('unavailable', () => {
        console.log('⚠️ WebSocket unavailable');
        setConnectionStatus('disconnected');
    });
    
    pusher.connection.bind('failed', (err) => {
        console.error('❌ WebSocket failed:', err);
        setConnectionStatus('disconnected');
    });
    
    pusher.connection.bind('error', (err) => {
        console.error('❌ WebSocket error:', err);
        setConnectionStatus('disconnected');
    });
    
    console.log('Pusher connection monitoring set up');
} else {
    console.warn('No Pusher connector available');
}

/**
 * Subscribe to a chat session channel for real-time updates
 * @param {string} sessionId - The chat session ID
 */
window.subscribeToChat = function(sessionId) {
    if (!window.Echo || !sessionId) {
        console.warn('Cannot subscribe: Echo not initialized or no sessionId');
        return null;
    }
    
    console.log(`📡 Subscribing to chat.${sessionId}`);
    
    // Leave any previous chat channel
    if (window.currentChatChannel) {
        try {
            window.Echo.leave(`chat.${window.currentChatChannel}`);
        } catch (e) {
            console.warn('Error leaving previous channel:', e);
        }
    }
    
    window.currentChatChannel = sessionId;
    
    // Use private channel for the chat session
    const channel = window.Echo.channel(`chat.${sessionId}`);
    
    // Listen for broadcast events
    channel.listen('.user.message', (event) => {
        console.log('📨 Received user.message event:', event);
        window.dispatchEvent(new CustomEvent('lunaos:user-message-sent', {
            detail: {
                sessionId: event.session_id,
                messageId: event.message_id,
                message: event.content,
                timestamp: event.timestamp
            }
        }));
    });
    
    channel.listen('.AiTokenReceived', (event) => {
        console.log('🔤 Received AI token:', event.token);
        window.dispatchEvent(new CustomEvent('lunaos:ai-token-received', {
            detail: {
                sessionId: event.session_id,
                token: event.token,
                sequence: event.sequence
            }
        }));
    });
    
    channel.listen('.AiResponseComplete', (event) => {
        console.log('✅ AI response complete:', event);
        window.dispatchEvent(new CustomEvent('lunaos:ai-response-complete', {
            detail: {
                sessionId: event.session_id,
                messageId: event.message_id,
                content: event.content,
                metadata: event.metadata,
                timestamp: event.timestamp
            }
        }));
    });
    
    console.log('✅ Subscribed to channel:', channel);
    return channel;
};

/**
 * Unsubscribe from current chat channel
 */
window.leaveCurrentChat = function() {
    if (window.Echo && window.currentChatChannel) {
        try {
            window.Echo.leave(`chat.${window.currentChatChannel}`);
            console.log('👋 Left channel:', window.currentChatChannel);
        } catch (e) {
            console.warn('Error leaving channel:', e);
        }
        window.currentChatChannel = null;
    }
};

/**
 * Get current connection status
 */
window.getWebSocketStatus = function() {
    return window.lunaosWebSocketStatus;
};

// Export for modules that need to use it
export { Echo };