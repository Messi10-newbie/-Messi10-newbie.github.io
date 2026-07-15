// ── Ultra layer: reveal, count-up, tilt, scroll chrome ───────────────────────
(function () {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Scroll reveal with per-sibling stagger
    const revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length && 'IntersectionObserver' in window && !reducedMotion) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (!e.isIntersecting) return;
                e.target.classList.add('reveal-visible');
                io.unobserve(e.target);
            });
        }, { threshold: 0.12 });
        revealEls.forEach((el, i) => {
            el.style.setProperty('--reveal-delay', `${(i % 6) * 70}ms`);
            io.observe(el);
        });
    } else {
        revealEls.forEach((el) => el.classList.add('reveal-visible'));
    }

    // Count-up stats
    const counters = document.querySelectorAll('[data-countup]');
    if (counters.length) {
        const animate = (el) => {
            const target = parseInt(el.dataset.countup, 10) || 0;
            if (reducedMotion) { el.textContent = target; return; }
            const dur = 1200;
            const start = performance.now();
            (function tick(now) {
                const p = Math.min(1, (now - start) / dur);
                el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
                if (p < 1) requestAnimationFrame(tick);
            })(start);
        };
        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (!e.isIntersecting) return;
                    animate(e.target);
                    io.unobserve(e.target);
                });
            }, { threshold: 0.4 });
            counters.forEach((el) => io.observe(el));
        } else {
            counters.forEach(animate);
        }
    }

    // 3D tilt on stall cards (pointer devices only)
    if (!reducedMotion && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.stall-tilt').forEach((card) => {
            card.addEventListener('pointermove', (e) => {
                const r = card.getBoundingClientRect();
                const x = (e.clientX - r.left) / r.width - 0.5;
                const y = (e.clientY - r.top) / r.height - 0.5;
                card.style.transform =
                    `translateY(-4px) rotateX(${(-y * 8).toFixed(2)}deg) rotateY(${(x * 8).toFixed(2)}deg)`;
            });
            card.addEventListener('pointerleave', () => { card.style.transform = ''; });
        });
    }

    // Scroll progress bar + navbar state + back-to-top
    const bar = document.createElement('div');
    bar.className = 'scroll-progress';
    document.body.appendChild(bar);

    const topBtn = document.createElement('button');
    topBtn.className = 'back-to-top';
    topBtn.type = 'button';
    topBtn.setAttribute('aria-label', 'Back to top');
    topBtn.innerHTML = '<i class="bi bi-arrow-up"></i>';
    topBtn.addEventListener('click', () =>
        window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' }));
    document.body.appendChild(topBtn);

    const navbar = document.querySelector('.an-navbar');

    function onScroll() {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        bar.style.width = max > 0 ? `${(window.scrollY / max) * 100}%` : '0%';
        topBtn.classList.toggle('show', window.scrollY > 400);
        if (navbar) navbar.classList.toggle('nav-scrolled', window.scrollY > 30);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}());

// ── Stall search ─────────────────────────────────────────────────────────────
(function () {
    const input  = document.getElementById('stallSearch');
    const grid   = document.getElementById('stallGrid');
    const noMsg  = document.getElementById('noStallResults');
    if (!input || !grid) return;

    input.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        let visible = 0;
        grid.querySelectorAll('.stall-col').forEach(function (col) {
            const match = !q || col.dataset.search.includes(q);
            col.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (noMsg) noMsg.style.display = visible === 0 ? '' : 'none';
    });
}());

document.addEventListener('DOMContentLoaded', () => {
    const totalEl = document.querySelector('[data-order-total]');

    function optionExtra(card) {
        if (!card) return 0;
        let extra = 0;
        card.querySelectorAll('select.opt-select').forEach((sel) => {
            const opt = sel.options[sel.selectedIndex];
            extra += parseFloat((opt && opt.dataset.price) || '0');
        });
        card.querySelectorAll('input.opt-addon:checked').forEach((cb) => {
            extra += parseFloat(cb.dataset.price || '0');
        });
        return Number.isNaN(extra) ? 0 : extra;
    }

    function updateTotals() {
        if (!totalEl) return;
        let total = 0;
        document.querySelectorAll('.quantity-input').forEach((input) => {
            const price = parseFloat(input.dataset.price || '0');
            const qty   = parseInt(input.value || '0', 10);
            const extra = optionExtra(input.closest('.menu-item-card'));
            if (!Number.isNaN(price) && !Number.isNaN(qty)) total += (price + extra) * qty;
        });
        totalEl.textContent = `RM ${total.toFixed(2)}`;
    }

    // Recompute when drink options change
    document.querySelectorAll('.opt-select, .opt-addon').forEach((el) => {
        el.addEventListener('change', updateTotals);
    });

    // +/- stepper buttons
    document.querySelectorAll('.qty-stepper').forEach((stepper) => {
        const input   = stepper.querySelector('.quantity-input');
        const minusBtn = stepper.querySelector('.qty-minus');
        const plusBtn  = stepper.querySelector('.qty-plus');
        if (!input) return;

        const max = parseInt(input.max || '20', 10);

        function setVal(v) {
            const clamped = Math.max(0, Math.min(max, v));
            input.value = clamped;
            minusBtn.disabled = clamped === 0;
            if (clamped > 0) {
                stepper.style.borderColor = 'var(--an-primary)';
            } else {
                stepper.style.borderColor = '';
            }
            updateTotals();
        }

        minusBtn && minusBtn.addEventListener('click', () => setVal(parseInt(input.value || '0', 10) - 1));
        plusBtn  && plusBtn.addEventListener('click',  () => setVal(parseInt(input.value || '0', 10) + 1));

        // init state
        setVal(parseInt(input.value || '0', 10));
    });

    updateTotals();
});
