document.addEventListener('DOMContentLoaded', function() {
    const mainSubjectsWrapper = document.querySelector('.main-subjects-wrapper');
    const noItemWrapper = document.querySelector('.main-subjects-no-item-wrapper');

    function checkSubjectCards() {
        const subjectCards = mainSubjectsWrapper.querySelectorAll('.card-template');
        if (subjectCards.length === 0) {
            noItemWrapper.style.display = 'flex';
            mainSubjectsWrapper.style.minHeight = '300px'; // Ensure minimum height to center no-item-wrapper
        } else {
            noItemWrapper.style.display = 'none';
            mainSubjectsWrapper.style.minHeight = 'auto'; // Reset min-height when there are subject cards
        }
    }

    // Initial check
    checkSubjectCards();
});