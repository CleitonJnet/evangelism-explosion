@php
    $initialToasts = [];

    foreach (['success', 'error', 'warning', 'info'] as $type) {
        $message = session($type);

        if (is_string($message) && trim($message) !== '') {
            $initialToasts[] = ['type' => $type, 'message' => $message];
        }
    }

    $statusMessage = session('status');

    if (is_string($statusMessage) && trim($statusMessage) !== '') {
        $message = $statusMessage === 'verification-link-sent'
            ? __('A new verification link has been sent to your email address.')
            : $statusMessage;

        $initialToasts[] = ['type' => 'success', 'message' => $message];
    }
@endphp

<div x-data="eeToastStack(@js($initialToasts))" x-init="init()"
    class="pointer-events-none fixed right-0 top-0 z-[9999] flex w-full max-w-sm flex-col gap-3 p-4">
    <template x-for="toast in toasts" :key="toast.id">
        <div role="alert"
            class="pointer-events-auto flex w-full items-center gap-3 rounded-lg border px-4 py-3 shadow-lg transition will-change-transform"
            :class="toastClasses(toast.type)"
            x-transition:enter="transform-gpu transition duration-300 ease-out"
            x-transition:enter-start="translate-y-[-8px] scale-[0.98] opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transform-gpu transition duration-250 ease-in"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-[-6px] scale-[0.98] opacity-0">
            <div class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                :class="iconContainerClasses(toast.type)">
                <svg x-show="toast.type === 'success'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.2 7.26a1 1 0 0 1-1.42-.006l-3.2-3.27a1 1 0 0 1 1.43-1.404l2.49 2.543 6.49-6.543a1 1 0 0 1 1.404.006Z"
                        clip-rule="evenodd" />
                </svg>

                <svg x-show="toast.type === 'error'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm2.53-10.78a.75.75 0 0 0-1.06-1.06L10 7.69 8.53 6.22a.75.75 0 1 0-1.06 1.06L8.94 8.75 7.47 10.22a.75.75 0 0 0 1.06 1.06L10 9.81l1.47 1.47a.75.75 0 0 0 1.06-1.06L11.06 8.75l1.47-1.47Z"
                        clip-rule="evenodd" />
                </svg>

                <svg x-show="toast.type === 'warning'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.72-1.36 3.485 0l6.516 11.583c.75 1.333-.213 2.992-1.742 2.992H3.483c-1.53 0-2.492-1.66-1.742-2.992L8.257 3.1ZM11 13a1 1 0 1 0-2 0 1 1 0 0 0 2 0Zm-1-6a1 1 0 0 0-1 1v3a1 1 0 1 0 2 0V8a1 1 0 0 0-1-1Z"
                        clip-rule="evenodd" />
                </svg>

                <svg x-show="toast.type === 'info'" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm-1 3a1 1 0 0 0-1 1v4a1 1 0 1 0 2 0v-4a1 1 0 0 0-1-1Z"
                        clip-rule="evenodd" />
                </svg>
            </div>

            <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>

            <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition"
                :class="closeButtonClasses(toast.type)" @click="remove(toast.id)">
                <span class="sr-only">{{ __('Fechar') }}</span>
                <svg class="h-4 w-4" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true">
                    <path d="M1 1L13 13M13 1L1 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </button>
        </div>
    </template>
</div>

@once
    @push('js')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('eeToastStack', (initialToasts = []) => ({
                    toasts: [],
                    nextId: 0,
                    recentSignatures: {},

                    init() {
                        initialToasts.forEach((toast) => this.push(toast));

                        window.addEventListener('toast', (event) => {
                            const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;

                            this.push({
                                type: detail?.type ?? 'info',
                                message: detail?.message ?? '',
                            });
                        });
                    },

                    push(toast) {
                        const message = String(toast?.message ?? '').trim();

                        if (message === '') {
                            return;
                        }

                        const type = ['success', 'error', 'warning', 'info'].includes(toast?.type)
                            ? toast.type
                            : 'info';
                        const signature = `${type}:${message}`;
                        const now = Date.now();

                        if (this.toasts.some((item) => `${item.type}:${item.message}` === signature)) {
                            return;
                        }

                        if ((this.recentSignatures[signature] ?? 0) > now - 750) {
                            return;
                        }

                        const id = ++this.nextId;
                        this.recentSignatures[signature] = now;

                        this.toasts.push({
                            id,
                            type,
                            message,
                        });

                        window.setTimeout(() => this.remove(id), 5000);
                    },

                    remove(id) {
                        this.toasts = this.toasts.filter((toast) => toast.id !== id);
                    },

                    toastClasses(type) {
                        return {
                            success: 'border-emerald-200 bg-white text-emerald-900 dark:border-emerald-800/60 dark:bg-neutral-900 dark:text-emerald-100',
                            error: 'border-red-200 bg-white text-red-900 dark:border-red-800/60 dark:bg-neutral-900 dark:text-red-100',
                            warning: 'border-amber-200 bg-white text-amber-900 dark:border-amber-800/60 dark:bg-neutral-900 dark:text-amber-100',
                            info: 'border-sky-200 bg-white text-sky-900 dark:border-sky-800/60 dark:bg-neutral-900 dark:text-sky-100',
                        }[type] ?? 'border-slate-200 bg-white text-slate-900 dark:border-slate-700 dark:bg-neutral-900 dark:text-slate-100';
                    },

                    iconContainerClasses(type) {
                        return {
                            success: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300',
                            error: 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-300',
                            warning: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
                            info: 'bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300',
                        }[type] ?? 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300';
                    },

                    closeButtonClasses(type) {
                        return {
                            success: 'text-emerald-500 hover:bg-emerald-100 hover:text-emerald-700 dark:text-emerald-300 dark:hover:bg-emerald-900/50',
                            error: 'text-red-500 hover:bg-red-100 hover:text-red-700 dark:text-red-300 dark:hover:bg-red-900/50',
                            warning: 'text-amber-500 hover:bg-amber-100 hover:text-amber-700 dark:text-amber-300 dark:hover:bg-amber-900/50',
                            info: 'text-sky-500 hover:bg-sky-100 hover:text-sky-700 dark:text-sky-300 dark:hover:bg-sky-900/50',
                        }[type] ?? 'text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800';
                    },
                }));
            });
        </script>
    @endpush
@endonce
