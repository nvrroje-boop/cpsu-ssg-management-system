// Student QR Scan JS
// Codex: vanilla JS, accessible, no frameworks

document.addEventListener('DOMContentLoaded', function () {
  const startBtn = document.querySelector('.student-qr-scan__start-btn');
  const resultDiv = document.querySelector('.student-qr-scan__result');
  const resultValue = document.querySelector('.student-qr-scan__result-value');

  startBtn.addEventListener('click', function () {
    // Placeholder: Simulate QR scan result
    setTimeout(function () {
      resultValue.textContent = 'Sample QR Code Data';
      resultDiv.style.display = '';
    }, 1200);
    // TODO: Integrate real QR scanner library
  });
});
