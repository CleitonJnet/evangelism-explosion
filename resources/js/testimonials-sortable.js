import Sortable from "sortablejs";

const instances = new WeakMap();

export function initTestimonialsSortable(root = document) {
    if (!root) {
        return;
    }

    const lists = [];

    if (root instanceof Element && root.matches(".js-testimonials-list")) {
        lists.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(".js-testimonials-list").forEach((list) => {
            lists.push(list);
        });
    }

    lists.forEach((list) => {
        if (instances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            handle: ".js-testimonial-drag-handle",
            draggable: ".js-testimonial-item",
            animation: 150,
            onEnd: (evt) => {
                const itemEl = evt.item;
                const toList = evt.to;

                if (!itemEl || !toList) {
                    return;
                }

                const id = Number(itemEl.dataset.itemId);
                const newIndex = typeof evt.newIndex === "number" ? evt.newIndex : 0;

                if (!Number.isFinite(id)) {
                    return;
                }

                let afterItemId = null;

                if (newIndex > 0) {
                    const prevEl = toList.children[newIndex - 1];
                    const prevId = Number(prevEl?.dataset?.itemId);
                    afterItemId = Number.isFinite(prevId) ? prevId : null;
                }

                const componentRoot =
                    toList.closest('[wire\\:id]') || itemEl.closest('[wire\\:id]');
                const componentId = componentRoot?.getAttribute("wire:id");

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call("moveAfter", id, afterItemId);
            },
        });

        instances.set(list, sortable);
    });
}

document.addEventListener("livewire:init", () => {
    initTestimonialsSortable(document);

    if (typeof Livewire === "undefined" || !Livewire.hook) {
        return;
    }

    Livewire.hook("morph.updated", ({ el }) => {
        initTestimonialsSortable(el);
    });
});
