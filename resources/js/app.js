import "./modules/postal_code-mask";
import "./modules/tel-mask";
import "./schedule-sortable";
import "./statistics-sortable";
import "./testimonials-sortable";
import { initDropdowns } from "./modules/dropdowns";
import { initHeader } from "./modules/navigation";

function initNavigation() {
    initHeader();
    initDropdowns();
}

// DOM ready
document.addEventListener("DOMContentLoaded", initNavigation);

// Livewire v3 SPA navigation hook (if using)
document.addEventListener("livewire:navigated", initNavigation);
