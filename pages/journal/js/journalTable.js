document.addEventListener("DOMContentLoaded", () => {

    const table = document.getElementById("journalTable");
    if (!table) return;

    /********************************************
     *  COLUMN REORDERING (SortableJS)
     ********************************************/
    const headerRow = document.getElementById("sortable-header");
    if (headerRow) {
        new Sortable(headerRow, {
            animation: 150,
            handle: ".drag-icon",
            ghostClass: "sortable-ghost",

            onEnd: function () {
                const ths = Array.from(headerRow.children);
                const newOrder = ths.map(th => th.dataset.field);

                fetch("/Http/RequestHandler.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ columnOrder: newOrder })
                })
                .then(() => window.location.reload())
                .catch(err => console.error("Column order save failed:", err));
            }
        });
    }


    /********************************************
     *  COLUMN RESIZING
     ********************************************/
    (function enableColumnResizing() {
        let startX, startWidth, currentTh;

        document.querySelectorAll("th .resize-handle").forEach(handle => {
            handle.addEventListener("mousedown", e => {
                currentTh = e.target.closest("th");
                startX = e.pageX;
                startWidth = currentTh.offsetWidth;

                document.addEventListener("mousemove", resizeColumn);
                document.addEventListener("mouseup", stopResize);

                e.preventDefault();
            });
        });

        function resizeColumn(e) {
            const newWidth = startWidth + (e.pageX - startX);
            currentTh.style.width = newWidth + "px";

            const index = Array.from(currentTh.parentNode.children).indexOf(currentTh);

            document.querySelectorAll("#journalTable tbody tr").forEach(row => {
                row.cells[index].style.width = newWidth + "px";
            });
        }

        function stopResize() {
            document.removeEventListener("mousemove", resizeColumn);
            document.removeEventListener("mouseup", stopResize);

            const fieldName = currentTh.dataset.field;

            fetch("/Http/RequestHandler.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    updateWidth: {
                        field: fieldName,
                        width: currentTh.offsetWidth
                    }
                })
            })
            .catch(err => console.error("Failed to save width:", err));
        }
    })();


    /********************************************
     *  INLINE EDITING SYSTEM
     ********************************************/
    
    // Central click handler for text & dropdown editing
    table.addEventListener("click", event => {
        const cell = event.target.closest("td");
        if (!cell || !cell.closest("tbody")) return;
        if (cell.classList.contains("is-editing")) return;

        const hasDropdown = cell.dataset.hasDropdown === "1";

        if (hasDropdown) startDropdownEdit(cell);
        else startTextEdit(cell);
    });


    /***********************
     * TEXT EDIT
     ***********************/
    function startTextEdit(cell) {
        const editor = cell.querySelector(".inline-editor");
        if (!editor) return;

        cell.classList.add("is-editing");

        const originalValue = editor.textContent.trim();
        editor.setAttribute("contenteditable", "true");
        editor.focus();
        placeCaretAtEnd(editor);

        function onKeydown(e) {
            if (e.key === "Enter") { e.preventDefault(); editor.blur(); }
            if (e.key === "Escape") { e.preventDefault(); cancel(); }
        }

        function onBlur() { save(); }

        editor.addEventListener("keydown", onKeydown);
        editor.addEventListener("blur", onBlur);

        function cleanup() {
            editor.removeEventListener("keydown", onKeydown);
            editor.removeEventListener("blur", onBlur);
            editor.setAttribute("contenteditable", "false");
            cell.classList.remove("is-editing");
        }

        function cancel() {
            editor.textContent = originalValue;
            cleanup();
        }

        function save() {
            const newValue = editor.textContent.trim();
            if (newValue === originalValue) return cleanup();

            const { id, field } = getCellMeta(cell);
            if (!id || !field) return cleanup();

            cell.classList.add("saving");

            sendUpdate(id, field, newValue)
                .then(ok => {
                    cell.classList.remove("saving");
                    if (!ok) {
                        editor.textContent = originalValue;
                        cell.classList.add("error");
                    } else {
                        cell.classList.add("saved");
                        setTimeout(() => cell.classList.remove("saved"), 900);
                    }
                })
                .finally(cleanup);
        }
    }


    /***********************
     * DROPDOWN EDIT
     ***********************/
    function startDropdownEdit(cell) {
        const editor = cell.querySelector(".inline-editor");
        if (!editor) return;

        cell.classList.add("is-editing");

        const originalValue = editor.textContent.trim();
        const { id, field } = getCellMeta(cell);
        if (!id || !field) return;

        let options = [];
        try {
            options = JSON.parse(cell.dataset.options || "[]");
        } catch (e) {
            console.error("Bad dropdown JSON:", e);
            return;
        }

        // Hide editor while menu is open
        editor.style.visibility = "hidden";

        // Create floating dropdown menu
        const menu = document.createElement("div");
        menu.className = "dropdown-menu";

        options.forEach(opt => {
            const item = document.createElement("div");
            item.className = "dropdown-option";
            item.textContent = opt;

            item.addEventListener("click", () => {
                pickOption(opt);
            });

            menu.appendChild(item);
        });

        // Append menu to body for perfect free-floating overlay
        document.body.appendChild(menu);

        // Position floating menu at cell's coordinates
        const rect = cell.getBoundingClientRect();
        menu.style.left = rect.left + "px";
        menu.style.top = rect.bottom + "px";


        /* --- Close menu helper --- */
        function closeMenu(commit = false, newValue = originalValue) {
            menu.remove();
            editor.style.visibility = "";
            cell.classList.remove("is-editing");

            if (!commit) return;

            cell.classList.add("saving");

            sendUpdate(id, field, newValue)
                .then(ok => {
                    cell.classList.remove("saving");

                    if (ok) {
                        editor.textContent = newValue;
                        cell.classList.add("saved");
                        setTimeout(() => cell.classList.remove("saved"), 900);
                    } else {
                        cell.classList.add("error");
                    }
                });
        }


        /* --- Option selection --- */
        function pickOption(val) {
            closeMenu(true, val);
        }


        /* --- Close dropdown when clicking outside --- */
        function onClickOutside(e) {
            if (!menu.contains(e.target) && !cell.contains(e.target)) {
                closeMenu(false);
            }
        }

        /* --- Close on Escape key --- */
        function onEscape(e) {
            if (e.key === "Escape") {
                closeMenu(false);
            }
        }

        document.addEventListener("mousedown", onClickOutside);
        document.addEventListener("keydown", onEscape, { once: true });

        // Clean up listeners when closing
        const originalCloseMenu = closeMenu;
        closeMenu = function(...args) {
            document.removeEventListener("mousedown", onClickOutside);
            document.removeEventListener("keydown", onEscape);
            originalCloseMenu(...args);
        };
    }

    /********************************************
     *  UTILITIES
     ********************************************/
    function sendUpdate(id, field, value) {
        return fetch("/Http/RequestHandler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                updateCell: { id, field, value }
            })
        })
        .then(r => r.json())
        .then(d => d?.status === "ok")
        .catch(err => {
            console.error("Update error:", err);
            return false;
        });
    }

    function getCellMeta(cell) {
        return {
            id: cell.dataset.id,
            field: cell.dataset.field
        };
    }

    function placeCaretAtEnd(el) {
        const range = document.createRange();
        const sel = window.getSelection();
        range.selectNodeContents(el);
        range.collapse(false);
        sel.removeAllRanges();
        sel.addRange(range);
    }
});
