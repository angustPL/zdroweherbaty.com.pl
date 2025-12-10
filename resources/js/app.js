// TinyMCE integration with Livewire
window.initTinyMCE = function (field, initialValue, element) {
    const editor = element;
    if (!editor) return;

    tinymce.init({
        target: editor,
        height: 400,
        license_key: "gpl",
        plugins: "link lists autolink visualblocks",
        toolbar:
            "undo redo | bold italic | h2 | alignleft aligncenter alignright | bullist numlist indent outdent | link | visualblocks",
        menubar: false,
        branding: false,
        skin: "oxide",
        content_style:
            "body { font-family: system-ui, -apple-system, sans-serif; }",
        setup: (editor) => {
            // Ustaw treść startową
            if (initialValue) {
                editor.setContent(initialValue);
            }

            // Przy zmianie treści -> Livewire
            editor.on("change input undo redo", () => {
                const wireId = editor
                    .getElement()
                    .closest("[wire\\:id]")
                    ?.getAttribute("wire:id");
                if (!wireId) return;
                const component = Livewire.find(wireId);
                if (!component) return;

                component.set(field, editor.getContent());
            });
        },
    });
};

// Czyszczenie instancji TinyMCE przy zamykaniu modali
window.cleanupTinyMCE = function () {
    if (
        window.tinymce &&
        tinymce.EditorManager &&
        tinymce.EditorManager.editors
    ) {
        tinymce.EditorManager.editors.forEach((editor) => {
            tinymce.remove(editor);
        });
    }
};

// Leaflet usunięty - EasyPack ma swój własny Leaflet
