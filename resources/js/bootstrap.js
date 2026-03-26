import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.axios = axios;

window.axios.defaults.baseURL = import.meta.env.VITE_API_URL ?? 'http://localhost:8000';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = false;

window.Pusher = Pusher;

const resolveAccessToken = () => localStorage.getItem('access_token') ?? sessionStorage.getItem('access_token');
const authEndpoint = `${import.meta.env.VITE_API_URL ?? 'http://localhost:8000'}/api/broadcasting/auth`;

window.Echo = new Echo({
	broadcaster: 'reverb',
	key: import.meta.env.VITE_REVERB_APP_KEY,
	wsHost: import.meta.env.VITE_REVERB_HOST,
	wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
	wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
	forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
	enabledTransports: ['ws', 'wss'],
	authEndpoint,
	authorizer: (channel) => ({
		authorize: (socketId, callback) => {
			const accessToken = resolveAccessToken();

			window.axios
				.post(
					authEndpoint,
					{
						socket_id: socketId,
						channel_name: channel.name,
					},
					{
						headers: accessToken
							? {
									Authorization: `Bearer ${accessToken}`,
							  }
							: {},
					},
				)
				.then(({ data }) => callback(false, data))
				.catch((error) => callback(true, error?.response?.data ?? error));
		},
	}),
});
