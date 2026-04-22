// Officer Attendance Tracking JS
// Codex: vanilla JS, accessible, no frameworks

document.addEventListener('DOMContentLoaded', function () {
  const table = document.querySelector('.attendance-tracking__table');
  const presentCount = document.getElementById('present-count');
  const absentCount = document.getElementById('absent-count');
  const markBtns = document.querySelectorAll('.attendance-tracking__mark-btn');

  function updateLiveCount() {
    let present = 0;
    let absent = 0;
    document.querySelectorAll('.attendance-tracking__badge').forEach(badge => {
      if (badge.classList.contains('attendance-tracking__badge--present')) present++;
      if (badge.classList.contains('attendance-tracking__badge--absent')) absent++;
    });
    presentCount.textContent = present;
    absentCount.textContent = absent;
  }

  markBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('tr');
      const badge = row.querySelector('.attendance-tracking__badge');
      const status = btn.getAttribute('data-status');
      // Update badge visually
      badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
      badge.classList.remove('attendance-tracking__badge--present', 'attendance-tracking__badge--absent');
      badge.classList.add('attendance-tracking__badge--' + status);
      updateLiveCount();
      // TODO: AJAX call to backend to persist attendance
    });
  });

  updateLiveCount();
});
