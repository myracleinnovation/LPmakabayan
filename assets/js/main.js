// Scroll functionality for main website
window.addEventListener('scroll', function () {
    const navbar = document.getElementById('scroll-navbar');
    const hero = document.querySelector('.position-relative.min-vh-100');
    if (!navbar || !hero) return;
    const heroBottom = hero.offsetTop + hero.offsetHeight - 80;
    if (window.scrollY > heroBottom) {
        navbar.classList.add('show-navbar');
    } else {
        navbar.classList.remove('show-navbar');
    }
});

const yearElement = document.getElementById('year');
if (yearElement) {
    yearElement.textContent = new Date().getFullYear();
}