const table = document.getElementById('journalTable');
const headers = table.querySelectorAll('thead th');

let dragSrcIndex = null;

headers.forEach((th, index) => {
  th.addEventListener('dragstart', () => {
    dragSrcIndex = index;
  });

  th.addEventListener('dragover', e => e.preventDefault());

  th.addEventListener('drop', () => {
    if (dragSrcIndex === null || dragSrcIndex === index) return;

    const rows = table.rows;
    for (let row of rows) {
      const cells = row.cells;
      row.insertBefore(cells[dragSrcIndex], cells[index]);
    }
  });
});