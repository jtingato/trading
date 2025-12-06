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
        const originalValue = editor.textContent.trim();

        cell.classList.add("is-editing");
        editor.setAttribute("contenteditable", "true");
        editor.focus();
        placeCaretAtEnd(editor);

        function onKeydown(e) {
            if (e.key === "Enter") { e.preventDefault(); editor.blur(); }
            if (e.key === "Escape") cancel();
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
        const originalValue = editor.textContent.trim();
        const { id, field } = getCellMeta(cell);

        let options = JSON.parse(cell.dataset.options || "[]");

        cell.classList.add("is-editing");
        editor.style.visibility = "hidden";

        const menu = document.createElement("div");
        menu.className = "dropdown-menu";

        /* Existing options */
        options.forEach(opt => {
            const item = document.createElement("div");
            item.className = "dropdown-option";
            item.textContent = opt;
            item.addEventListener("click", () => pickOption(opt));
            menu.appendChild(item);
        });

        /* Create new option row */
        const newRow = document.createElement("div");
        newRow.className = "dropdown-new-option";
        newRow.textContent = "➕ Create new option…";
        newRow.addEventListener("click", showNewOptionInput);
        menu.appendChild(newRow);

        document.body.appendChild(menu);

        const rect = cell.getBoundingClientRect();
        menu.style.left = rect.left + "px";
        menu.style.top = rect.bottom + "px";

        function showNewOptionInput() {
            const wrapper = document.createElement("div");

            const input = document.createElement("input");
            input.className = "dropdown-new-input";
            input.placeholder = "Enter new option…";

            wrapper.appendChild(input);
            newRow.replaceWith(wrapper);

            input.focus();

            input.addEventListener("keydown", e => {
                if (e.key === "Enter") {
                    const val = input.value.trim();
                    if (val !== "") pickOption(val, true);
                }
                if (e.key === "Escape") closeMenu(false);
            });
        }

        function pickOption(val, isNew = false) {

            if (isNew) {
                saveDropdownOption(field, val).then(ok => {
                    if (ok) {
                        options.push(val);
                        cell.dataset.options = JSON.stringify(options);
                    }
                });
            }

            closeMenu(true, val);
        }

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

        document.addEventListener("mousedown", e => {
            if (!menu.contains(e.target) && !cell.contains(e.target)) closeMenu(false);
        }, { once: true });
    }


    /********************************************
     *  UTILITIES
     ********************************************/
    function sendUpdate(id, field, value) {
        console.log("🔵 sendUpdate()", { id, field, value });

        return fetch("/Http/RequestHandler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ updateCell: { id, field, value } })
        })
        .then(async (r) => {
            const text = await r.text();
            console.log("🔵 RAW RESPONSE:", text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("❌ JSON parse error:", e);
                alert("Save failed: bad JSON response from server");
                return false;
            }

            console.log("🔵 Parsed JSON:", data);

            if (!data || data.status !== "ok") {
                console.error("❌ Server error:", data);
                if (data && data.message) {
                    alert("Save failed: " + data.message);
                } else {
                    alert("Save failed: unknown server error");
                }
                return false;
            }

            return true;
        })
        .catch(err => {
            console.error("❌ Network or fetch error:", err);
            alert("Save failed: network or server error");
            return false;
        });
    }

    function saveDropdownOption(field, value) {
        console.log("🔵 saveDropdownOption()", { field, value });

        return fetch("/Http/RequestHandler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                addDropdownOption: { field, value }
            })
        })
        .then(async (r) => {
            const text = await r.text();
            console.log("🔵 RAW DROPDOWN RESPONSE:", text);

            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("❌ JSON parse error (dropdown):", e);
                alert("Failed to save dropdown option: bad JSON response");
                return false;
            }

            if (!data || data.status !== "ok") {
                console.error("❌ Dropdown save error:", data);
                if (data && data.message) {
                    alert("Failed to save dropdown option: " + data.message);
                } else {
                    alert("Failed to save dropdown option");
                }
                return false;
            }

            return true;
        })
        .catch(err => {
            console.error("❌ Dropdown option save error:", err);
            alert("Failed to save dropdown option: network/server error");
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
        range.selectNodeContents(el);
        range.collapse(false);
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }
});
