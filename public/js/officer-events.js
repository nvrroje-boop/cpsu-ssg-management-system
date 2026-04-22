// public/js/officer-events.js
// QR modal open/close and QR code rendering (placeholder)
document.addEventListener('DOMContentLoaded', function () {
  const qrModal = document.getElementById('qrModal');
  const qrCodeContainer = document.getElementById('qrCodeContainer');
  document.querySelectorAll('.btn--qr').forEach(btn => {
    btn.addEventListener('click', function () {
      // Placeholder: Replace with actual QR code rendering logic
      qrCodeContainer.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;">QR</div>';
      qrModal.classList.add('modal--open');
    });
  });
  qrModal?.querySelectorAll('.modal__close').forEach(btn => {
    btn.addEventListener('click', function () {
      qrModal.classList.remove('modal--open');
    });
  });
  qrModal?.addEventListener('click', function (e) {
    if (e.target === qrModal) {
      qrModal.classList.remove('modal--open');
    }
  });
});
