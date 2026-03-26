/**
 * Header behavior (scroll + hide/show + mobile menu)
 *
 * - Idempotent (safe to call multiple times)
 * - Livewire-friendly (re-inits on SPA navigations)
 */
export function initHeader() {
    initWebsiteHeader();
    initAppMobileHeader();
}

function initWebsiteHeader() {
    const header = document.getElementById("main-header");
    if (!header) return;
    if (header.dataset.navInit === "1") return;
    header.dataset.navInit = "1";

    const wrapper = document.getElementById("header-wrapper");
    const topBar = document.getElementById("top-bar");
    const mainBar = document.getElementById("main-bar");

    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");
    const iconHamburger = document.getElementById("icon-hamburger");
    const iconClose = document.getElementById("icon-close");

    let open = false;
    let hiddenOnScroll = false;
    let lastY = window.scrollY || 0;

    const topBarAlpha = 0.15;
    const mainBarBg = 0.2;

    function setScrolledClasses(isScrolled) {
        const shadowOn = "shadow-[0_10px_30px_rgba(0,0,0,.25)]";
        const blurOn = "backdrop-blur-lg";

        [topBar, mainBar].forEach((el) => {
            if (!el) return;
            el.classList.toggle("shadow-none", !isScrolled);
            el.classList.toggle(shadowOn, isScrolled);
            el.classList.toggle(blurOn, isScrolled);
        });
    }

    function applyOpacity(scrollY) {
        const y = Math.max(0, scrollY);
        const scrolled = y > 20;

        setScrolledClasses(scrolled);

        const t = Math.min(1, y / 200);
        const topAlpha = topBarAlpha + t * 0.85;
        const mainAlpha = mainBarBg + t * 1;

        if (topBar) {
            topBar.style.backgroundColor = `rgba(5, 47, 74,${topAlpha})`;
        }

        if (mainBar) {
            mainBar.style.backgroundColor = `rgba(5, 47, 74,${mainAlpha})`;
        }
    }

    function renderHeaderPosition() {
        if (!wrapper) return;
        wrapper.classList.toggle("-translate-y-full", hiddenOnScroll);
        wrapper.classList.toggle("translate-y-0", !hiddenOnScroll);
    }

    function renderMenu() {
        if (!mobileMenu) return;

        mobileMenu.classList.toggle("hidden", !open);
        mobileMenu.classList.toggle("flex", open);

        if (iconHamburger) {
            iconHamburger.classList.toggle("hidden", open);
        }

        if (iconClose) {
            iconClose.classList.toggle("hidden", !open);
        }

        document.documentElement.classList.toggle("overflow-hidden", open);
        document.body.classList.toggle("overflow-hidden", open);

        if (menuBtn) {
            menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
        }
    }

    function openMenu() {
        open = true;
        renderMenu();
    }

    function closeMenu() {
        open = false;
        renderMenu();
    }

    function toggleMenu() {
        open ? closeMenu() : openMenu();
    }

    if (menuBtn) {
        menuBtn.addEventListener("click", (e) => {
            e.preventDefault();
            toggleMenu();
        });
    }

    document.addEventListener("click", (e) => {
        if (!open) return;
        if (!mobileMenu || !menuBtn) return;

        const target = e.target;
        if (!(target instanceof Node)) return;

        const clickedInsideMenu = mobileMenu.contains(target);
        const clickedButton = menuBtn.contains(target);

        if (!clickedInsideMenu && !clickedButton) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && open) {
            closeMenu();
        }
    });

    document.querySelectorAll(".js-close-menu").forEach((el) => {
        if (el.dataset.navCloseInit === "1") return;
        el.dataset.navCloseInit = "1";
        el.addEventListener("click", () => {
            if (open) {
                closeMenu();
            }
        });
    });

    function onScroll() {
        const y = window.scrollY || 0;
        const delta = y - lastY;

        applyOpacity(y);

        const threshold = 8;
        if (!open) {
            if (delta > threshold && y > 80) {
                hiddenOnScroll = true;
            }

            if (delta < -threshold) {
                hiddenOnScroll = false;
            }
        } else {
            hiddenOnScroll = false;
        }

        renderHeaderPosition();
        lastY = y;
    }

    window.addEventListener("scroll", onScroll, { passive: true });

    applyOpacity(lastY);
    renderHeaderPosition();
    renderMenu();
}

function initAppMobileHeader() {
    const header = document.getElementById("app-mobile-header");
    if (!header) return;
    if (header.dataset.navInit === "1") return;
    header.dataset.navInit = "1";

    const floatingButton = document.getElementById("app-mobile-sidebar-fab");
    let lastY = window.scrollY || 0;
    let hiddenOnScroll = false;
    let accumulatedDelta = 0;
    let ticking = false;

    function renderHeaderPosition() {
        header.classList.toggle("is-hidden", hiddenOnScroll);

        if (floatingButton) {
            floatingButton.classList.toggle("is-header-hidden", hiddenOnScroll);
        }
    }

    function updateFromScroll() {
        const y = window.scrollY || 0;
        const delta = y - lastY;
        const directionChanged =
            (delta > 0 && accumulatedDelta < 0) || (delta < 0 && accumulatedDelta > 0);

        if (directionChanged) {
            accumulatedDelta = 0;
        }

        accumulatedDelta += delta;

        if (accumulatedDelta > 18 && y > 96) {
            hiddenOnScroll = true;
            accumulatedDelta = 0;
        }

        if (accumulatedDelta < -14 || y <= 24) {
            hiddenOnScroll = false;
            accumulatedDelta = 0;
        }

        renderHeaderPosition();
        lastY = y;
        ticking = false;
    }

    function onScroll() {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(updateFromScroll);
    }

    window.addEventListener("scroll", onScroll, { passive: true });
    renderHeaderPosition();
}
