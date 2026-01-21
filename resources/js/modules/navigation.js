/**
 * Header behavior (scroll + hide/show + mobile menu)
 *
 * - Idempotent (safe to call multiple times)
 * - Livewire-friendly (re-inits on SPA navigations)
 */
export function initHeader() {
    const header = document.getElementById("main-header");
    if (!header) return;

    // Prevent duplicate bindings (for this header)
    if (header.dataset.navInit === "1") return;
    header.dataset.navInit = "1";

    const wrapper = document.getElementById("header-wrapper");
    const topBar = document.getElementById("top-bar");
    const mainBar = document.getElementById("main-bar");

    const menuBtn = document.getElementById("menu-btn");
    const mobileMenu = document.getElementById("mobile-menu");
    const iconHamburger = document.getElementById("icon-hamburger");
    const iconClose = document.getElementById("icon-close");

    // State
    let open = false;
    let hiddenOnScroll = false;

    // Scroll tracking
    let lastY = window.scrollY || 0;

    // Opacities
    let topBarAlpha = 0.15;
    let mainBarBg = 0.2;

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

        // 0..200px
        const t = Math.min(1, y / 200);

        const topAlpha = topBarAlpha + t * 0.85;
        const mainAlpha = mainBarBg + t * 1;

        if (topBar)
            topBar.style.backgroundColor = `rgba(5, 47, 74,${topAlpha})`;
        if (mainBar)
            mainBar.style.backgroundColor = `rgba(5, 47, 74,${mainAlpha})`;

        // if (topBar) topBar.style.backgroundColor = `rgba(0,0,0,${topAlpha})`;
        // if (mainBar) mainBar.style.backgroundColor = `rgba(0,0,0,${mainAlpha})`;
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

        if (iconHamburger) iconHamburger.classList.toggle("hidden", open);
        if (iconClose) iconClose.classList.toggle("hidden", !open);

        // lock scroll when menu is open
        document.documentElement.classList.toggle("overflow-hidden", open);
        document.body.classList.toggle("overflow-hidden", open);

        // a11y
        if (menuBtn)
            menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
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

    // Click menu button
    if (menuBtn) {
        menuBtn.addEventListener("click", (e) => {
            e.preventDefault();
            toggleMenu();
        });
    }

    // Click outside (mobile menu)
    document.addEventListener("click", (e) => {
        if (!open) return;
        if (!mobileMenu || !menuBtn) return;

        const target = e.target;
        if (!(target instanceof Node)) return;

        const clickedInsideMenu = mobileMenu.contains(target);
        const clickedButton = menuBtn.contains(target);

        if (!clickedInsideMenu && !clickedButton) closeMenu();
    });

    // ESC closes menu
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && open) closeMenu();
    });

    // Close on link click (use .js-close-menu)
    document.querySelectorAll(".js-close-menu").forEach((el) => {
        if (el.dataset.navCloseInit === "1") return;
        el.dataset.navCloseInit = "1";
        el.addEventListener("click", () => {
            if (open) closeMenu();
        });
    });

    // Scroll behavior (hide on down, show on up)
    function onScroll() {
        const y = window.scrollY || 0;
        const delta = y - lastY;

        applyOpacity(y);

        const TH = 8;
        if (!open) {
            if (delta > TH && y > 80) hiddenOnScroll = true;
            if (delta < -TH) hiddenOnScroll = false;
        } else {
            hiddenOnScroll = false;
        }

        renderHeaderPosition();
        lastY = y;
    }

    window.addEventListener("scroll", onScroll, { passive: true });

    // Init
    applyOpacity(lastY);
    renderHeaderPosition();
    renderMenu();
}
