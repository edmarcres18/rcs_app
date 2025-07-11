import 'bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const forceTLS = import.meta.env.VITE_PUSHER_SCHEME === 'https';

const echoConfig = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: forceTLS,
    disableStats: true,
    enabledTransports: ['ws', 'wss']
};

// If you are using a self-hosted Laravel Websockets server,
// you must define VITE_PUSHER_HOST and VITE_PUSHER_PORT.
// For the cloud-based Pusher service, these should be undefined.
if (import.meta.env.VITE_PUSHER_HOST) {
    echoConfig.wsHost = import.meta.env.VITE_PUSHER_HOST;
    echoConfig.wsPort = import.meta.env.VITE_PUSHER_PORT;
}

window.Echo = new Echo(echoConfig);
