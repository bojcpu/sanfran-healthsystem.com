import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

window.Alpine = Alpine;
window.Chart = Chart;
window.Swal = Swal;

const pwa = {
    deferredPrompt: null,
    isStandalone: window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true,
};

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    pwa.deferredPrompt = event;
    window.dispatchEvent(new CustomEvent('pwa:prompt-ready'));
});

window.addEventListener('appinstalled', () => {
    pwa.deferredPrompt = null;
    pwa.isStandalone = true;
    window.dispatchEvent(new CustomEvent('pwa:installed'));
});

const announcementPushWidget = document.querySelector('[data-announcement-push-widget]');
const staffNotificationBell = document.querySelector('[data-staff-notification-count-url]');
const staffNotificationBadge = staffNotificationBell?.querySelector('[data-staff-notification-count]');

if (staffNotificationBell && staffNotificationBadge) {
    const refreshStaffNotificationCount = async () => {
        try {
            const response = await fetch(staffNotificationBell.dataset.staffNotificationCountUrl, {
                cache: 'no-store',
                headers: { Accept: 'application/json' },
            });

            if (! response.ok) {
                return;
            }

            const data = await response.json();
            const unreadCount = Math.max(0, Number(data.unreadCount) || 0);

            staffNotificationBadge.textContent = String(unreadCount);
            staffNotificationBadge.classList.toggle('hidden', unreadCount === 0);
            staffNotificationBadge.classList.toggle('flex', unreadCount > 0);
        } catch {
            // Keep the last known count if the server is temporarily unreachable.
        }
    };

    refreshStaffNotificationCount();
    window.setInterval(refreshStaffNotificationCount, 15000);
}

if ('serviceWorker' in navigator) {
    const serviceWorkerUrl = announcementPushWidget
        ? new URL('../sw.js', announcementPushWidget.dataset.publicKeyUrl)
        : '/sw.js';

    navigator.serviceWorker.register(serviceWorkerUrl).catch(() => {
        // Service worker registration can fail on insecure origins during local testing.
    });
}

const countBadge = announcementPushWidget?.querySelector('[data-announcement-unread-count]');
const markReadButton = announcementPushWidget?.querySelector('[data-announcement-mark-read]');
const announcementBell = announcementPushWidget?.querySelector('[data-announcement-bell]');
const publicUnreadStorageKey = announcementPushWidget
    ? `sanfran-healthsys:announcement-last-read:${new URL('../', announcementPushWidget.dataset.publicKeyUrl).pathname}`
    : null;
let publicUnreadCount = 0;
let pushUnreadCount = 0;
let latestPublicAnnouncementId = 0;

const renderUnreadCount = () => {
    const unreadCount = Math.max(0, publicUnreadCount, pushUnreadCount);
    countBadge.textContent = String(unreadCount);
    countBadge.classList.toggle('hidden', unreadCount === 0);
    countBadge.classList.toggle('flex', unreadCount > 0);
    markReadButton.classList.toggle('hidden', unreadCount === 0);

    if (unreadCount > 0) {
        navigator.setAppBadge?.(unreadCount).catch(() => {});
    } else {
        navigator.clearAppBadge?.().catch(() => {});
    }
};

const markPublicAnnouncementsRead = () => {
    try {
        window.localStorage.setItem(publicUnreadStorageKey, String(latestPublicAnnouncementId));
    } catch {}

    publicUnreadCount = 0;
    renderUnreadCount();
};

const refreshPublicUnreadCount = async () => {
    try {
        const lastReadId = window.localStorage.getItem(publicUnreadStorageKey);
        const url = new URL(announcementPushWidget.dataset.publicUnreadUrl);

        if (lastReadId !== null) {
            url.searchParams.set('since_id', lastReadId);
        }

        const response = await fetch(url, { headers: { Accept: 'application/json' } });

        if (! response.ok) {
            return;
        }

        const data = await response.json();
        latestPublicAnnouncementId = Number(data.latestId) || 0;

        if (lastReadId === null) {
            window.localStorage.setItem(publicUnreadStorageKey, String(Math.max(0, latestPublicAnnouncementId - 1)));
            publicUnreadCount = latestPublicAnnouncementId > 0 ? 1 : 0;
        } else {
            publicUnreadCount = Math.max(0, Number(data.unreadCount) || 0);
        }

        announcementBell.href = publicUnreadCount > 0
            ? data.latestUnreadUrl || data.latestUrl
            : data.latestUrl;
        announcementBell.title = publicUnreadCount > 0
            ? 'View new announcement'
            : 'View latest announcement';
        announcementBell.setAttribute('aria-label', announcementBell.title);
        renderUnreadCount();
    } catch {}
};

if (announcementPushWidget) {
    refreshPublicUnreadCount();
    window.setInterval(refreshPublicUnreadCount, 10000);
    announcementBell.addEventListener('click', markPublicAnnouncementsRead);
}

const supportsAnnouncementPush = window.isSecureContext
    && 'serviceWorker' in navigator
    && 'PushManager' in window
    && 'Notification' in window;

