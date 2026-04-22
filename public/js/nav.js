// public/js/nav.js
// Responsive hamburger nav toggle

document.addEventListener('DOMContentLoaded', function () {
  const navToggle = document.querySelector('.nav__toggle');
  const navMenu = document.querySelector('.nav__menu');
  if (navToggle && navMenu) {
    navToggle.addEventListener('click', function () {
      navMenu.classList.toggle('nav__menu--open');
      navToggle.setAttribute('aria-expanded', navMenu.classList.contains('nav__menu--open'));
    });
    // Close menu on link click (mobile)
    navMenu.querySelectorAll('.nav__item').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 1024) {
          navMenu.classList.remove('nav__menu--open');
          navToggle.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }
});
