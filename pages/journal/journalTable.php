<?php 
declare(strict_types=1);

// Get field names in saved order (from DB)
$fieldNames = $viewModel->journalHeaderNames;

// Convert to display names
$displayNames = $viewModel->displayNamesFromFieldNames($fieldNames);
?>

<table id="journalTable" class="journal-table table table-bordered">
    <thead>
        <tr id="sortable-header">
            <?php foreach ($displayNames as $i => $label): 
                $field = $fieldNames[$i];
                $width = $viewModel->findJournalField($field)->width;
                $widthStyle = $width ? "style=\"width: {$width}px\"" : "";
            ?>
                <th 
                    data-field="<?= htmlspecialchars($field) ?>" 
                    data-index="<?= $i ?>"
                    
                    <?= $widthStyle ?>
                >
                    <i class="fas fa-grip-vertical drag-icon"></i>
                    <?= htmlspecialchars($label) ?>
                    <div class="resize-handle"></div>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($viewModel->rows as $row): ?>
            <tr data-row-id="<?= $row['id'] ?>">
                <?php foreach ($fieldNames as $field): ?>
                    <td 
                        data-field="<?= htmlspecialchars($field) ?>"
                        data-id="<?= htmlspecialchars((string)($row['id']), ENT_QUOTES) ?>"
                        data-has-dropdown="<?= isset($viewModel->dropdownFields[$field]) ? '1' : '0' ?>"
                        data-options="<?= isset($viewModel->dropdownFields[$field]) 
                            ? htmlspecialchars(json_encode($viewModel->dropdownFields[$field]), ENT_QUOTES) 
                            : '' ?>"
                    >
                        <div 
                            class="inline-editor"
                            contenteditable="<?= isset($viewModel->dropdownFields[$field]) ? 'false' : 'true' ?>"
                        >
                            <?= htmlspecialchars((string)($row[$field] ?? ''), ENT_QUOTES) ?>
                        </div>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<!--<script src="/pages/journal/tableEditing.js"></script> -->

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const headerRow = document.getElementById("sortable-header");

        new Sortable(headerRow, {
            animation: 150,
            handle: ".drag-icon",
            ghostClass: "sortable-ghost",

            onEnd: function () {

                // Get new field order
                const ths = Array.from(headerRow.children);
                const newOrder = ths.map(th => th.getAttribute("data-field"));

                // Save server-side
                fetch("/Http/RequestHandler.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ columnOrder: newOrder })
                })
                .then(() => {
                    // ⭐ CRITICAL FIX: let PHP regenerate the table after ordering change
                    window.location.reload();
                })
                .catch(err => console.error("Column order save failed:", err));
            }
        });

    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let startX, startWidth, currentTh;

        document.querySelectorAll("th .resize-handle").forEach(handle => {
            handle.addEventListener("mousedown", function (e) {
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

            const fieldName = currentTh.getAttribute("data-field");

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
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.getElementById('journalTable');
        if (!table) return;

        table.addEventListener('click', (event) => {
            const cell = event.target.closest('td');
            if (!cell || !cell.closest('tbody')) return;
            if (cell.classList.contains('is-editing')) return;

            const hasDropdown = cell.dataset.hasDropdown === '1';

            if (hasDropdown) {
                startDropdownEdit(cell);
            } else {
                startTextEdit(cell);
            }
        });

        /** --------------------------
         * TEXT EDITING
         * -------------------------- */
        function startTextEdit(cell) {
            const editor = cell.querySelector('.inline-editor');
            if (!editor) return;

            cell.classList.add('is-editing');

            const originalValue = editor.textContent.trim();
            editor.setAttribute('contenteditable', 'true');
            editor.focus();
            placeCaretAtEnd(editor);

            const onKeydown = (e) => {
                if (e.key === 'Enter') { e.preventDefault(); editor.blur(); }
                if (e.key === 'Escape') { e.preventDefault(); cancelTextEdit(cell, editor, originalValue); }
            };

            const onBlur = () => commitTextEdit(cell, editor, originalValue);

            editor.addEventListener('keydown', onKeydown);
            editor.addEventListener('blur', onBlur);

            cell._handlers = { onKeydown, onBlur };
        }

        function cancelTextEdit(cell, editor, originalValue) {
            editor.textContent = originalValue;
            cleanupCell(cell, editor);
        }

        function commitTextEdit(cell, editor, originalValue) {
            const newValue = editor.textContent.trim();
            if (newValue === originalValue) return cleanupCell(cell, editor);

            const { id, field } = getCellMeta(cell);
            if (!id || !field) return cleanupCell(cell, editor);

            cell.classList.add('saving');

            sendUpdate(id, field, newValue)
                .then(ok => {
                    cell.classList.remove('saving');
                    if (!ok) {
                        editor.textContent = originalValue;
                        cell.classList.add('error');
                    } else {
                        cell.classList.add('saved');
                        setTimeout(() => cell.classList.remove('saved'), 900);
                    }
                })
                .finally(() => cleanupCell(cell, editor));
        }

        /** --------------------------
         * DROPDOWN EDITING
         * -------------------------- */
        function startDropdownEdit(cell) {
            const editor = cell.querySelector('.inline-editor');
            const originalValue = editor.textContent.trim();
            const { id, field } = getCellMeta(cell);

            cell.classList.add('is-editing');
            editor.style.display = 'none';

            const options = JSON.parse(cell.dataset.options || '[]');
            const select = document.createElement('select');
            select.classList.add('inline-select');

            options.forEach(opt => {
                const option = document.createElement('option');
                option.value = opt;
                option.textContent = opt;
                if (opt === originalValue) option.selected = true;
                select.appendChild(option);
            });

            cell.appendChild(select);
            select.focus();

            const finish = (commit) => {
                const newValue = select.value;

                if (!commit || newValue === originalValue) {
                    cell.removeChild(select);
                    editor.style.display = '';
                    cell.classList.remove('is-editing');
                    return;
                }

                cell.classList.add('saving');

                sendUpdate(id, field, newValue)
                    .then(ok => {
                        cell.classList.remove('saving');
                        if (ok) {
                            editor.textContent = newValue;
                            cell.classList.add('saved');
                            setTimeout(() => cell.classList.remove('saved'), 900);
                        } else {
                            cell.classList.add('error');
                        }
                    })
                    .finally(() => {
                        cell.removeChild(select);
                        editor.style.display = '';
                        cell.classList.remove('is-editing');
                    });
            };

            select.addEventListener('change', () => finish(true));
            select.addEventListener('blur', () => finish(true));
            select.addEventListener('keydown', e => {
                if (e.key === 'Escape') finish(false);
            });
        }

        /** --------------------------
         * UTILITIES
         * -------------------------- */
        function sendUpdate(id, field, value) {
            return fetch('/Http/RequestHandler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ updateCell: { id, field, value } })
            })
            .then(r => r.json())
            .then(data => data?.status === 'ok')
            .catch(err => { console.error(err); return false; });
        }

        function getCellMeta(cell) {
            return {
                id: cell.dataset.id,
                field: cell.dataset.field,
            };
        }

        function cleanupCell(cell, editor) {
            editor.setAttribute('contenteditable', 'false');
            cell.classList.remove('is-editing');
            if (cell._handlers) {
                editor.removeEventListener('keydown', cell._handlers.onKeydown);
                editor.removeEventListener('blur', cell._handlers.onBlur);
                delete cell._handlers;
            }
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
</script>


<style>
    .sortable-ghost { opacity: 0.4; background-color: #ccc; }
    th { cursor: move; }

    #journalTable td { transition: background-color 0.3s ease; }

    #journalTable td.saving {
        background-color: #fff4c4 !important;
    }

    #journalTable td.saved {
        background-color: #d4ffd6 !important;
    }

    #journalTable td.error {
        background-color: #ffd4d4 !important;
    }

    .inline-editor { width: 100%; display: block; }
    .inline-select { width: 100%; padding: 4px; }
</style>
