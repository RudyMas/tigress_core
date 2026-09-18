/**
 * Initialize carousel behaviour
 */
export function initCarousel() {
    const carouselContainer = document.getElementById('carouselContainer');
    const closeCarousel = document.getElementById('closeCarousel');

    // No carousel on this page
    if (!carouselContainer) {
        return;
    }

    // Check if the carousel should be hidden
    if (sessionStorage.getItem('carouselHidden') === 'true') {
        carouselContainer.style.display = 'none';
    }

    // Add event listener to the close button
    if (closeCarousel) {
        closeCarousel.addEventListener('click', function () {
            carouselContainer.style.display = 'none';
            sessionStorage.setItem('carouselHidden', 'true');
        });
    }
}