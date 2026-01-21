import "./modules/postal_code-mask";
import "./modules/tel-mask";

// DOM ready
document.addEventListener("DOMContentLoaded", initNavigation);

// Livewire v3 SPA navigation hook (if using)
document.addEventListener("livewire:navigated", initNavigation);
