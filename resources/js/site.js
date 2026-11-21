// Public website motion: see-through header that turns solid on scroll,
// scroll progress bar, reveal-on-scroll, word-by-word headings, hero
// parallax and number counters. Everything is skipped when the visitor
// asks for reduced motion.
const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
document.documentElement.classList.add('js');

const header = document.querySelector('.site-header');
const progress = document.querySelector('.site-progress');
const heroMedia = document.querySelector('.hero-media');

let ticking = false;
function onScroll() {
    const y = window.scrollY;
    header?.classList.toggle('is-solid', y > 40);
    if (progress) {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        progress.style.setProperty('--progress', max > 0 ? (y / max).toFixed(4) : 0);
    }
    if (heroMedia && !reduce && y < window.innerHeight * 1.2) {
        heroMedia.style.setProperty('--parallax', `${Math.round(y * 0.35)}px`);
    }
    ticking = false;
}
window.addEventListener('scroll', () => {
    if (!ticking) { requestAnimationFrame(onScroll); ticking = true; }
}, { passive: true });
onScroll();

// Wrap each word of [data-split] headings so they rise one by one.
document.querySelectorAll('[data-split]').forEach((el) => {
    const words = el.textContent.trim().split(/\s+/);
    el.setAttribute('aria-label', el.textContent.trim());
    el.innerHTML = words.map((w, i) => `<span class="split-word" aria-hidden="true"><span style="--i:${i}">${w.replace(/</g, '&lt;')}</span></span>`).join(' ');
    el.classList.add('split-ready');
    if (!el.hasAttribute('data-reveal')) el.setAttribute('data-reveal', 'none');
});

// Children of [data-stagger] reveal one after another.
document.querySelectorAll('[data-stagger]').forEach((group) => {
    const step = parseFloat(group.dataset.stagger) || 0.12;
    [...group.children].forEach((child, i) => {
        if (!child.hasAttribute('data-reveal')) child.setAttribute('data-reveal', group.dataset.effect || 'up');
        child.style.setProperty('--delay', `${(i * step).toFixed(2)}s`);
    });
});

function countUp(el) {
    const target = parseFloat(el.dataset.count);
    if (Number.isNaN(target)) return;
    if (reduce) { el.textContent = target.toLocaleString(); return; }
    const start = performance.now();
    const dur = 1600;
    const tick = (now) => {
        const p = Math.min((now - start) / dur, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
        if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
}

const targets = document.querySelectorAll('[data-reveal], [data-count]');
if (reduce || !('IntersectionObserver' in window)) {
    targets.forEach((el) => { el.classList.add('is-in'); if (el.dataset.count) countUp(el); });
} else {
    // A fully clipped element has no visible area, so watch its parent instead.
    const watched = new Map();
    const io = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            const el = watched.get(entry.target) || entry.target;
            el.classList.add('is-in');
            if (el.dataset.count) countUp(el);
            io.unobserve(entry.target);
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });
    targets.forEach((el) => {
        const watch = el.dataset.reveal === 'clip' && el.parentElement ? el.parentElement : el;
        if (watch !== el) watched.set(watch, el);
        io.observe(watch);
    });
}
