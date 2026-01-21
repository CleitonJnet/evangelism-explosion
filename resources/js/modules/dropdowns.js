/**
 * Dropdown behavior (hover + focus + ESC)
 *
 * - Idempotent per dropdown
 * - Livewire-friendly (re-inits on SPA navigations)
 */
export function initDropdowns() {
    const dropdowns = document.querySelectorAll("[data-dropdown]");

    dropdowns.forEach((dropdown) => {
        if (dropdown.dataset.dropdownInit === "1") return;
        dropdown.dataset.dropdownInit = "1";

        const toggle = dropdown.querySelector("[data-dropdown-toggle]");
        const menu = dropdown.querySelector("[data-dropdown-menu]");
        if (!toggle || !menu) return;

        let isInside = false;
        let closeTimeout;

        const openMenu = () => {
            clearTimeout(closeTimeout);
            dropdown.classList.add("is-open");
        };

        const closeMenu = () => {
            clearTimeout(closeTimeout);
            closeTimeout = window.setTimeout(() => {
                if (!isInside) dropdown.classList.remove("is-open");
            }, 180);
        };

        const enter = () => {
            isInside = true;
            openMenu();
        };

        const leave = () => {
            isInside = false;
            closeMenu();
        };

        // Hover title
        toggle.addEventListener("mouseenter", enter);
        toggle.addEventListener("mouseleave", leave);

        // Hover submenu
        menu.addEventListener("mouseenter", enter);
        menu.addEventListener("mouseleave", leave);

        // Focus a11y
        toggle.addEventListener("focus", openMenu);
        toggle.addEventListener("blur", closeMenu);

        // ESC closes dropdown
        toggle.addEventListener("keydown", (e) => {
            if (e.key === "Escape") dropdown.classList.remove("is-open");
        });
    });
}
