// public/js/officers.js
// Modal open/close logic for Add Officer

document.addEventListener('DOMContentLoaded', function () {
  const addBtn = document.getElementById('addOfficerBtn');
  const modal = document.getElementById('addOfficerModal');
  if (addBtn && modal) {
    addBtn.addEventListener('click', function () {
      modal.classList.add('modal--open');
    });
    modal.addEventListener('click', function (e) {
      if (e.target === modal) {
        modal.classList.remove('modal--open');
      }
    });
    modal.querySelectorAll('.modal__close').forEach(btn => {
      btn.addEventListener('click', function () {
        modal.classList.remove('modal--open');
      });
    });
  }
});
