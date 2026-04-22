// public/js/password-reset.js
// Modal open/close and dynamic form for password reset

document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('resetConfirmModal');
  const form = document.getElementById('resetConfirmForm');
  const userNameSpan = document.getElementById('resetUserName');
  document.querySelectorAll('.password-reset__trigger').forEach(btn => {
    btn.addEventListener('click', function () {
      const userId = btn.getAttribute('data-user-id');
      const userName = btn.getAttribute('data-user-name');
      form.action = `/admin/password-reset/${userId}`;
      userNameSpan.textContent = userName;
      modal.classList.add('modal--open');
    });
  });
  modal?.querySelectorAll('.modal__close').forEach(btn => {
    btn.addEventListener('click', function () {
      modal.classList.remove('modal--open');
    });
  });
  modal?.addEventListener('click', function (e) {
    if (e.target === modal) {
      modal.classList.remove('modal--open');
    }
  });
});
