import './bootstrap';

const initRevealAnimations = () => {
    const animatedItems = document.querySelectorAll('[data-animate]');

    if (!animatedItems.length) {
        return;
    }

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion || !('IntersectionObserver' in window)) {
        animatedItems.forEach((item) => item.classList.add('reveal-in'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, currentObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('reveal-in');
                currentObserver.unobserve(entry.target);
            });
        },
        {
            root: null,
            rootMargin: '0px 0px -10% 0px',
            threshold: 0.1,
        },
    );

    animatedItems.forEach((item) => {
        item.classList.add('reveal');

        const delay = Number.parseInt(item.dataset.animateDelay ?? '0', 10);
        if (!Number.isNaN(delay) && delay > 0) {
            item.style.setProperty('--reveal-delay', `${delay}ms`);
        }

        observer.observe(item);
    });

    // Safety fallback: reveal all items after 5s if observer hasn't fired
    setTimeout(() => {
        animatedItems.forEach((item) => {
            if (!item.classList.contains('reveal-in')) {
                item.classList.add('reveal-in');
            }
        });
    }, 3000);
};

/**
 * Lazy-load CSS background images using IntersectionObserver.
 * Elements with data-bg-lazy attribute get their background-image
 * applied only when they enter the viewport (or 200px before).
 */
const initLazyBackgrounds = () => {
    const lazyEls = document.querySelectorAll('[data-bg-lazy]');
    if (!lazyEls.length) return;

    const applyBg = (el) => {
        const src = el.dataset.bgLazy;
        if (src) {
            el.style.backgroundImage = `url("${src}")`;
            el.removeAttribute('data-bg-lazy');
        }
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    applyBg(entry.target);
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '200px 0px' },
        );

        lazyEls.forEach((el) => observer.observe(el));
    } else {
        // Fallback: load immediately
        lazyEls.forEach((el) => applyBg(el));
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRevealAnimations, { once: true });
    document.addEventListener('DOMContentLoaded', initLazyBackgrounds, { once: true });
} else {
    initRevealAnimations();
    initLazyBackgrounds();
}
