// public/js/qr-display.js
// QR code rendering (placeholder) and countdown timer

document.addEventListener('DOMContentLoaded', function () {
  // Placeholder QR rendering
  const qrCode = document.getElementById('qrCode');
  if (qrCode) {
    qrCode.innerHTML = '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;">QR</div>';
  }
  // Countdown timer (assume expiry in 5 minutes for demo)
  const countdown = document.getElementById('qrCountdown');
  let seconds = 5 * 60;
  function updateCountdown() {
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    countdown.textContent = `${m}:${s}`;
    if (seconds > 0) {
      seconds--;
      setTimeout(updateCountdown, 1000);
    } else {
      countdown.textContent = 'Expired';
      qrCode.innerHTML = '<div style="color:red;font-weight:700;">Expired</div>';
    }
  }
  updateCountdown();
});