if (announcementPushWidget && ! supportsAnnouncementPush) {
    const pushToggleButton = announcementPushWidget.querySelector('[data-announcement-push-toggle-button]');
    const statusText = announcementPushWidget.querySelector('[data-announcement-push-status]');
    const message = 'Announcement alerts require HTTPS and a browser that supports push notifications.';

    pushToggleButton.disabled = true;
    pushToggleButton.title = 'Offline alerts unavailable';
    pushToggleButton.setAttribute('aria-label', 'Offline alerts unavailable');
    statusText.textContent = message;
    markReadButton.addEventListener('click', markPublicAnnouncementsRead);
}

if (announcementPushWidget && supportsAnnouncementPush) {
    const pushToggleButton = announcementPushWidget.querySelector('[data-announcement-push-toggle-button]');
    const statusText = announcementPushWidget.querySelector('[data-announcement-push-status]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let publicKey = null;
    let pushRegistration = null;
    let currentSubscription = null;

    const setStatus = (message) => {
        statusText.textContent = message;
    };

    const sendPushRequest = async (url, body = {}) => {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });

        if (! response.ok) {
            throw new Error(`Push request failed (${response.status}).`);
        }

        return response.json();
    };

    const decodeApplicationServerKey = (encodedKey) => {
        if (! /^[A-Za-z0-9_-]{87}$/.test(encodedKey)) {
            throw new Error('Offline alerts are not configured correctly.');
        }

        const padding = '='.repeat((4 - encodedKey.length % 4) % 4);
        const base64 = (encodedKey + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);

        const key = Uint8Array.from(rawData, (character) => character.charCodeAt(0));

        if (key.length !== 65) {
            throw new Error('Offline alerts are not configured correctly.');
        }

        return key;
    };

    const syncSubscription = async (subscription) => {
        currentSubscription = subscription;
        await sendPushRequest(announcementPushWidget.dataset.subscribeUrl, subscription.toJSON());
        pushToggleButton.classList.add('hidden');

        const response = await sendPushRequest(announcementPushWidget.dataset.unreadUrl, {
            endpoint: subscription.endpoint,
        });

        const url = new URL(window.location.href);
        if (url.searchParams.has('push_read')) {
            await sendPushRequest(announcementPushWidget.dataset.markReadUrl, {
                endpoint: subscription.endpoint,
            });
            url.searchParams.delete('push_read');
            window.history.replaceState({}, '', url);
            pushUnreadCount = 0;
            markPublicAnnouncementsRead();
        } else {
            pushUnreadCount = Math.max(0, Number(response.unreadCount) || 0);
            renderUnreadCount();
        }
    };

    const subscribeCurrentBrowser = async () => {
        currentSubscription = await pushRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: decodeApplicationServerKey(publicKey),
        });
        await syncSubscription(currentSubscription);
        pushToggleButton.classList.add('hidden');
        setStatus('Offline alerts are on.');
    };

    const initializePushWidget = async () => {
        try {
            const keyResponse = await fetch(announcementPushWidget.dataset.publicKeyUrl, {
                headers: { Accept: 'application/json' },
            });

            if (! keyResponse.ok) {
                throw new Error('Announcement alerts are not configured yet.');
            }

            const keyData = await keyResponse.json();
            publicKey = keyData.publicKey;
            pushRegistration = await navigator.serviceWorker.ready;
            currentSubscription = await pushRegistration.pushManager.getSubscription();

            if (currentSubscription) {
                await syncSubscription(currentSubscription);
                pushToggleButton.classList.add('hidden');
            } else if (Notification.permission === 'granted') {
                await subscribeCurrentBrowser();
            }
        } catch (error) {
            pushToggleButton.disabled = true;
            pushToggleButton.title = 'Offline alerts unavailable';
            pushToggleButton.setAttribute('aria-label', 'Offline alerts unavailable');
            setStatus(error.message);
        }
    };

    pushToggleButton.addEventListener('click', async () => {
        pushToggleButton.disabled = true;

        try {
            if (currentSubscription) {
                const endpoint = currentSubscription.endpoint;
                await currentSubscription.unsubscribe();
                await sendPushRequest(announcementPushWidget.dataset.unsubscribeUrl, { endpoint });
                currentSubscription = null;
                pushUnreadCount = 0;
                renderUnreadCount();
                pushToggleButton.title = 'Enable offline alerts';
                pushToggleButton.setAttribute('aria-label', 'Enable offline alerts');
                setStatus('Offline alerts turned off.');
            } else {
                const permission = await Notification.requestPermission();

                if (permission !== 'granted') {
                    setStatus('Allow notifications in your browser to receive announcements.');

                    return;
                }

                await subscribeCurrentBrowser();
            }
        } catch (error) {
            setStatus(error.message || 'Could not update offline alerts.');
        } finally {
            pushToggleButton.disabled = false;
        }
    });

    markReadButton.addEventListener('click', async () => {
        try {
            if (currentSubscription) {
                await sendPushRequest(announcementPushWidget.dataset.markReadUrl, {
                    endpoint: currentSubscription.endpoint,
                });
            }

            pushUnreadCount = 0;
            markPublicAnnouncementsRead();
            setStatus('Announcements marked as read.');
        } catch (error) {
            setStatus('Could not update announcement alerts. Please try again.');
        }
    });

    navigator.serviceWorker.addEventListener('message', (event) => {
        if (event.data?.type === 'announcement-push' && currentSubscription) {
            pushUnreadCount = Math.max(0, Number(event.data.unreadCount) || 0);
            renderUnreadCount();
        }
    });

    initializePushWidget();
}

