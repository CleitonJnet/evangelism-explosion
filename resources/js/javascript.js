import "./modules/contact-whatsapp";
import "./modules/postal_code-mask";
import "./modules/tel-mask";

import { initHeader } from "./modules/navigation";
import { initDropdowns } from "./modules/dropdowns";

export function initNavigation() {
    initHeader();
    initDropdowns();
}

// DOM ready
document.addEventListener("DOMContentLoaded", initNavigation);

// Livewire v3 SPA navigation hook (if using)
document.addEventListener("livewire:navigated", initNavigation);
