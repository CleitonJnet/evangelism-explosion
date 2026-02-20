import Sortable from "sortablejs";

const boardInstances = new WeakMap();

function toNumber(value) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
}

function findComponentId(...elements) {
    const root = elements.find(Boolean)?.closest('[wire\\:id]');

    return root?.getAttribute("wire:id") || null;
}

export function initStpApproachesSortable(root = document) {
    if (!root) {
        return;
    }

    const lists = [];

    if (root instanceof Element && root.matches(".js-stp-approach-list")) {
        lists.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(".js-stp-approach-list").forEach((list) => {
            lists.push(list);
        });
    }

    lists.forEach((list) => {
        if (boardInstances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            group: "stp-approaches",
            draggable: ".js-stp-approach-item",
            animation: 150,
            onEnd: (evt) => {
                const itemEl = evt.item;
                const fromList = evt.from;
                const toList = evt.to;

                if (!itemEl || !fromList || !toList) {
                    return;
                }

                const approachId = toNumber(itemEl.dataset.approachId);
                const toContainer = toList.dataset.container || "";
                const fromContainer = fromList.dataset.container || "";
                const newIndex = typeof evt.newIndex === "number" ? evt.newIndex : 0;

                if (!approachId || !toContainer || !fromContainer) {
                    return;
                }

                const componentId = findComponentId(toList, itemEl);

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call(
                    "moveApproach",
                    approachId,
                    toContainer,
                    newIndex,
                    fromContainer
                );
            },
        });

        boardInstances.set(list, sortable);
    });
}

document.addEventListener("livewire:init", () => {
    initStpApproachesSortable(document);

    if (typeof Livewire === "undefined" || !Livewire.hook) {
        return;
    }

    Livewire.hook("morph.updated", ({ el }) => {
        initStpApproachesSortable(el);
    });
});
