// Officer Attendance Report JS
// Codex: vanilla JS, accessible, no frameworks

document.addEventListener('DOMContentLoaded', function () {
  const presentCount = document.getElementById('present-count');
  const absentCount = document.getElementById('absent-count');
  const totalCount = document.getElementById('total-count');

  function updateSummary() {
    let present = 0;
    let absent = 0;
    let total = 0;
    document.querySelectorAll('.attendance-report__badge').forEach(badge => {
      total++;
      if (badge.classList.contains('attendance-report__badge--present')) present++;
      if (badge.classList.contains('attendance-report__badge--absent')) absent++;
    });
    presentCount.textContent = present;
    absentCount.textContent = absent;
    totalCount.textContent = total;
  }

  updateSummary();
});
