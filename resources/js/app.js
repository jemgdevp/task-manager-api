import './bootstrap';

const resolveAuthenticatedUserId = () => {
	if (window.__AUTH_USER_ID) {
		return Number(window.__AUTH_USER_ID);
	}

	const userMeta = document.querySelector('meta[name="user-id"]')?.getAttribute('content');

	if (userMeta) {
		return Number(userMeta);
	}

	const maybeStoredUser = localStorage.getItem('user');

	if (maybeStoredUser) {
		try {
			const parsedUser = JSON.parse(maybeStoredUser);

			if (parsedUser?.id) {
				return Number(parsedUser.id);
			}
		} catch {
			return null;
		}
	}

	return null;
};

const userId = resolveAuthenticatedUserId();

if (window.Echo && userId) {
	const taskChannel = window.Echo.private(`App.Models.User.${userId}`);

	taskChannel
		.listen('.task.created', (event) => {
			console.info('[Reverb] task.created', event);
			window.dispatchEvent(new CustomEvent('task.created', { detail: event }));
		})
		.listen('.task.updated', (event) => {
			console.info('[Reverb] task.updated', event);
			window.dispatchEvent(new CustomEvent('task.updated', { detail: event }));
		})
		.listen('.task.deleted', (event) => {
			console.info('[Reverb] task.deleted', event);
			window.dispatchEvent(new CustomEvent('task.deleted', { detail: event }));
		});
} else {
	console.info('[Reverb] Echo initialized, but no authenticated user id was resolved for private channel subscription.');
}
