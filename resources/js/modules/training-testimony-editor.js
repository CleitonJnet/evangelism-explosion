const editorRoots = "[data-testimony-editor-root]";

let tiptapModulesPromise = null;

function loadTiptapModules() {
    if (tiptapModulesPromise) {
        return tiptapModulesPromise;
    }

    const globalModulesPromise = window.__trainingTestimonyTiptap;

    if (globalModulesPromise) {
        tiptapModulesPromise = globalModulesPromise;
        return tiptapModulesPromise;
    }

    tiptapModulesPromise = Promise.all([
        import("https://esm.sh/@tiptap/core@2"),
        import("https://esm.sh/@tiptap/starter-kit@2"),
        import("https://esm.sh/@tiptap/extension-underline@2"),
        import("https://esm.sh/@tiptap/extension-text-style@2"),
        import("https://esm.sh/@tiptap/extension-color@2"),
        import("https://esm.sh/@tiptap/extension-text-align@2"),
    ]).then(([core, starterKit, underline, textStyle, color, textAlign]) => {
        return {
            ...core,
            StarterKit: starterKit.default,
            Underline: underline.default,
            TextStyle: textStyle.default,
            Color: color.default,
            TextAlign: textAlign.default,
        };
    });

    return tiptapModulesPromise;
}

function createStyleExtensions({ Extension }) {
    const FontSize = Extension.create({
        name: "fontSize",
        addOptions() {
            return {
                types: ["textStyle"],
            };
        },
        addCommands() {
            return {
                setFontSize:
                    (fontSize) =>
                    ({ chain }) => {
                        return chain().setMark("textStyle", { fontSize }).run();
                    },
                unsetFontSize:
                    () =>
                    ({ chain }) => {
                        return chain()
                            .setMark("textStyle", { fontSize: null })
                            .removeEmptyTextStyle()
                            .run();
                    },
            };
        },
        addGlobalAttributes() {
            return [
                {
                    types: this.options.types,
                    attributes: {
                        fontSize: {
                            default: null,
                            parseHTML: (element) => element.style.fontSize || null,
                            renderHTML: (attributes) => {
                                if (!attributes.fontSize) {
                                    return {};
                                }

                                return {
                                    style: `font-size: ${attributes.fontSize}`,
                                };
                            },
                        },
                    },
                },
            ];
        },
    });

    const FontFamily = Extension.create({
        name: "fontFamily",
        addOptions() {
            return {
                types: ["textStyle"],
            };
        },
        addCommands() {
            return {
                setFontFamily:
                    (fontFamily) =>
                    ({ chain }) => {
                        return chain().setMark("textStyle", { fontFamily }).run();
                    },
                unsetFontFamily:
                    () =>
                    ({ chain }) => {
                        return chain()
                            .setMark("textStyle", { fontFamily: null })
                            .removeEmptyTextStyle()
                            .run();
                    },
            };
        },
        addGlobalAttributes() {
            return [
                {
                    types: this.options.types,
                    attributes: {
                        fontFamily: {
                            default: null,
                            parseHTML: (element) => element.style.fontFamily || null,
                            renderHTML: (attributes) => {
                                if (!attributes.fontFamily) {
                                    return {};
                                }

                                return {
                                    style: `font-family: ${attributes.fontFamily}`,
                                };
                            },
                        },
                    },
                },
            ];
        },
    });

    return { FontSize, FontFamily };
}

function sanitizeTextLength(text) {
    return text.replace(/\s+/g, " ").trim().length;
}

function updateToolbarState(root, editor) {
    const textStyleAttributes = editor.getAttributes("textStyle");
    const headingSelect = root.querySelector('[data-tiptap-select="heading"]');
    const fontSizeSelect = root.querySelector('[data-tiptap-select="font-size"]');
    const fontFamilySelect = root.querySelector('[data-tiptap-select="font-family"]');
    const colorInput = root.querySelector('[data-tiptap-input="color"]');

    root.querySelectorAll("[data-tiptap-action]").forEach((button) => {
        const action = button.dataset.tiptapAction;
        let active = false;

        switch (action) {
            case "bold":
            case "italic":
            case "underline":
            case "strike":
            case "blockquote":
            case "bullet-list":
            case "ordered-list":
                active = editor.isActive(
                    action === "bullet-list"
                        ? "bulletList"
                        : action === "ordered-list"
                          ? "orderedList"
                          : action
                );
                break;
            case "align-left":
                active = editor.isActive({ textAlign: "left" });
                break;
            case "align-center":
                active = editor.isActive({ textAlign: "center" });
                break;
            case "align-right":
                active = editor.isActive({ textAlign: "right" });
                break;
            case "align-justify":
                active = editor.isActive({ textAlign: "justify" });
                break;
            default:
                break;
        }

        button.classList.toggle("is-active", active);
    });

    if (headingSelect) {
        if (editor.isActive("heading", { level: 2 })) {
            headingSelect.value = "h2";
        } else if (editor.isActive("heading", { level: 3 })) {
            headingSelect.value = "h3";
        } else {
            headingSelect.value = "paragraph";
        }
    }

    if (fontSizeSelect) {
        fontSizeSelect.value = textStyleAttributes.fontSize || "16px";
    }

    if (fontFamilySelect) {
        fontFamilySelect.value = textStyleAttributes.fontFamily || "inherit";
    }

    if (colorInput) {
        colorInput.value = textStyleAttributes.color || "#0f172a";
    }
}

