document.addEventListener('DOMContentLoaded', function() {
    const uploadImageInput = document.getElementById('upload-image');
    const userImage = document.getElementById('user-image');
    const sidebarUserImage = document.querySelector('.sidebar-profile-picture-user-image');
    const responsiveSidebarUserImage = document.querySelector('.responsive-sidebar-profile-picture-user-image');
    const sidebarPlaceholder = document.querySelector('.sidebar-profile-picture-placeholder');
    const responsiveSidebarPlaceholder = document.querySelector('.responsive-sidebar-profile-picture-placeholder');
    const profilePlaceholder = document.querySelector('.profile-picture-placeholder');

    uploadImageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const imageUrl = e.target.result;
                userImage.src = imageUrl;
                userImage.style.display = 'block';
                userImage.style.objectFit = 'cover'; // Ensure the image covers the container

                // Sync with sidebar profile picture
                sidebarUserImage.src = imageUrl;
                sidebarUserImage.style.display = 'block';
                sidebarUserImage.style.objectFit = 'cover'; // Ensure the image covers the container
                sidebarPlaceholder.style.display = 'none'; // Hide the sidebar placeholder

                // Sync with responsive sidebar profile picture
                responsiveSidebarUserImage.src = imageUrl;
                responsiveSidebarUserImage.style.display = 'block';
                responsiveSidebarUserImage.style.objectFit = 'cover'; // Ensure the image covers the container
                responsiveSidebarPlaceholder.style.display = 'none'; // Hide the responsive sidebar placeholder

                profilePlaceholder.style.display = 'none'; // Hide the profile placeholder
            };
            reader.readAsDataURL(file);
        }
    });
});