/* Child Theme - Custom JS File for Users to add their own JS code */
(function () {
	'use strict';

	var interfaceLabels = {
		en: {
			openMenu: 'Open navigation menu', closeMenu: 'Close navigation menu',
			saveProperty: 'Save property', compareProperty: 'Compare property'
		},
		vi: {
			openMenu: 'Mở menu điều hướng', closeMenu: 'Đóng menu điều hướng',
			saveProperty: 'Lưu bất động sản', compareProperty: 'So sánh bất động sản'
		},
		ko: {
			openMenu: '탐색 메뉴 열기', closeMenu: '탐색 메뉴 닫기',
			saveProperty: '매물 저장', compareProperty: '매물 비교'
		},
		ja: {
			openMenu: 'ナビゲーションメニューを開く', closeMenu: 'ナビゲーションメニューを閉じる',
			saveProperty: '物件を保存', compareProperty: '物件を比較'
		},
		ru: {
			openMenu: 'Открыть меню навигации', closeMenu: 'Закрыть меню навигации',
			saveProperty: 'Сохранить объект', compareProperty: 'Сравнить объект'
		},
		zh: {
			openMenu: '打开导航菜单', closeMenu: '关闭导航菜单',
			saveProperty: '收藏房源', compareProperty: '比较房源'
		}
	};

	function getDocumentLanguage() {
		return (document.documentElement.lang || 'en').slice(0, 2);
	}

	function activateWithKeyboard(element) {
		element.addEventListener('keydown', function (event) {
			if ('Enter' === event.key || ' ' === event.key) {
				event.preventDefault();
				element.click();
			}
		});
	}

	function enhanceMobileControls() {
		var language = getDocumentLanguage();
		var activeLabels = interfaceLabels[language] || interfaceLabels.en;
		var hamburger = document.querySelector('.rh_temp_header_responsive_view .rh_menu__hamburger');

		if (hamburger) {
			var responsiveMenu = document.querySelector('.rh_temp_header_responsive_view .rh_menu__responsive');
			var languageDetails = responsiveMenu ? responsiveMenu.querySelector('.hrd-language-switcher details') : null;
			var closeButton = null;

			if (responsiveMenu) {
				var menuBrand = document.createElement('div');
				var headerLogo = document.querySelector('.rh_temp_header_responsive_view .rh_logo img');
				menuBrand.className = 'hrd-mobile-menu-brand';
				if (headerLogo) {
					var menuLogo = headerLogo.cloneNode(true);
					menuLogo.removeAttribute('id');
					menuBrand.appendChild(menuLogo);
				}
				responsiveMenu.prepend(menuBrand);

				closeButton = document.createElement('button');
				closeButton.type = 'button';
				closeButton.className = 'hrd-mobile-menu-close';
				closeButton.setAttribute('aria-label', activeLabels.closeMenu);
				closeButton.innerHTML = '<span aria-hidden="true"></span>';
				responsiveMenu.prepend(closeButton);
			}

			function syncMobileMenuState() {
				var isOpen = hamburger.classList.contains('is-active');
				document.documentElement.classList.toggle('hrd-mobile-menu-open', isOpen);
				hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
				hamburger.setAttribute('aria-label', isOpen ? activeLabels.closeMenu : activeLabels.openMenu);
			}

			hamburger.setAttribute('role', 'button');
			hamburger.setAttribute('tabindex', '0');
			hamburger.setAttribute('aria-label', activeLabels.openMenu);
			hamburger.setAttribute('aria-expanded', hamburger.classList.contains('is-active') ? 'true' : 'false');
			activateWithKeyboard(hamburger);

			hamburger.addEventListener('click', function () {
				window.setTimeout(syncMobileMenuState, 0);
			});

			if (closeButton) {
				closeButton.addEventListener('click', function () {
					if (hamburger.classList.contains('is-active')) {
						hamburger.click();
						hamburger.focus();
					}
				});
			}

			if (languageDetails && responsiveMenu) {
				languageDetails.addEventListener('toggle', function () {
					if (languageDetails.open) {
						window.setTimeout(function () {
							languageDetails.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
						}, 0);
					}
				});
			}

			document.addEventListener('keydown', function (event) {
				if ('Escape' === event.key && hamburger.classList.contains('is-active')) {
					hamburger.click();
					hamburger.focus();
				}
			});

			syncMobileMenuState();
		}

		document.querySelectorAll('.rh_prop_card .favorite').forEach(function (control) {
			control.setAttribute('aria-label', activeLabels.saveProperty);
			control.setAttribute('title', activeLabels.saveProperty);
		});

		document.querySelectorAll('.rh_prop_card .rh_trigger_compare').forEach(function (control) {
			control.setAttribute('aria-label', activeLabels.compareProperty);
			control.setAttribute('title', activeLabels.compareProperty);
		});

		function compactPropertyMetaLabels() {
			if (!window.matchMedia('(max-width: 767px)').matches) {
				return;
			}

			var labels = {
				Bedrooms: 'Beds',
				Bathrooms: 'Baths',
				Garage: 'Garage',
				Area: 'Area'
			};

			document.querySelectorAll('.single-property .rh_property__meta .rh_meta_titles').forEach(function (label) {
				var text = label.textContent.trim();
				if (labels[text]) {
					label.textContent = labels[text];
				}
			});
		}

		compactPropertyMetaLabels();

		function enhanceMapNote() {
			var mapNote = document.querySelector('.single-property .rh_property__common_note');
			if (!mapNote || mapNote.querySelector('.hrd-map-notice')) {
				return false;
			}

			var heading = mapNote.querySelector('.rh_property__heading');
			var disclaimer = mapNote.querySelector('p');
			var mapText = heading ? heading.textContent.trim() : mapNote.textContent.trim();
			var disclaimerText = disclaimer ? disclaimer.textContent.trim() : '';
			var labels = {
				en: 'Map location notice',
				vi: 'Lưu ý về vị trí bản đồ',
				ko: '지도 위치 안내',
				ja: '地図上の位置に関する注意',
				ru: 'Примечание о местоположении на карте',
				zh: '地图位置说明'
			};
			var disclaimerLabels = {
				en: 'Property information disclaimer',
				vi: 'Tuyên bố miễn trừ thông tin',
				ko: '매물 정보 면책 안내',
				ja: '物件情報に関する免責事項',
				ru: 'Отказ от ответственности за информацию об объекте',
				zh: '房源信息免责声明'
			};
			var details = document.createElement('details');
			var summary = document.createElement('summary');
			var content = document.createElement('p');

			details.className = 'hrd-map-notice';
			details.open = false;
			summary.textContent = labels[language] || labels.en;
			content.textContent = mapText;
			details.appendChild(summary);
			details.appendChild(content);
			mapNote.replaceChildren(details);

			if (disclaimerText) {
				var disclaimerDetails = document.createElement('details');
				var disclaimerSummary = document.createElement('summary');
				var disclaimerContent = document.createElement('p');
				disclaimerDetails.className = 'hrd-map-notice hrd-map-notice--disclaimer';
				disclaimerDetails.open = false;
				disclaimerSummary.textContent = disclaimerLabels[language] || disclaimerLabels.en;
				disclaimerContent.textContent = disclaimerText;
				disclaimerDetails.appendChild(disclaimerSummary);
				disclaimerDetails.appendChild(disclaimerContent);
				mapNote.appendChild(disclaimerDetails);
			}

			window.addEventListener('pageshow', function () {
				mapNote.querySelectorAll('.hrd-map-notice').forEach(function (note) {
					note.open = false;
				});
			}, { once: true });
			return true;
		}

		if (document.body.classList.contains('single-property') && !enhanceMapNote()) {
			var mapNoteObserver = new MutationObserver(function () {
				if (enhanceMapNote()) {
					mapNoteObserver.disconnect();
				}
			});
			mapNoteObserver.observe(document.body, { childList: true, subtree: true });
			window.setTimeout(function () {
				mapNoteObserver.disconnect();
			}, 5000);
		}

		document.querySelectorAll('.hrd-language-switcher details').forEach(function (details) {
			details.addEventListener('keydown', function (event) {
				if ('Escape' !== event.key || !details.open) {
					return;
				}

				event.preventDefault();
				event.stopPropagation();
				details.open = false;
				details.querySelector('summary').focus();
			});
		});

		var apartmentFaq = document.querySelector('.hrd-apartment-faq');
		if (apartmentFaq) {
			var apartmentFaqItems = apartmentFaq.querySelectorAll('details');
			apartmentFaqItems.forEach(function (item) {
				item.addEventListener('toggle', function () {
					if (!item.open) {
						return;
					}

					apartmentFaqItems.forEach(function (otherItem) {
						if (otherItem !== item) {
							otherItem.open = false;
						}
					});
				});
			});
		}

		if (window.jQuery) {
			window.jQuery(document)
				.on('shown.bs.select', '.rh_mod_sfoi_wrapper select', function () {
					if (window.matchMedia('(max-width: 767px)').matches) {
						document.documentElement.classList.add('hrd-search-picker-open');
					}
				})
				.on('hidden.bs.select', '.rh_mod_sfoi_wrapper select', function () {
					document.documentElement.classList.remove('hrd-search-picker-open');
				});
		}
	}

	if ('loading' === document.readyState) {
		document.addEventListener('DOMContentLoaded', enhanceMobileControls);
	} else {
		enhanceMobileControls();
	}
}());
