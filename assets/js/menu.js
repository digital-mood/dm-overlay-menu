// DM Overlay Menu JS
// Small dependency-free controller. Comments are in English.
(function () {
  const burger = document.getElementById('dmBurger');
  const overlay = document.getElementById('dmOverlay');
  const closeBtn = document.getElementById('dmClose');

  if (!burger || !overlay || !closeBtn) return;

  function openMenu() {
    overlay.classList.add('is-open');
    burger.setAttribute('aria-expanded', 'true');
    overlay.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('body--lock');
  }
  function closeMenu() {
    overlay.classList.remove('is-open');
    burger.setAttribute('aria-expanded', 'false');
    overlay.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('body--lock');
  }

  burger.addEventListener('click', openMenu);
  closeBtn.addEventListener('click', closeMenu);

  // Close when clicking outside the right panel
  overlay.addEventListener('click', (e) => {
    const isRightPanel = e.target.closest('.dm-panel--orange');
    const isBurger = e.target.closest('#dmBurger');
    if (!isRightPanel && !isBurger) closeMenu();
  });

  // ESC to close
  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeMenu();
  });
})();