function syncCounter(counter, textLength) {
    if (!counter) {
        return;
    }

    const limit = Number(counter.dataset.limit || 0);

    counter.textContent = `${textLength} / ${limit}`;
    counter.classList.toggle("text-red-600", textLength > limit);
    counter.classList.toggle("text-slate-600", textLength <= limit);
}

function handleAction(editor, action) {
    const chain = editor.chain().focus();

    switch (action) {
        case "bold":
            chain.toggleBold().run();
            break;
        case "italic":
            chain.toggleItalic().run();
            break;
        case "underline":
            chain.toggleUnderline().run();
            break;
        case "strike":
            chain.toggleStrike().run();
            break;
        case "blockquote":
            chain.toggleBlockquote().run();
            break;
        case "bullet-list":
            chain.toggleBulletList().run();
            break;
        case "ordered-list":
            chain.toggleOrderedList().run();
            break;
        case "align-left":
            chain.setTextAlign("left").run();
            break;
        case "align-center":
            chain.setTextAlign("center").run();
            break;
        case "align-right":
            chain.setTextAlign("right").run();
            break;
        case "align-justify":
            chain.setTextAlign("justify").run();
            break;
        case "undo":
            chain.undo().run();
            break;
        case "redo":
            chain.redo().run();
            break;
        default:
            break;
    }
}

function rememberSelection(root, editor) {
    const { from, to } = editor.state.selection;
    root.__trainingTestimonySelection = { from, to };

    if (from !== to) {
        root.__trainingTestimonyRangeSelection = { from, to };
    }
}

function chainWithSavedSelection(root, editor) {
    const chain = editor.chain().focus();
    const currentSelection = editor.state.selection;
    const savedSelection =
        currentSelection.from !== currentSelection.to
            ? { from: currentSelection.from, to: currentSelection.to }
            : root.__trainingTestimonyRangeSelection || root.__trainingTestimonySelection;

    if (!savedSelection) {
        return chain;
    }

    const maxPosition = editor.state.doc.content.size;
    const from = Math.max(1, Math.min(savedSelection.from, maxPosition));
    const to = Math.max(from, Math.min(savedSelection.to, maxPosition));

    return chain.setTextSelection({ from, to });
}

function destroyEditor(root) {
    root.__trainingTestimonyInitializing = false;

    if (root.__trainingTestimonyEditor) {
        root.__trainingTestimonyEditor.destroy();
        root.__trainingTestimonyEditor = null;
    }

    root.__trainingTestimonySelection = null;
    root.__trainingTestimonyRangeSelection = null;

    if (root.__trainingTestimonySubmitHandler) {
        const form = root.closest("form");

        if (form) {
            form.removeEventListener("submit", root.__trainingTestimonySubmitHandler);
        }

        root.__trainingTestimonySubmitHandler = null;
    }
}

