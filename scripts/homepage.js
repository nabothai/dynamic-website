document.addEventListener('DOMContentLoaded', () => {
    const heroSection = document.querySelector('.hero');
    heroSection.addEventListener('mouseover', () => {
        heroSection.style.background = 'linear-gradient(to right, #27ae60, #c0392b)';
    });

    heroSection.addEventListener('mouseout', () => {
        heroSection.style.background = 'linear-gradient(to right, #c0392b, #27ae60)';
    });

    const highlights = document.querySelectorAll('.highlights div');
    highlights.forEach(highlight => {
        highlight.addEventListener('mouseover', () => {
            highlight.style.transform = 'scale(1.05)';
        });

        highlight.addEventListener('mouseout', () => {
            highlight.style.transform = 'scale(1)';
        });
    });
});