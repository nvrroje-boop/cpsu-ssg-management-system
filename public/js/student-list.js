// Officer Student List JS
// Codex: vanilla JS, accessible, no frameworks

document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.querySelector('.student-list__search');
  const filterSelect = document.querySelector('.student-list__filter');
  const tableRows = document.querySelectorAll('.student-list__table tbody tr');

  function filterTable() {
    const search = searchInput.value.toLowerCase();
    const year = filterSelect.value;
    tableRows.forEach(row => {
      const name = row.children[1].textContent.toLowerCase();
      const id = row.children[0].textContent.toLowerCase();
      const rowYear = row.children[2].textContent;
      let show = true;
      if (search && !(name.includes(search) || id.includes(search))) show = false;
      if (year && rowYear !== year) show = false;
      row.style.display = show ? '' : 'none';
    });
  }

  searchInput.addEventListener('input', filterTable);
  filterSelect.addEventListener('change', filterTable);
});
