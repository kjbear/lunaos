// LunaOS app.js
// No WebSocket - Herd uses pre-built assets

// Fix for Vite import.meta.env error in production
try {
    if (typeof import !== 'undefined' && import.meta) {
        import.meta.env = import.meta.env || {};
    }
} catch (e) {
    // Silent fail for production builds
}

// Future: Add Alpine.js, HTMX enhancements, or other client-side logic here
console.log('🌙 LunaOS loaded');