const waitForInstallPrompt = (timeoutMs = 2500) => new Promise((resolve) => {
    if (pwa.deferredPrompt) {
        resolve(pwa.deferredPrompt);

        return;
    }

    const finish = (promptEvent) => {
        window.removeEventListener('beforeinstallprompt', onPrompt);
        window.clearTimeout(timer);
        resolve(promptEvent ?? null);
    };

    const onPrompt = (event) => {
        event.preventDefault();
        pwa.deferredPrompt = event;
        finish(event);
    };

    const timer = window.setTimeout(() => finish(pwa.deferredPrompt), timeoutMs);

    window.addEventListener('beforeinstallprompt', onPrompt);
});

const showPwaInstallHelp = () => {
    const isSecure = window.isSecureContext;
    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);
    const isSafari = /^((?!chrome|android).)*safari/i.test(window.navigator.userAgent);

    let steps = `
        <ol class="mt-3 list-decimal space-y-1 pl-5 text-left text-sm text-gray-600">
            <li>Open this page in <strong>Chrome</strong> or <strong>Edge</strong>.</li>
            <li>Tap the browser menu (⋮).</li>
            <li>Choose <strong>Add to Home screen</strong> or <strong>Install app</strong>.</li>
        </ol>
    `;

    if (isIos || isSafari) {
        steps = `
            <ol class="mt-3 list-decimal space-y-1 pl-5 text-left text-sm text-gray-600">
                <li>Tap the <strong>Share</strong> button.</li>
                <li>Choose <strong>Add to Home Screen</strong>.</li>
                <li>Tap <strong>Add</strong>.</li>
            </ol>
        `;
    }

    const securityNote = isSecure
        ? ''
        : `<p class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-left text-sm text-amber-800">
                Open this page again using
                <strong>https://${window.location.host}</strong>
                so your phone can save it.
           </p>`;

    return Swal.fire({
        title: 'Add this to your phone',
        html: `
            <p class="text-sm text-gray-600">
                You can save this website on your phone, like an app. Then you can open it from your home screen.
            </p>
            ${securityNote}
            ${steps}
        `,
        icon: 'info',
        confirmButtonText: 'Got it',
        confirmButtonColor: '#094F29',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn-primary',
            htmlContainer: 'text-sm',
        },
    });
};

const actionButtonSelector = [
    '.btn-primary',
    '.btn-secondary',
    '.btn-danger',
    '.btn-warning',
    '.btn-accent',
    '.profile-dropdown-item',
].join(', ');

const hasConfirmGuard = (element) => element.matches([
    '[data-confirm-delete]',
    '[data-confirm-archive]',
    '[data-confirm-restore]',
    '[data-confirm-deactivate]',
    '[data-confirm-logout]',
].join(', '));

const shouldSkipButtonLoading = (element) => {
    if (! element) {
        return true;
    }

    return element.dataset.noLoading === 'true'
        || element.getAttribute('aria-disabled') === 'true'
        || element.classList.contains('filter-chip')
        || element.classList.contains('filter-chip-active')
        || element.classList.contains('tab-link')
        || element.classList.contains('tab-link-active')
        || element.classList.contains('nav-link')
        || element.classList.contains('nav-link-active')
        || element.classList.contains('btn-icon')
        || Boolean(element.closest('.swal2-popup'));
};

window.setButtonLoading = (button, { temporaryMs = null } = {}) => {
    if (! button || shouldSkipButtonLoading(button) || button.dataset.loading === 'true') {
        return false;
    }

    button.dataset.loading = 'true';
    button.classList.add('is-loading');
    button.setAttribute('aria-busy', 'true');

    if ('disabled' in button) {
        button.disabled = true;
    } else {
        button.setAttribute('aria-disabled', 'true');
        button.classList.add('pointer-events-none');
    }

    if (button instanceof HTMLInputElement) {
        if (! button.dataset.originalValue) {
            button.dataset.originalValue = button.value;
        }

        button.value = button.dataset.originalValue
            ? `${button.dataset.originalValue}…`
            : 'Please wait…';
    } else {
        if (! button.dataset.originalHtml) {
            button.dataset.originalHtml = button.innerHTML;
        }

        const label = (button.textContent || '').trim() || 'Please wait';
        button.replaceChildren();

        const spinner = document.createElement('span');
        spinner.className = 'btn-spinner';
        spinner.setAttribute('aria-hidden', 'true');

        const text = document.createElement('span');
        text.textContent = label;

        button.append(spinner, text);
    }

    if (temporaryMs) {
        window.setTimeout(() => {
            window.clearButtonLoading(button);
        }, temporaryMs);
    }

    return true;
};

