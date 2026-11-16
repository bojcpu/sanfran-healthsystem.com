<div x-data="pwaInstall" class="contents">
    <button
        type="button"
        {{ $attributes->merge(['class' => 'btn-secondary']) }}
        x-show="! isStandalone"
        x-cloak
        data-no-loading="true"
        :disabled="installing"
        :title="buttonTitle"
        @click="beginInstall()"
    >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9.75v6.75m0 0-3-3m3 3 3-3m-8.25 6a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
        </svg>
        <span x-text="installing ? 'Adding…' : 'Add to phone'">Add to phone</span>
    </button>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="privacyOpen"
            class="modal-root fixed inset-0 z-[90] overflow-y-auto px-4 py-6 sm:px-6"
            role="presentation"
            @keydown.escape.window="handlePrivacyEscape()"
        >
            <div class="modal-backdrop-premium fixed inset-0 bg-gray-900/65 backdrop-blur-sm" @click="closePrivacy()"></div>

            <div class="relative mx-auto flex min-h-full items-center justify-center">
                <section
                    x-show="privacyOpen"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="pwa-privacy-title"
                    aria-describedby="pwa-privacy-description"
                    class="modal-panel-premium relative w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-2xl shadow-primary/10"
                    @click.stop
                >
                    <div class="flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-5 py-4 sm:px-6">
                        <div>
                            <h2 id="pwa-privacy-title" class="text-lg font-semibold text-gray-900">Data Privacy Notice &amp; Terms</h2>
                            <p id="pwa-privacy-description" class="mt-1 text-sm text-gray-500">Please review this notice before adding SanFran HealthSys to your phone.</p>
                        </div>
                        <button type="button" class="btn-icon shrink-0" aria-label="Close privacy notice" data-no-loading="true" @click="closePrivacy()">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4 px-5 py-5 sm:px-6">
                        <p class="text-sm leading-6 text-gray-700">
                            Adding this app saves a shortcut to your device and allows the browser to keep public app files available for offline use. Read the full notice before continuing.
                        </p>

                        <button type="button" class="btn-secondary text-sm" data-no-loading="true" @click="openPrivacyDetails()">
                            Read full privacy details
                        </button>

                        <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                            <input
                                type="checkbox"
                                x-model="privacyAccepted"
                                :disabled="! privacyRead"
                                class="mt-1 rounded border-gray-300 text-primary focus:ring-primary disabled:cursor-not-allowed disabled:opacity-50"
                            >
                            <span class="text-sm leading-5 text-gray-700">I have read and understood the Data Privacy Notice and Terms.</span>
                        </label>

                        <p x-show="! privacyRead" class="text-xs text-gray-500">
                            Open the full details and scroll to the end before checking this box.
                        </p>

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-4">
                            <button type="button" class="btn-secondary" data-no-loading="true" @click="closePrivacy()">Cancel</button>
                            <button
                                type="button"
                                class="btn-primary"
                                data-no-loading="true"
                                :disabled="! privacyRead || ! privacyAccepted || installing"
                                @click="continueInstall()"
                            >Continue</button>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div
            x-cloak
            x-show="privacyDetailsOpen"
            class="modal-root fixed inset-0 z-[100] overflow-y-auto px-4 py-6 sm:px-6"
            role="presentation"
            @click.self="closePrivacyDetails()"
        >
            <div class="modal-backdrop-premium fixed inset-0 bg-gray-900/75 backdrop-blur-sm" @click="closePrivacyDetails()"></div>

            <div class="relative mx-auto flex min-h-full items-center justify-center">
                <section
                    x-show="privacyDetailsOpen"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="pwa-privacy-details-title"
                    class="modal-panel-premium relative w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-2xl shadow-primary/10"
                    @click.stop
                >
                    <div class="flex items-start justify-between gap-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white px-5 py-4 sm:px-6">
                        <h2 id="pwa-privacy-details-title" class="text-lg font-semibold text-gray-900">Full Data Privacy Details</h2>
                        <button type="button" class="btn-icon shrink-0" aria-label="Close full privacy details" data-no-loading="true" @click="closePrivacyDetails()">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div
                        x-ref="privacyDetails"
                        class="max-h-[60vh] space-y-5 overflow-y-auto px-5 py-5 sm:px-6"
                        tabindex="0"
                        @scroll.passive="checkPrivacyDetails($event)"
                    >
                        <section class="space-y-2">
                            <h3 class="font-semibold text-gray-900">1. What happens when you add the app</h3>
                            <p class="text-sm leading-6 text-gray-700">
                                Adding SanFran HealthSys creates a shortcut or installed web app on your device. The browser may store public app files, such as the logo and app resources, so they can be available offline. Installing the app by itself does not submit your health information or give you access to staff-only patient records.
                            </p>
                        </section>

                        <section class="space-y-2">
                            <h3 class="font-semibold text-gray-900">2. Optional announcement notifications</h3>
                            <p class="text-sm leading-6 text-gray-700">
                                Notifications are optional and are controlled separately from installing the app. If you choose to enable them, your browser sends a push subscription address and its security keys to SanFran HealthSys. These are stored to deliver public health announcement alerts and track their unread count. The notification may include the announcement title, a short message, and a link.
                            </p>
                            <p class="text-sm leading-6 text-gray-700">
                                You can turn notifications off using the alert control on the announcement page or in your browser/device settings. Turning them off removes the saved push subscription.
                            </p>
                        </section>

                        <section class="space-y-2">
                            <h3 class="font-semibold text-gray-900">3. Information kept on your device</h3>
                            <p class="text-sm leading-6 text-gray-700">
                                Your browser may keep cached public app files and a local marker for announcements you have read. You can remove these by clearing this website's stored data in your browser settings or uninstalling the app.
                            </p>
                        </section>

                        <section class="space-y-2">
                            <h3 class="font-semibold text-gray-900">4. Protecting personal information</h3>
                            <p class="text-sm leading-6 text-gray-700">
                                This page is for public announcements. Do not send or post private patient or medical information through public announcements or notifications. Staff-only patient records remain behind the staff login.
                            </p>
                        </section>

                        <section class="space-y-2">
                            <h3 class="font-semibold text-gray-900">5. Your acknowledgment</h3>
                            <p class="text-sm leading-6 text-gray-700">
                                By checking the acknowledgment box, you confirm that you have read these details. This acknowledgment does not turn on notifications; your browser will ask permission separately if you choose to enable them.
                            </p>
                        </section>
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 sm:px-6">
                        <p class="text-xs text-gray-500" x-text="privacyRead ? 'You have reached the end of the notice.' : 'Scroll through the full notice to continue.'"></p>
                        <button type="button" class="btn-primary" data-no-loading="true" @click="closePrivacyDetails()">Close details</button>
                    </div>
                </section>
            </div>
        </div>
    </template>
</div>
