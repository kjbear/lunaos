<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket Test</title>
</head>
<body>
    <h1>WebSocket Connection Test</h1>
    <div id="status">Checking connection...</div>
    <div id="log"></div>
    
    @vite(['resources/js/app.js'])
    
    <script>
        function log(msg) {
            const div = document.createElement('div');
            div.textContent = msg;
            document.getElementById('log').appendChild(div);
            console.log(msg);
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            log('Page loaded');
            
            // Check if Echo is available
            setTimeout(() => {
                if (typeof window.Echo === 'undefined') {
                    log('ERROR: Echo not initialized!');
                    document.getElementById('status').textContent = '❌ Echo not initialized';
                    return;
                }
                
                log('Echo found: ' + typeof window.Echo);
                log('Echo.connector: ' + typeof window.Echo.connector);
                
                if (window.Echo.connector) {
                    log('Echo.connector.socket: ' + typeof window.Echo.connector.socket);
                    
                    if (window.Echo.connector.socket) {
                        const socket = window.Echo.connector.socket;
                        log('Socket readyState: ' + socket.readyState);
                        log('  0=CONNECTING, 1=OPEN, 2=CLOSING, 3=CLOSED');
                        
                        const states = ['CONNECTING', 'OPEN', 'CLOSING', 'CLOSED'];
                        document.getElementById('status').textContent = 'Socket state: ' + states[socket.readyState];
                        
                        // Override event handlers
                        socket.onopen = () => {
                            log('✅ WebSocket OPEN');
                            document.getElementById('status').textContent = '✅ Connected';
                        };
                        
                        socket.onclose = (e) => {
                            log('❌ WebSocket CLOSED: ' + JSON.stringify(e));
                            document.getElementById('status').textContent = '❌ Disconnected';
                        };
                        
                        socket.onerror = (e) => {
                            log('❌ WebSocket ERROR: ' + JSON.stringify(e));
                            document.getElementById('status').textContent = '❌ Error';
                        };
                    } else {
                        log('No socket object yet');
                    }
                } else {
                    log('No Echo.connector');
                }
            }, 2000);
        });
    </script>
</body>
</html>
