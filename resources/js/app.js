import "./modules/postal_code-mask";
import "./modules/tel-mask";
import "./schedule-sortable";
import "./statistics-sortable";
import "./stp-approaches-sortable";
import "./testimonials-sortable";
import { initDropdowns } from "./modules/dropdowns";
import { initHeader } from "./modules/navigation";
import {
    destroyTrainingTestimonyEditors,
    initTrainingTestimonyEditors,
} from "./modules/training-testimony-editor";

function initNavigation() {
    initHeader();
    initDropdowns();
    initTrainingTestimonyEditors();
}

// DOM ready
document.addEventListener("DOMContentLoaded", initNavigation);

// Livewire v3 SPA navigation hook (if using)
document.addEventListener("livewire:navigated", initNavigation);
document.addEventListener("livewire:navigating", destroyTrainingTestimonyEditors);
