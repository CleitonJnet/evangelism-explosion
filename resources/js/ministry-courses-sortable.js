import Sortable from "sortablejs";

const instances = new WeakMap();

export function initMinistryCoursesSortable(root = document) {
    if (!root) {
        return;
    }

    const lists = [];

    if (root instanceof Element && root.matches(".js-ministry-course-list")) {
        lists.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(".js-ministry-course-list").forEach((list) => {
            lists.push(list);
        });
    }

    lists.forEach((list) => {
        if (instances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            group: "ministry-courses",
            handle: ".js-course-drag-handle",
            draggable: ".js-ministry-course-item",
            animation: 150,
            onEnd: (evt) => {
                const itemEl = evt.item;
                const toList = evt.to;

                if (!itemEl || !toList) {
                    return;
                }

                const courseId = Number(itemEl.dataset.itemId);
                const targetExecution = Number(toList.dataset.execution);
                const newIndex =
                    typeof evt.newIndex === "number" ? evt.newIndex : 0;

                if (!Number.isFinite(courseId) || !Number.isFinite(targetExecution)) {
                    return;
                }

                let afterCourseId = null;

                if (newIndex > 0) {
                    const previousEl = toList.children[newIndex - 1];
                    const previousId = Number(previousEl?.dataset?.itemId);
                    afterCourseId = Number.isFinite(previousId)
                        ? previousId
                        : null;
                }

                const componentRoot =
                    toList.closest('[wire\\:id]') || itemEl.closest('[wire\\:id]');
                const componentId = componentRoot?.getAttribute("wire:id");

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call(
                    "moveCourseAfter",
                    courseId,
                    targetExecution,
                    afterCourseId
                );
            },
        });

        instances.set(list, sortable);
    });
}

document.addEventListener("livewire:init", () => {
    initMinistryCoursesSortable(document);

    if (typeof Livewire === "undefined" || !Livewire.hook) {
        return;
    }

    Livewire.hook("morph.updated", ({ el }) => {
        initMinistryCoursesSortable(el);
    });
});
