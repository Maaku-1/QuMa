document.addEventListener('DOMContentLoaded', function() {
    const profileCardWrapper = document.querySelector('.profile-card-wrapper');
    const hoverEffect = document.createElement('div');
    hoverEffect.classList.add('hover-effect');
    profileCardWrapper.appendChild(hoverEffect);

    profileCardWrapper.addEventListener('mousemove', function(event) {
        const rect = profileCardWrapper.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        hoverEffect.style.transform = `translate(${x}px, ${y}px) scale(1)`;
    });

    profileCardWrapper.addEventListener('mouseleave', function() {
        hoverEffect.style.transform = 'translate(-50%, -50%) scale(0)';
    });
});