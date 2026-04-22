// public/js/students.js
// Select all checkboxes for bulk actions

document.addEventListener('DOMContentLoaded', function () {
  const selectAll = document.getElementById('selectAll');
  if (selectAll) {
    selectAll.addEventListener('change', function () {
      document.querySelectorAll('input[name="selected[]"]').forEach(cb => {
        cb.checked = selectAll.checked;
      });
    });
  }
});
