import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Obtener variables de entorno de Vite o usar valores predeterminados seguros
const isHttps = typeof window !== 'undefined' && window.location.protocol === 'https:';
const appKey = import.meta.env.VITE_REVERB_APP_KEY || 'profesionaleswebsocketkey';
const host = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const port = import.meta.env.VITE_REVERB_PORT || (isHttps ? '443' : '8080');
const scheme = import.meta.env.VITE_REVERB_SCHEME || (isHttps ? 'https' : 'http');

const echo = new Echo({
    broadcaster: 'reverb',
    key: appKey,
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    enabledTransports: ['ws', 'wss'],
    // Custom authorizer para resolver de forma dinámica el token Sanctum en localStorage
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                const token = localStorage.getItem('token');
                
                fetch('/api/broadcasting/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': token ? `Bearer ${token}` : '',
                    },
                    body: JSON.stringify({
                        socket_id: socketId,
                        channel_name: channel.name
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('No autorizado en WebSockets.');
                    }
                    return response.json();
                })
                .then(data => {
                    callback(false, data);
                })
                .catch(error => {
                    console.error('Error de autorización en canal privado:', error);
                    callback(true, error);
                });
            }
        };
    }
});

export default echo;
