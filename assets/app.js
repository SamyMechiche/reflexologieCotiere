import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

function initSliderAndMenu() {
    const slides = document.querySelector('.slides');
    const slide = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    if (slides && slide.length && prevBtn && nextBtn) {
        let currentIndex = 0;
        const slideCount = slide.length;

        function goToSlide(index) {
            let slidesPerView = 1;
            let slideWidth = slides.parentElement.clientWidth;
            let gap = 0;

            if (window.innerWidth >= 1024) {
                slidesPerView = 3;
                gap = parseFloat(window.getComputedStyle(slides).gap) || 0;
                slideWidth = (slides.parentElement.clientWidth - 2 * gap) / 3;
            }

            if (index < 0) {
                index = slideCount - slidesPerView;
            } else if (index > slideCount - slidesPerView) {
                index = 0;
            }
            const totalShift = index * (slideWidth + gap);
            slides.style.transform = `translateX(-${totalShift}px)`;
            currentIndex = index;
        }

        prevBtn.onclick = () => goToSlide(currentIndex - 1);
        nextBtn.onclick = () => goToSlide(currentIndex + 1);
        window.addEventListener('resize', () => goToSlide(currentIndex));
        goToSlide(0);
    }
}

let menuTimeout;
let scrollHandler = null;
let footerObserver = null;
function initBottomMenuScroll() {
    const bottomMenu = document.querySelector('.bottom-menu');
    const footer = document.querySelector('footer');
    if (!bottomMenu) return;
    // Always start with menu visible
    bottomMenu.classList.remove('hide');
    // Remove previous handler if any
    if (scrollHandler) {
        window.removeEventListener('scroll', scrollHandler);
    }
    if (footerObserver) {
        footerObserver.disconnect();
    }
    let footerInView = false;
    // Hide menu when scrolling, unless footer is in view
    scrollHandler = () => {
        if (footerInView) return;
        bottomMenu.classList.add('hide');
        clearTimeout(menuTimeout);
        menuTimeout = setTimeout(() => {
            if (!footerInView) bottomMenu.classList.remove('hide');
        }, 500);
    };
    window.addEventListener('scroll', scrollHandler);
    // Hide menu when footer is in view
    if (footer) {
        footerObserver = new window.IntersectionObserver(
            (entries) => {
                footerInView = entries[0].isIntersecting;
                if (footerInView) {
                    bottomMenu.classList.add('hide');
                } else {
                    bottomMenu.classList.remove('hide');
                }
            },
            { root: null, threshold: 0.1 }
        );
        footerObserver.observe(footer);
    }
}

function initScrollToTopBtn() {
    const btn = document.getElementById('scrollToTopBtn');
    if (!btn) return;
    function toggleBtn() {
        if (window.scrollY > 200) {
            btn.style.display = 'block';
        } else {
            btn.style.display = 'none';
        }
    }
    btn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });
    window.addEventListener('scroll', toggleBtn);
    // Run once on load
    toggleBtn();
}

document.addEventListener('DOMContentLoaded', () => {
    initSliderAndMenu();
    initBottomMenuScroll();
    initScrollToTopBtn();
});
document.addEventListener('turbo:load', () => {
    initSliderAndMenu();
    initBottomMenuScroll();
    initScrollToTopBtn();
});

document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('openReviewModal');
    const modal = document.getElementById('reviewModal');
    const closeBtn = document.getElementById('closeReviewModal');
    const starEls = document.querySelectorAll('#starRating .star');
    const starsInput = document.getElementById('starsInput');
    let selectedStars = 0;

    if (openBtn && modal) {
        openBtn.addEventListener('click', function() {
            modal.style.display = 'flex';
            fetchSessions();
        });
    }
    if (closeBtn && modal) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    // Star rating logic
    starEls.forEach(star => {
        star.addEventListener('mouseover', function() {
            highlightStars(this.dataset.value);
        });
        star.addEventListener('mouseout', function() {
            highlightStars(selectedStars);
        });
        star.addEventListener('click', function() {
            selectedStars = this.dataset.value;
            starsInput.value = selectedStars;
            highlightStars(selectedStars);
        });
    });
    function highlightStars(count) {
        starEls.forEach(star => {
            star.classList.toggle('selected', star.dataset.value <= count);
        });
    }
    // Fetch sessions for dropdown (AJAX)
    function fetchSessions() {
        fetch('/sessions-for-review')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('sessionSelect');
                select.innerHTML = '';
                data.forEach(session => {
                    const opt = document.createElement('option');
                    opt.value = session.id;
                    opt.textContent = session.name + ' (' + session.duration + ' min)';
                    select.appendChild(opt);
                });
            });
    }
    // Handle form submit (AJAX)
    document.getElementById('reviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('/submit-review', {
            method: 'POST',
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('reviewMessage').textContent = data.message;
            if (data.success) {
                this.reset();
                highlightStars(0);
                selectedStars = 0;
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const slider = document.querySelector('.banner-slider .banner-slides');
    if (!slider) return;
    const slides = slider.querySelectorAll('img');
    const slideCount = slides.length;
    let current = 0;

    // Set up slider styles
    slider.style.display = 'flex';
    slider.style.transition = 'transform 0.7s cubic-bezier(0.4,0,0.2,1)';
    slides.forEach(img => {
        img.style.width = '100vw';
        img.style.height = '320px'; // adjust as needed
        img.style.objectFit = 'cover';
        img.style.flexShrink = '0';
    });

    function goToSlide(idx) {
        slider.style.transform = `translateX(-${idx * 100}vw)`;
    }

    setInterval(() => {
        current = (current + 1) % slideCount;
        goToSlide(current);
    }, 3000); // 3 seconds per slide

    // Initial position
    goToSlide(0);
});