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
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRevealAnimations, { once: true });
} else {
    initRevealAnimations();
}
