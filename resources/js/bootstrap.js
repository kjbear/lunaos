import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo for WebSocket broadcasting
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
});

// Listen for subagent activity on mission-control channel
window.Echo.channel('mission-control')
    .listen('.subagent.activity', (event) => {
        // Dispatch to Livewire components
        window.Livewire?.dispatch('activity-received', event);
        
        // Also dispatch a custom event for non-Livewire components
        window.dispatchEvent(new CustomEvent('subagent-activity', { detail: event }));
    });

console.log('🔌 WebSocket connected to Reverb');