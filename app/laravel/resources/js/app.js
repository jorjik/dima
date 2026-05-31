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
            el.style.backgroundImage = `url('${src}')`;
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

/**
 * Lazy-load gallery videos using IntersectionObserver.
 * Videos with data-video-lazy have preload="none" and data-src on <source>.
 * When they enter the viewport (300px before), we activate them:
 * move data-src -> src, change preload to "metadata", and load.
 */
const initLazyVideos = () => {
    const lazyVids = document.querySelectorAll('video[data-video-lazy]');
    if (!lazyVids.length) return;

    const activateVideo = (video) => {
        // Move data-src from <source> to src
        const source = video.querySelector('source[data-src]');
        if (source) {
            source.src = source.dataset.src;
            source.removeAttribute('data-src');
        }

        // Change preload to metadata so browser loads the first frame
        video.preload = 'metadata';

        // Force the browser to start loading metadata
        video.load();

        video.removeAttribute('data-video-lazy');
    };

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    activateVideo(entry.target);
                    observer.unobserve(entry.target);
                });
            },
            { rootMargin: '300px 0px' },
        );

        lazyVids.forEach((el) => observer.observe(el));
    } else {
        // Fallback: activate immediately
        lazyVids.forEach((el) => activateVideo(el));
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRevealAnimations, { once: true });
    document.addEventListener('DOMContentLoaded', initLazyBackgrounds, { once: true });
    document.addEventListener('DOMContentLoaded', initLazyVideos, { once: true });
} else {
    initRevealAnimations();
    initLazyBackgrounds();
    initLazyVideos();
}