window.clearButtonLoading = (button) => {
    if (! button || button.dataset.loading !== 'true') {
        return;
    }

    button.dataset.loading = 'false';
    button.classList.remove('is-loading', 'pointer-events-none');
    button.removeAttribute('aria-busy');

    if ('disabled' in button) {
        button.disabled = false;
    }

    button.removeAttribute('aria-disabled');

    if (button instanceof HTMLInputElement) {
        if (button.dataset.originalValue) {
            button.value = button.dataset.originalValue;
            delete button.dataset.originalValue;
        }
    } else if (button.dataset.originalHtml) {
        button.innerHTML = button.dataset.originalHtml;
        delete button.dataset.originalHtml;
    }
};

window.lockFormButtons = (form) => {
    if (! form) {
        return;
    }

    form.dataset.submitting = 'true';

    form.querySelectorAll('button[type="submit"], input[type="submit"], button:not([type])').forEach((button) => {
        window.setButtonLoading(button);
    });
};

window.confirmAction = (event, { title = 'Are you sure?', text = 'This action cannot be undone.', confirmText = 'Yes, continue' }) => {
    event.preventDefault();

    const form = event.target.closest('form');
    const trigger = event.target.closest('button, input[type="submit"]');

    if (! form) {
        return;
    }

    Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#094F29',
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn-primary mx-1',
            cancelButton: 'btn-secondary mx-1',
            actions: 'gap-2',
        },
    }).then((result) => {
        if (result.isConfirmed) {
            if (trigger) {
                window.setButtonLoading(trigger);
            }

            window.lockFormButtons(form);
            form.submit();
        }
    });
};

const initButtonLoadingGuards = () => {
    document.addEventListener('click', (event) => {
        const button = event.target.closest('button, a, input[type="submit"]');

        if (! button || shouldSkipButtonLoading(button) || hasConfirmGuard(button)) {
            return;
        }

        if (button.dataset.loading === 'true' || button.disabled) {
            event.preventDefault();
            event.stopImmediatePropagation();

            return;
        }

        const form = button.form || button.closest('form');
        const isSubmitButton = button.matches('button[type="submit"], input[type="submit"], button:not([type])');
        const isLink = button.tagName === 'A' && button.hasAttribute('href');
        const isActionButton = button.matches(actionButtonSelector);

        if (isSubmitButton && form) {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                event.stopImmediatePropagation();

                return;
            }

            // Do not disable the submit button here. Disabling the submitter during
            // the click event cancels the browser's default form submission.
            // Loading state is applied in the submit handler instead.

            return;
        }

        if (isLink && isActionButton) {
            const href = button.getAttribute('href');

            if (! href || href.startsWith('#') || href.startsWith('javascript:')) {
                return;
            }

            if (button.target === '_blank' || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            window.setButtonLoading(button, { temporaryMs: 1000 });

            return;
        }

        if (button.tagName === 'BUTTON' && button.type === 'button' && isActionButton) {
            window.setButtonLoading(button, { temporaryMs: 1000 });
        }
    }, true);

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (! (form instanceof HTMLFormElement) || form.dataset.loadingSkip === 'true') {
            return;
        }

        form.dataset.submitting = 'true';
        window.lockFormButtons(form);
    }, true);
};

window.confirmDelete = (event, message = 'This action cannot be undone.') => {
    window.confirmAction(event, {
        title: 'Are you sure?',
        text: message,
        confirmText: 'Yes, delete it',
    });
};

window.confirmArchive = (event, message = 'This record will be archived.') => {
    window.confirmAction(event, {
        title: 'Archive record?',
        text: message,
        confirmText: 'Yes, archive it',
    });
};

window.confirmRestore = (event, message = 'This record will be restored to the active list.') => {
    window.confirmAction(event, {
        title: 'Restore record?',
        text: message,
        confirmText: 'Yes, restore it',
    });
};

window.confirmDeactivate = (event, message = 'This account will be set to inactive.') => {
    window.confirmAction(event, {
        title: 'Deactivate account?',
        text: message,
        confirmText: 'Yes, deactivate',
    });
};

