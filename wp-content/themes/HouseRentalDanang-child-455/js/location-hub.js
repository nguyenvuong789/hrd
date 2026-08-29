(function () {
	'use strict';

	var config = window.hrdLocationHub;
	var hub = document.querySelector('[data-location-hub]');

	if (!config || !hub || !window.fetch || !window.FormData) {
		return;
	}

	var sortControl = document.querySelector('[data-hub-sort]');
	if (sortControl) {
		sortControl.addEventListener('change', function () {
			var url = new URL(sortControl.dataset.hubUrl || window.location.pathname, window.location.origin);
			if (sortControl.value !== 'default') {
				url.searchParams.set('sortby', sortControl.value);
			}
			window.location.assign(url.toString());
		});
	}

	hub.addEventListener('click', function (event) {
		var button = event.target.closest('[data-hub-load-more]');
		if (!button || button.disabled || button.dataset.loading === 'true') {
			return;
		}

		var section = button.closest('[data-hub-section]');
		var grid = section ? section.querySelector('[data-hub-grid]') : null;
		var status = section ? section.querySelector('[data-hub-status]') : null;
		var nextPage = Number.parseInt(button.dataset.page || '1', 10) + 1;
		if (!section || !grid || !status || !Number.isInteger(nextPage)) {
			return;
		}

		var body = new FormData();
		body.append('action', config.action);
		body.append('nonce', config.nonce);
		body.append('district', config.district);
		body.append('section', button.dataset.section || '');
		body.append('page', String(nextPage));
		body.append('sort', config.sort);
		body.append('language', config.language);
		body.append('lang', config.language);

		button.disabled = true;
		button.dataset.loading = 'true';
		button.classList.add('is-loading');
		status.classList.remove('is-error');
		status.textContent = config.strings.loading;
		var controller = window.AbortController ? new AbortController() : null;
		var timeout = controller ? window.setTimeout(function () {
			controller.abort();
		}, 15000) : null;

		var requestOptions = {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		};
		if (controller) {
			requestOptions.signal = controller.signal;
		}

		window.fetch(config.ajaxUrl, requestOptions)
			.then(function (response) {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}
				return response.json();
			})
			.then(function (response) {
				if (!response.success || !response.data || response.data.page !== nextPage || response.data.district !== config.district) {
					throw new Error('Invalid response');
				}

				var template = document.createElement('template');
				template.innerHTML = response.data.html || '';
				var firstNewCard = template.content.querySelector('article');
				if (!firstNewCard) {
					button.parentNode.removeChild(button);
					status.textContent = config.strings.empty;
					return;
				}

				grid.appendChild(template.content);
				button.dataset.page = String(nextPage);
				status.textContent = config.strings.loaded;

				if (!response.data.has_more) {
					button.parentNode.removeChild(button);
				} else {
					button.disabled = false;
					button.dataset.loading = 'false';
					button.classList.remove('is-loading');
				}

				if (firstNewCard) {
					var focusTarget = firstNewCard.querySelector('h3 a, a');
					if (focusTarget) {
						focusTarget.focus({ preventScroll: true });
					}
				}
			})
			.catch(function () {
				button.disabled = false;
				button.dataset.loading = 'false';
				button.classList.remove('is-loading');
				status.classList.add('is-error');
				status.textContent = config.strings.error;
			})
			.then(function () {
				if (timeout) {
					window.clearTimeout(timeout);
				}
			});
	});
}());
