<script type="module" src="{{ asset('build/assets/app-Dzr1eSXw.js') }}"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

@fluxScripts

<script>
    (() => {
        if (window.__eeMobileSidebarSwipeInitialized) {
            return;
        }

        window.__eeMobileSidebarSwipeInitialized = true;

        const mobileMediaQuery = window.matchMedia('(max-width: 1023.98px)');
        const edgeThreshold = 32;
        const horizontalThreshold = 72;
        const verticalTolerance = 56;

        let touchStartX = null;
        let touchStartY = null;
        let startTarget = null;

        const interactiveSelector = [
            'a',
            'button',
            'input',
            'select',
            'textarea',
            '[role="button"]',
            '[data-no-sidebar-swipe]',
            '[data-flux-sidebar]',
            '[data-flux-sidebar-backdrop]',
            '[data-flux-dropdown]',
            '[data-flux-modal]',
            '[contenteditable="true"]',
        ].join(', ');

        function isMobileViewport() {
            return mobileMediaQuery.matches;
        }

        function getMobileSidebar() {
            return document.querySelector('[data-flux-sidebar][collapsible="mobile"]');
        }

        function isSidebarOpen() {
            const sidebar = getMobileSidebar();

            if (!sidebar) {
                return false;
            }

            return !sidebar.hasAttribute('data-flux-sidebar-collapsed-mobile');
        }

        function openSidebar() {
            const sidebar = getMobileSidebar();

            if (sidebar instanceof HTMLElement) {
                sidebar.dispatchEvent(new CustomEvent('flux-sidebar-toggle', {
                    bubbles: true,
                }));

                return true;
            }

            const activator = document.querySelector('[data-flux-sidebar-toggle] button') ??
                document.querySelector('#app-mobile-sidebar-fab [data-flux-sidebar-collapse] button') ??
                document.querySelector('[data-flux-sidebar-toggle]') ??
                document.querySelector('[data-flux-sidebar-collapse]');

            if (activator instanceof HTMLElement) {
                activator.click();

                return true;
            }

            return false;
        }

        function shouldIgnoreSwipe(target) {
            if (!(target instanceof Element)) {
                return false;
            }

            return Boolean(target.closest(interactiveSelector));
        }

        document.addEventListener('touchstart', (event) => {
            if (!isMobileViewport() || event.touches.length !== 1) {
                touchStartX = null;
                touchStartY = null;
                startTarget = null;

                return;
            }

            const touch = event.touches[0];

            touchStartX = touch.clientX;
            touchStartY = touch.clientY;
            startTarget = event.target;
        }, {
            passive: true,
        });

        document.addEventListener('touchend', (event) => {
            if (!isMobileViewport() || touchStartX === null || touchStartY === null) {
                return;
            }

            const touch = event.changedTouches[0];
            const deltaX = touch.clientX - touchStartX;
            const deltaY = touch.clientY - touchStartY;
            const startedAtEdge = touchStartX <= edgeThreshold;
            const isHorizontalSwipe = deltaX >= horizontalThreshold && Math.abs(deltaY) <=
                verticalTolerance && Math.abs(deltaX) > Math.abs(deltaY);

            if (startedAtEdge && isHorizontalSwipe && !isSidebarOpen() && !shouldIgnoreSwipe(startTarget)) {
                openSidebar();
            }

            touchStartX = null;
            touchStartY = null;
            startTarget = null;
        }, {
            passive: true,
        });
    })();
</script>
