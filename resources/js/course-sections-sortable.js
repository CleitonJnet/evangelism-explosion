import Sortable from "sortablejs";

const instances = new WeakMap();

export function initCourseSectionsSortable(root = document) {
    if (!root) {
        return;
    }

    const lists = [];

    if (root instanceof Element && root.matches(".js-course-sections-list")) {
        lists.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(".js-course-sections-list").forEach((list) => {
            lists.push(list);
        });
    }

    lists.forEach((list) => {
        if (instances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            handle: ".js-section-drag-handle",
            draggable: ".js-course-section-item",
            animation: 150,
            onEnd: (evt) => {
                const itemEl = evt.item;
                const toList = evt.to;

                if (!itemEl || !toList) {
                    return;
                }

                const sectionId = Number(itemEl.dataset.itemId);
                const newIndex = typeof evt.newIndex === "number" ? evt.newIndex : 0;

                if (!Number.isFinite(sectionId)) {
                    return;
                }

                let afterSectionId = null;

                if (newIndex > 0) {
                    const previousEl = toList.children[newIndex - 1];
                    const previousId = Number(previousEl?.dataset?.itemId);
                    afterSectionId = Number.isFinite(previousId) ? previousId : null;
                }

                const componentRoot =
                    toList.closest('[wire\\:id]') || itemEl.closest('[wire\\:id]');
                const componentId = componentRoot?.getAttribute("wire:id");

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call(
                    "moveSectionAfter",
                    sectionId,
                    afterSectionId
                );
            },
        });

        instances.set(list, sortable);
    });
}

document.addEventListener("livewire:init", () => {
    initCourseSectionsSortable(document);

    if (typeof Livewire === "undefined" || !Livewire.hook) {
        return;
    }

    Livewire.hook("morph.updated", ({ el }) => {
        initCourseSectionsSortable(el);
    });
});
