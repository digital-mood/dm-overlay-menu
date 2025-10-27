// DM Overlay Menu JS (v1.1.0)
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

  overlay.addEventListener('click', (e) => {
    const isRightPanel = e.target.closest('.dm-panel--orange');
    const isBurger = e.target.closest('#dmBurger');
    if (!isRightPanel && !isBurger) closeMenu();
  });

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeMenu();
  });

  (function(){
    const h = document.querySelector('.dm-header'); if(!h) return;
    const onS = () => window.scrollY > 8 ? h.classList.add('is-scrolled') : h.classList.remove('is-scrolled');
    window.addEventListener('scroll', onS, {passive:true}); onS();
  })();
})();
