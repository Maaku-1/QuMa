document.addEventListener('DOMContentLoaded', function() {
    const nameElement = document.getElementById('name');
    const profileDetails = document.querySelector('.sidebar-profile-details');
    const profilePicWrapper = document.querySelector('.sidebar-profile-picture-wrapper');

    const responsiveNameElement = document.getElementById('name');
    const responsiveProfileDetails = document.querySelector('.responsive-sidebar-profile-details');
    const responsiveProfilePicWrapper = document.querySelector('.responsive-sidebar-profile-wrapper');

    function updateProfileLayout() {
        const name = nameElement ? nameElement.textContent.trim() : '';

        // Check if name is missing
        if (!name) {
            profilePicWrapper.classList.add('centered');  // Center the profile picture wrapper with animation
            profileDetails.style.display = 'none';
            profileDetails.classList.remove('visible'); // Hide the name section when empty
        } else {
            profilePicWrapper.classList.remove('centered');  // Return the profile picture to its original position
            profileDetails.style.display = ''; // Restore the name section visibility
            profileDetails.classList.add('visible');
        }
    }

    function updateResponsiveProfileLayout() {
        const name = responsiveNameElement ? responsiveNameElement.textContent.trim() : '';

        // Check if name is missing
        if (!name) {
            responsiveProfilePicWrapper.classList.add('centered');  // Center the profile picture wrapper
            responsiveProfileDetails.style.display = 'none'; // Hide the name section when empty
        } else {
            responsiveProfilePicWrapper.classList.remove('centered');  // Return the profile picture to its original position
            responsiveProfileDetails.style.display = ''; // Restore the name section visibility
        }
    }

    // Initial check
    updateProfileLayout();
    updateResponsiveProfileLayout();

    // Add MutationObserver to detect changes in the profile details
    const observer = new MutationObserver(updateProfileLayout);
    if (nameElement) observer.observe(nameElement, { childList: true, subtree: true });

    const responsiveObserver = new MutationObserver(updateResponsiveProfileLayout);
    if (responsiveNameElement) responsiveObserver.observe(responsiveNameElement, { childList: true, subtree: true });
});