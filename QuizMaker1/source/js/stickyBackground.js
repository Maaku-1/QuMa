document.addEventListener('DOMContentLoaded', () => {
    const header = document.querySelector('.header-wrapper');
    const stickyClass = 'is-sticky';

    const handleScroll = () => {
        if (window.scrollY > 0) {
            header.classList.add(stickyClass);
        } else {
            header.classList.remove(stickyClass);
        }
    };

    window.addEventListener('scroll', handleScroll);
    handleScroll(); // Initial check in case the page is already scrolled
});