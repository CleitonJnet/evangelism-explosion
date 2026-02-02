import Sortable from "sortablejs";

const instances = new WeakMap();

export function initScheduleSortable(root = document) {
    if (!root) {
        return;
    }

    const lists = [];

    if (root instanceof Element && root.matches(".js-schedule-day-list")) {
        lists.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(".js-schedule-day-list").forEach((list) => {
            lists.push(list);
        });
    }

    lists.forEach((list) => {
        if (instances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            group: "training-schedule",
            handle: ".js-drag-handle",
            draggable: ".js-schedule-item",
            animation: 150,
            onEnd: (evt) => {
                const itemEl = evt.item;
                const toList = evt.to;

                if (!itemEl || !toList) {
                    return;
                }

                const id = Number(itemEl.dataset.itemId);
                const dateKey = String(toList.dataset.dateKey || "");
                const dayStart = String(toList.dataset.dayStart || "");
                const newIndex = typeof evt.newIndex === "number" ? evt.newIndex : 0;

                if (!Number.isFinite(id) || !dateKey || !dayStart) {
                    return;
                }

                let startsAt = dayStart;

                if (toList.children.length === 0) {
                    startsAt = dayStart;
                } else if (newIndex === 0) {
                    startsAt = dayStart;
                } else {
                    const prevEl = toList.children[newIndex - 1];
                    const prevStartsAt = prevEl?.dataset?.startsAt;
                    const prevEndsAt = prevEl?.dataset?.endsAt;

                    startsAt = prevEndsAt || prevStartsAt || dayStart;
                }

                const componentRoot =
                    toList.closest('[wire\\:id]') || itemEl.closest('[wire\\:id]');
                const componentId = componentRoot?.getAttribute("wire:id");

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call("moveItem", id, dateKey, startsAt);
            },
        });

        instances.set(list, sortable);
    });
}

document.addEventListener("livewire:init", () => {
    initScheduleSortable(document);

    if (typeof Livewire === "undefined" || !Livewire.hook) {
        return;
    }

    Livewire.hook("morph.updated", ({ el }) => {
        initScheduleSortable(el);
    });
});