window.confirmLogout = (event, message = 'You will be signed out of SanFran HealthSys.') => {
    event.preventDefault();

    const form = event.target.closest('form');
    const trigger = event.target.closest('button, input[type="submit"]');

    if (! form) {
        return;
    }

    Swal.fire({
        title: 'Log out?',
        text: message,
        showCancelButton: true,
        confirmButtonText: 'Log out',
        cancelButtonText: 'Stay signed in',
        reverseButtons: true,
        focusCancel: true,
        buttonsStyling: false,
        width: '26rem',
        customClass: {
            popup: 'swal-logout-popup',
            title: 'swal-logout-title',
            htmlContainer: 'swal-logout-body',
            actions: 'swal-logout-actions',
            confirmButton: 'btn-primary swal-logout-confirm',
            cancelButton: 'btn-secondary swal-logout-cancel',
        },
        didOpen: (popup) => {
            const title = popup.querySelector('.swal2-title');

            if (! title) {
                return;
            }

            const mark = document.createElement('div');
            mark.className = 'swal-logout-mark';
            mark.setAttribute('aria-hidden', 'true');
            mark.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9l3 3m0 0-3 3m3-3H9" />
                </svg>
            `;
            title.before(mark);
        },
    }).then((result) => {
        if (result.isConfirmed) {
            if (trigger) {
                window.setButtonLoading(trigger);
            }

            window.lockFormButtons(form);
            form.submit();
        }
    });
};

document.addEventListener('alpine:init', () => {
    Alpine.data('recordSearchSuggestions', (endpoint) => ({
        suggestions: [],
        open: false,
        activeIndex: -1,
        error: false,
        controller: null,

        async fetchSuggestions(value) {
            const query = value.trim();

            if (! query) {
                this.close();
                return;
            }

            this.controller?.abort();
            this.controller = new AbortController();
            this.error = false;

            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('q', query);

                const response = await fetch(url, {
                    headers: { Accept: 'application/json' },
                    signal: this.controller.signal,
                });

                if (! response.ok) {
                    throw new Error(`Suggestion request failed with status ${response.status}`);
                }

                const data = await response.json();
                this.suggestions = data.suggestions ?? [];
                this.activeIndex = -1;
                this.open = this.suggestions.length > 0;
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                console.error('Unable to load record search suggestions.', error);
                this.suggestions = [];
                this.open = false;
                this.error = true;
            }
        },

        move(direction) {
            if (! this.suggestions.length) {
                return;
            }

            this.open = true;
            this.activeIndex = (this.activeIndex + direction + this.suggestions.length) % this.suggestions.length;
        },

        selectActive(event) {
            if (this.activeIndex >= 0) {
                event.preventDefault();
                this.select(this.suggestions[this.activeIndex]);
            }
        },

        select(suggestion) {
            this.$refs.search.value = suggestion.value;
            this.close();
            this.$refs.search.form?.requestSubmit();
        },

        close() {
            this.controller?.abort();
            this.controller = null;
            this.open = false;
            this.activeIndex = -1;
            this.suggestions = [];
            this.error = false;
        },
    }));

    Alpine.store('modal', {
        active: null,
        open(name) {
            this.active = name;
            document.body.classList.add('modal-open');
        },
        close() {
            this.active = null;
            document.body.classList.remove('modal-open');
        },
    });

    Alpine.data('doctorMascot', (catalog = null) => ({
        catalog: catalog ?? { welcome: '', categories: [], questions: [] },
        lookX: 0,
        lookY: 0,
        examining: false,
        open: false,
        expanded: false,
        query: '',
        activeCategory: 'all',
        messages: [],
        messageSeq: 0,
        canScrollCatsLeft: false,
        canScrollCatsRight: false,
        posX: 0,
        posY: 0,
        dropX: 0,
        dropY: 0,
        walkMs: 0,
        walking: false,
        dragging: false,
        facingLeft: false,
        ignoreClick: false,
        dragArmed: false,
        dragStartX: 0,
        dragStartY: 0,
        walkTimer: null,
        onPointerMove: null,
        onPointerUp: null,
        onEscape: null,
        onResize: null,

        init() {
            this.placeDefault();

            this.onPointerMove = (event) => {
                if (this.dragArmed) {
                    this.onMascotPointerMove(event);
                }

                this.trackEyes(event);
            };

            this.onPointerUp = (event) => {
                if (this.dragArmed) {
                    this.onMascotPointerUp(event);
                }
            };

            this.onEscape = (event) => {
                if (event.key === 'Escape' && this.open) {
                    this.closeHelp();
                }
            };

            this.onResize = () => {
                this.clampPosition();
            };

            window.addEventListener('pointermove', this.onPointerMove, { passive: true });
            window.addEventListener('mousemove', this.onPointerMove, { passive: true });
            window.addEventListener('pointerup', this.onPointerUp);
            window.addEventListener('mouseup', this.onPointerUp);
            window.addEventListener('keydown', this.onEscape);
            window.addEventListener('resize', this.onResize);
        },

        destroy() {
            if (this.onPointerMove) {
                window.removeEventListener('pointermove', this.onPointerMove);
                window.removeEventListener('mousemove', this.onPointerMove);
            }

            if (this.onPointerUp) {
                window.removeEventListener('pointerup', this.onPointerUp);
                window.removeEventListener('mouseup', this.onPointerUp);
            }

            if (this.onEscape) {
                window.removeEventListener('keydown', this.onEscape);
            }

            if (this.onResize) {
                window.removeEventListener('resize', this.onResize);
            }

            if (this.walkTimer) {
                window.clearTimeout(this.walkTimer);
            }

            document.body.classList.remove('modal-open');
        },

        get rootStyle() {
            if (this.expanded) {
                return {
                    '--doctor-walk-ms': '0ms',
                    left: '0px',
                    top: '0px',
                };
            }

            return {
                '--doctor-walk-ms': `${this.walkMs}ms`,
                left: `${this.posX}px`,
                top: `${this.posY}px`,
            };
        },

        get filteredQuestions() {
            const query = this.query.trim().toLowerCase();

            return this.catalog.questions.filter((item) => {
                const inCategory = this.activeCategory === 'all' || item.category === this.activeCategory;

                if (! inCategory) {
                    return false;
                }

                if (query === '') {
                    return true;
                }

                const haystack = [item.question, item.answer, ...(item.keywords ?? [])].join(' ').toLowerCase();

                return haystack.includes(query);
            });
        },

        get showQuestionList() {
            return this.messages.length <= 1 || this.query.trim() !== '' || this.activeCategory !== 'all';
        },

        questionById(id) {
            return this.catalog.questions.find((item) => item.id === id) ?? null;
        },

        relatedQuestions(ids) {
            return (ids ?? [])
                .map((id) => this.questionById(id))
                .filter(Boolean);
        },

        placeDefault() {
            const margin = 16;

            this.posX = Math.max(margin, window.innerWidth - margin);
            this.posY = Math.max(margin, window.innerHeight - margin);
        },

        clampedPoint(x, y) {
            const margin = 16;
            const mascot = this.$refs.trigger;
            const width = this.open
                ? (this.$el?.offsetWidth || 320)
                : (mascot?.offsetWidth || 120);
            const height = this.open
                ? (this.$el?.offsetHeight || 200)
                : (mascot?.offsetHeight || 170);

            return {
                x: Math.min(window.innerWidth - margin, Math.max(width + margin, x)),
                y: Math.min(window.innerHeight - margin, Math.max(height + margin, y)),
            };
        },

        clampPosition() {
            const next = this.clampedPoint(this.posX, this.posY);
            this.posX = next.x;
            this.posY = next.y;
        },

        walkTo(x, y) {
            const next = this.clampedPoint(x, y);
            const distance = Math.hypot(next.x - this.posX, next.y - this.posY);

            if (distance < 10) {
                return;
            }

            this.facingLeft = next.x < this.posX;

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.posX = next.x;
                this.posY = next.y;

                return;
            }

            this.walkMs = Math.min(2200, Math.max(480, distance * 1.4));
            this.walking = true;
            this.examining = false;

            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    this.posX = next.x;
                    this.posY = next.y;
                });
            });

            if (this.walkTimer) {
                window.clearTimeout(this.walkTimer);
            }

            this.walkTimer = window.setTimeout(() => {
                this.walking = false;
                this.facingLeft = false;
                this.walkMs = 0;
            }, this.walkMs + 50);
        },

        onMascotPointerDown(event) {
            if (event.button !== 0) {
                return;
            }

            this.dragArmed = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.dragging = false;
            this.dropX = event.clientX;
            this.dropY = event.clientY;

            if (event.pointerId != null && event.currentTarget?.setPointerCapture) {
                event.currentTarget.setPointerCapture(event.pointerId);
            }
        },

        onMascotPointerMove(event) {
            if (! this.dragArmed) {
                return;
            }

            const distance = Math.hypot(event.clientX - this.dragStartX, event.clientY - this.dragStartY);

            if (! this.dragging && distance > 8) {
                this.dragging = true;
            }

            if (this.dragging) {
                this.dropX = event.clientX;
                this.dropY = event.clientY;
            }
        },

        onMascotPointerUp(event) {
            if (! this.dragArmed) {
                return;
            }

            this.dragArmed = false;

            if (this.dragging) {
                this.walkTo(event.clientX, event.clientY);
                this.dragging = false;
                this.ignoreClick = true;
            }
        },

        onMascotPointerCancel() {
            this.dragArmed = false;

            if (this.dragging) {
                this.ignoreClick = true;
            }

            this.dragging = false;
        },

        onMascotClick(event) {
            if (this.ignoreClick || this.walking) {
                event.preventDefault();
                this.ignoreClick = false;

                return;
            }

            this.toggleHelp();
        },

        trackEyes(event) {
            const face = this.$refs.face;

            if (! face || this.dragging) {
                return;
            }

            const rect = face.getBoundingClientRect();
            const centerX = rect.left + (rect.width / 2);
            const centerY = rect.top + (rect.height / 2);
            const deltaX = event.clientX - centerX;
            const deltaY = event.clientY - centerY;
            const maxOffset = 3.2;
            const range = 140;

            this.lookX = Math.max(-maxOffset, Math.min(maxOffset, (deltaX / range) * maxOffset));
            this.lookY = Math.max(-maxOffset, Math.min(maxOffset, (deltaY / range) * maxOffset));
        },

        toggleHelp() {
            if (this.open) {
                this.closeHelp();

                return;
            }

            this.openHelp();
        },

        toggleExpanded() {
            this.expanded = ! this.expanded;
            document.body.classList.toggle('modal-open', this.expanded);

            if (! this.expanded) {
                this.$nextTick(() => this.clampPosition());
            }
        },

        openHelp() {
            this.open = true;
            this.examining = true;

            if (this.messages.length === 0) {
                this.pushMessage('doctor', this.catalog.welcome, []);
            }

            this.$nextTick(() => {
                this.updateCatScroll();
                this.clampPosition();

                if (window.matchMedia('(min-width: 640px)').matches) {
                    this.$refs.search?.focus();
                }
            });
        },

        closeHelp() {
            this.open = false;
            this.examining = false;
            this.expanded = false;
            this.query = '';
            document.body.classList.remove('modal-open');
            this.$nextTick(() => this.clampPosition());
        },

        setCategory(id) {
            this.activeCategory = id;
            this.$nextTick(() => {
                const active = this.$refs.categories?.querySelector('.is-active');

                active?.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
                this.updateCatScroll();
            });
        },

        updateCatScroll() {
            const row = this.$refs.categories;

            if (! row) {
                this.canScrollCatsLeft = false;
                this.canScrollCatsRight = false;

                return;
            }

            this.canScrollCatsLeft = row.scrollLeft > 4;
            this.canScrollCatsRight = row.scrollLeft + row.clientWidth < row.scrollWidth - 4;
        },

        scrollCats(delta) {
            this.$refs.categories?.scrollBy({ left: delta, behavior: 'smooth' });
        },

        onCategoryWheel(event) {
            const row = event.currentTarget;

            if (! row || event.deltaY === 0) {
                return;
            }

            event.preventDefault();
            row.scrollLeft += event.deltaY;
            this.updateCatScroll();
        },

        browseAll() {
            this.query = '';
            this.activeCategory = 'all';
        },

        ask(id) {
            const item = this.questionById(id);

            if (! item) {
                return;
            }

            this.query = '';
            this.pushMessage('user', item.question, []);
            const answer = this.pushMessage('doctor', item.answer, item.related ?? []);
            this.examining = true;
            this.scrollToAnswer(answer.id);
        },

        pushMessage(role, text, related) {
            this.messageSeq += 1;
            const message = {
                id: this.messageSeq,
                role,
                text,
                related,
            };
            this.messages.push(message);

            return message;
        },

        scrollToAnswer(id) {
            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    const thread = this.$refs.thread;
                    const target = thread?.querySelector(`[data-faq-message="${id}"]`);

                    if (! thread || ! target) {
                        return;
                    }

                    const threadRect = thread.getBoundingClientRect();
                    const targetRect = target.getBoundingClientRect();
                    const top = thread.scrollTop + (targetRect.top - threadRect.top) - 12;
                    const max = Math.max(0, thread.scrollHeight - thread.clientHeight);

                    thread.scrollTo({
                        top: Math.max(0, Math.min(top, max)),
                        behavior: 'smooth',
                    });
                });
            });
        },
    }));

    Alpine.data('pwaInstall', () => ({
        isStandalone: pwa.isStandalone,
        canPrompt: Boolean(pwa.deferredPrompt),
        installing: false,
        privacyOpen: false,
        privacyDetailsOpen: false,
        privacyRead: false,
        privacyAccepted: false,
        get buttonTitle() {
            return 'Save this website on your phone, like an app.';
        },
        init() {
            window.addEventListener('pwa:prompt-ready', () => {
                this.canPrompt = true;
            });

            window.addEventListener('pwa:installed', () => {
                this.isStandalone = true;
                this.installing = false;
                this.canPrompt = false;
            });

            window.addEventListener('appinstalled', () => {
                this.isStandalone = true;
                this.installing = false;
                this.canPrompt = false;
            });
        },
        beginInstall() {
            if (this.isStandalone || this.installing) {
                return;
            }

            this.privacyRead = false;
            this.privacyAccepted = false;
            this.privacyOpen = true;
            document.body.classList.add('modal-open');
        },
        openPrivacyDetails() {
            this.privacyDetailsOpen = true;
            this.$nextTick(() => this.checkPrivacyDetails());
        },
        checkPrivacyDetails(event = null) {
            const panel = event?.target ?? this.$refs.privacyDetails;

            if (! panel) {
                return;
            }

            if (panel.scrollHeight <= panel.clientHeight + 2
                || panel.scrollTop + panel.clientHeight >= panel.scrollHeight - 4) {
                this.privacyRead = true;
            }
        },
        closePrivacyDetails() {
            this.privacyDetailsOpen = false;
        },
        closePrivacy() {
            this.privacyDetailsOpen = false;
            this.privacyOpen = false;
            this.privacyAccepted = false;
            document.body.classList.remove('modal-open');
        },
        handlePrivacyEscape() {
            if (this.privacyDetailsOpen) {
                this.closePrivacyDetails();

                return;
            }

            this.closePrivacy();
        },
        async continueInstall() {
            if (! this.privacyRead || ! this.privacyAccepted || this.installing) {
                return;
            }

            this.closePrivacy();
            await this.install();
        },
        async install() {
            if (this.isStandalone || this.installing) {
                return;
            }

            this.installing = true;

            try {
                const promptEvent = pwa.deferredPrompt ?? await waitForInstallPrompt();

                if (! promptEvent) {
                    await showPwaInstallHelp();

                    return;
                }

                pwa.deferredPrompt = null;
                this.canPrompt = false;
                promptEvent.prompt();
                await promptEvent.userChoice;
            } finally {
                this.installing = false;
            }
        },
    }));
});

const initScrollReveal = () => {
    const elements = document.querySelectorAll('.reveal-on-scroll');

    if (elements.length === 0) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        elements.forEach((element) => element.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.08,
            rootMargin: '0px 0px -24px 0px',
        },
    );

    elements.forEach((element) => observer.observe(element));
};

const initSystemReveal = () => {
    const revealTargets = [];

    document.querySelectorAll('.card').forEach((card) => {
        if (! card.classList.contains('reveal-on-scroll')) {
            card.classList.add('reveal-on-scroll', 'reveal-up');
        }
        revealTargets.push(card);
    });

    document.querySelectorAll('[role="alert"]').forEach((alert) => {
        if (! alert.classList.contains('reveal-on-scroll')) {
            alert.classList.add('reveal-on-scroll', 'reveal-fade');
        }
        revealTargets.push(alert);
    });

    document.querySelectorAll('.page-content > *, main.page-content > *').forEach((section) => {
        if (section.matches('script, style, template')) {
            return;
        }

        if (! section.classList.contains('reveal-on-scroll') && ! section.querySelector('.reveal-on-scroll')) {
            section.classList.add('reveal-on-scroll', 'reveal-up');
            revealTargets.push(section);
        }
    });

    document.querySelectorAll('.chip-scroll .filter-chip, .chip-scroll .filter-chip-active').forEach((chip, index) => {
        chip.classList.add('reveal-stagger');
        chip.style.setProperty('--reveal-delay', `${Math.min(index * 40, 360)}ms`);
    });

    revealTargets.forEach((element, index) => {
        if (! element.style.getPropertyValue('--reveal-delay')) {
            element.style.setProperty('--reveal-delay', `${Math.min(index * 55, 440)}ms`);
        }
    });
};

const initTableRowReveal = () => {
    document.querySelectorAll('tbody').forEach((tbody) => {
        tbody.querySelectorAll('tr').forEach((row, index) => {
            row.classList.add('reveal-row');
            row.style.setProperty('--reveal-delay', `${Math.min(index * 45, 400)}ms`);
        });
    });

    const rows = document.querySelectorAll('tbody tr.reveal-row');

    if (rows.length === 0) {
        return;
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        rows.forEach((row) => row.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.08, rootMargin: '0px 0px -20px 0px' },
    );

    rows.forEach((row) => observer.observe(row));
};

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('input', (event) => {
        const input = event.target;

        if (!(input instanceof HTMLInputElement) || !input.matches('[data-digits-only]')) {
            return;
        }

        const cursor = input.selectionStart ?? input.value.length;
        const removedBeforeCursor = input.value.slice(0, cursor).replace(/[0-9]/g, '').length;
        const digits = input.value.replace(/\D/g, '').slice(0, 11);

        if (input.value !== digits) {
            input.value = digits;
            const nextCursor = Math.min(cursor - removedBeforeCursor, digits.length);
            input.setSelectionRange(nextCursor, nextCursor);
        }
    });

    document.querySelectorAll('[data-confirm-delete]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.dataset.confirmDelete || 'This action cannot be undone.';
            window.confirmDelete(event, message);
        });
    });

    document.querySelectorAll('[data-confirm-archive]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.dataset.confirmArchive || 'This record will be archived.';
            window.confirmArchive(event, message);
        });
    });

    document.querySelectorAll('[data-confirm-restore]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.dataset.confirmRestore || 'This record will be restored to the active list.';
            window.confirmRestore(event, message);
        });
    });

    document.querySelectorAll('[data-confirm-deactivate]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.dataset.confirmDeactivate || 'This account will be set to inactive.';
            window.confirmDeactivate(event, message);
        });
    });

    document.querySelectorAll('[data-confirm-logout]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.dataset.confirmLogout || 'You will be signed out of SanFran HealthSys.';
            window.confirmLogout(event, message);
        });
    });

    const interactiveSelector = [
        '.btn-primary',
        '.btn-secondary',
        '.btn-danger',
        '.btn-warning',
        '.btn-accent',
        '.btn-icon',
        '.nav-link',
        '.nav-link-active',
        '.filter-chip',
        '.filter-chip-active',
        '.tab-link',
        '.tab-link-active',
        '.profile-dropdown-item',
    ].join(', ');

    document.addEventListener('click', (event) => {
        const target = event.target.closest(interactiveSelector);

        if (! target || target.disabled) {
            return;
        }

        const rect = target.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 1.6;
        const ripple = document.createElement('span');
        ripple.className = 'btn-ripple';
        ripple.style.width = `${size}px`;
        ripple.style.height = `${size}px`;
        ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
        ripple.style.top = `${event.clientY - rect.top - size / 2}px`;

        target.appendChild(ripple);

        window.setTimeout(() => ripple.remove(), 700);
    });

    initSystemReveal();
    initScrollReveal();
    initTableRowReveal();
    initButtonLoadingGuards();
});

Alpine.start();