async function initEditor(root) {
    if (root.__trainingTestimonyEditor || root.__trainingTestimonyInitializing) {
        return;
    }

    root.__trainingTestimonyInitializing = true;

    const form = root.closest("form");
    const hiddenInput = form?.querySelector('input[name="notes"]');
    const counter = form?.querySelector("#testimony-counter");
    const surface = root.querySelector('[data-tiptap-surface="editor"]');

    if (!form || !hiddenInput || !counter || !surface) {
        root.__trainingTestimonyInitializing = false;
        return;
    }

    let editor = null;

    try {
        const modules = await loadTiptapModules();
        const {
            Editor,
            Extension,
            StarterKit,
            Underline,
            TextStyle,
            Color,
            TextAlign,
        } = modules;
        const { FontSize, FontFamily } = createStyleExtensions({ Extension });

        editor = new Editor({
            element: surface,
            content: hiddenInput.value || "",
            extensions: [
                StarterKit.configure({
                    heading: {
                        levels: [2, 3],
                    },
                }),
                Underline,
                TextStyle,
                Color,
                TextAlign.configure({
                    types: ["heading", "paragraph"],
                }),
                FontSize,
                FontFamily,
            ],
            onCreate: ({ editor: editorInstance }) => {
                rememberSelection(root, editorInstance);
                const textLength = sanitizeTextLength(editorInstance.getText());
                syncCounter(counter, textLength);
                updateToolbarState(root, editorInstance);

                if (root.dataset.autofocus === "true") {
                    requestAnimationFrame(() => {
                        editorInstance.commands.focus("end");
                    });
                }
            },
            onUpdate: ({ editor: editorInstance }) => {
                hiddenInput.value = editorInstance.isEmpty
                    ? ""
                    : editorInstance.getHTML().trim();

                const textLength = sanitizeTextLength(editorInstance.getText());
                syncCounter(counter, textLength);
                updateToolbarState(root, editorInstance);
            },
            onSelectionUpdate: ({ editor: editorInstance }) => {
                rememberSelection(root, editorInstance);
                updateToolbarState(root, editorInstance);
            },
        });
    } catch (error) {
        root.__trainingTestimonyInitializing = false;
        throw error;
    }

    root.__trainingTestimonyEditor = editor;
    root.__trainingTestimonyInitializing = false;

    root.querySelectorAll("[data-tiptap-action]").forEach((button) => {
        button.addEventListener("pointerdown", () => {
            rememberSelection(root, editor);
        });

        button.addEventListener("click", () => {
            handleAction(editor, button.dataset.tiptapAction);
            updateToolbarState(root, editor);
        });
    });

    const headingSelect = root.querySelector('[data-tiptap-select="heading"]');
    if (headingSelect) {
        headingSelect.addEventListener("pointerdown", () => {
            rememberSelection(root, editor);
        });

        headingSelect.addEventListener("change", (event) => {
            const value = event.target.value;
            const chain = chainWithSavedSelection(root, editor);

            if (value === "h2" || value === "h3") {
                chain.toggleHeading({ level: Number(value.slice(1)) }).run();
                return;
            }

            chain.setParagraph().run();
        });
    }

    const fontSizeSelect = root.querySelector('[data-tiptap-select="font-size"]');
    if (fontSizeSelect) {
        fontSizeSelect.addEventListener("pointerdown", () => {
            rememberSelection(root, editor);
        });

        fontSizeSelect.addEventListener("change", (event) => {
            const value = event.target.value;
            chainWithSavedSelection(root, editor).setFontSize(value).run();
        });
    }

    const fontFamilySelect = root.querySelector('[data-tiptap-select="font-family"]');
    if (fontFamilySelect) {
        fontFamilySelect.addEventListener("pointerdown", () => {
            rememberSelection(root, editor);
        });

        fontFamilySelect.addEventListener("change", (event) => {
            const value = event.target.value;

            if (value === "inherit") {
                chainWithSavedSelection(root, editor).unsetFontFamily().run();
                return;
            }

            chainWithSavedSelection(root, editor).setFontFamily(value).run();
        });
    }

    const colorInput = root.querySelector('[data-tiptap-input="color"]');
    if (colorInput) {
        colorInput.addEventListener("pointerdown", () => {
            rememberSelection(root, editor);
        });

        colorInput.addEventListener("input", (event) => {
            chainWithSavedSelection(root, editor).setColor(event.target.value).run();
        });
    }

    const submitHandler = (event) => {
        const limit = Number(counter.dataset.limit || 0);
        const textLength = sanitizeTextLength(editor.getText());

        hiddenInput.value = editor.isEmpty ? "" : editor.getHTML().trim();
        syncCounter(counter, textLength);

        if (textLength > limit) {
            event.preventDefault();
        }
    };

    root.__trainingTestimonySubmitHandler = submitHandler;
    form.addEventListener("submit", submitHandler);
}

export function initTrainingTestimonyEditors() {
    document.querySelectorAll(editorRoots).forEach((root) => {
        initEditor(root).catch((error) => {
            console.error("Failed to initialize testimony editor", error);
        });
    });
}

export function destroyTrainingTestimonyEditors() {
    document.querySelectorAll(editorRoots).forEach((root) => {
        destroyEditor(root);
    });
}
