import "./modules/postal_code-mask";
import "./modules/tel-mask";
import "./schedule-sortable";

// DOM ready
document.addEventListener("DOMContentLoaded", initNavigation);

// Livewire v3 SPA navigation hook (if using)
document.addEventListener("livewire:navigated", initNavigation);
