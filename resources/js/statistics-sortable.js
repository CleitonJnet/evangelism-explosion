import Sortable from "sortablejs";

const mentorInstances = new WeakMap();
const studentInstances = new WeakMap();

function findComponentId(...elements) {
    const root = elements.find(Boolean)?.closest('[wire\\:id]');

    return root?.getAttribute("wire:id") || null;
}

function toNumber(value) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : null;
}

export function initStatisticsSortable(root = document) {
    if (!root) {
        return;
    }

    const mentorLists = [];
    const studentLists = [];

    if (root instanceof Element && root.matches(".js-statistics-mentor-list")) {
        mentorLists.push(root);
    }

    if (root instanceof Element && root.matches(".js-statistics-student-list")) {
        studentLists.push(root);
    }

    if (root.querySelectorAll) {
        root.querySelectorAll(".js-statistics-mentor-list").forEach((list) => {
            mentorLists.push(list);
        });

        root.querySelectorAll(".js-statistics-student-list").forEach((list) => {
            studentLists.push(list);
        });
    }

    mentorLists.forEach((list) => {
        if (mentorInstances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            group: "statistics-mentors",
            draggable: ".js-statistics-mentor-item",
            handle: ".js-statistics-mentor-handle",
            animation: 150,
            onAdd: (evt) => {
                const itemEl = evt.item;
                const fromList = evt.from;
                const toList = evt.to;

                if (!itemEl || !fromList || !toList) {
                    return;
                }

                const mentorItems = Array.from(
                    toList.querySelectorAll(".js-statistics-mentor-item")
                );

                if (mentorItems.length < 2) {
                    return;
                }

                const displacedMentor = mentorItems.find((mentorEl) => mentorEl !== itemEl);

                if (!displacedMentor) {
                    return;
                }

                fromList.appendChild(displacedMentor);
            },
            onEnd: (evt) => {
                const itemEl = evt.item;
                const fromList = evt.from;
                const toList = evt.to;

                if (!itemEl || !fromList || !toList) {
                    return;
                }

                const mentorId = toNumber(itemEl.dataset.mentorId);
                const fromTeamId = toNumber(fromList.dataset.teamId);
                const toTeamId = toNumber(toList.dataset.teamId);

                if (!mentorId || !fromTeamId || !toTeamId) {
                    return;
                }

                const componentId = findComponentId(toList, itemEl);

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call(
                    "swapMentor",
                    mentorId,
                    fromTeamId,
                    toTeamId
                );
            },
        });

        mentorInstances.set(list, sortable);
    });

    studentLists.forEach((list) => {
        if (studentInstances.has(list)) {
            return;
        }

        const sortable = new Sortable(list, {
            group: "statistics-students",
            draggable: ".js-statistics-student-item",
            animation: 150,
            onEnd: (evt) => {
                const itemEl = evt.item;
                const fromList = evt.from;
                const toList = evt.to;

                if (!itemEl || !fromList || !toList) {
                    return;
                }

                const studentId = toNumber(itemEl.dataset.studentId);
                const fromTeamId = toNumber(fromList.dataset.teamId);
                const toTeamId = toNumber(toList.dataset.teamId);
                const newIndex = typeof evt.newIndex === "number" ? evt.newIndex : 0;

                if (!studentId || !fromTeamId || !toTeamId) {
                    return;
                }

                let afterStudentId = null;

                if (newIndex > 0) {
                    const previousEl = toList.children[newIndex - 1];
                    afterStudentId = toNumber(previousEl?.dataset?.studentId);
                }

                const componentId = findComponentId(toList, itemEl);

                if (!componentId || typeof Livewire === "undefined") {
                    return;
                }

                Livewire.find(componentId).call(
                    "moveStudent",
                    studentId,
                    fromTeamId,
                    toTeamId,
                    afterStudentId
                );
            },
        });

        studentInstances.set(list, sortable);
    });
}

document.addEventListener("livewire:init", () => {
    initStatisticsSortable(document);

    if (typeof Livewire === "undefined" || !Livewire.hook) {
        return;
    }

    Livewire.hook("morph.updated", ({ el }) => {
        initStatisticsSortable(el);
    });
});
