document.addEventListener('DOMContentLoaded', function() {
    const passwordField = document.getElementById('password');
    const togglePasswordIcon = document.getElementById('toggle-password');

    // Check if elements are loaded correctly
    if (passwordField && togglePasswordIcon) {
        // Function to toggle password visibility
        function togglePasswordVisibility() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            togglePasswordIcon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
        }

        // Show/hide the eye icon based on input value
        function toggleEyeIconVisibility() {
            togglePasswordIcon.style.visibility = passwordField.value.length > 0 ? 'visible' : 'hidden';
        }

        // Attach event listeners
        passwordField.addEventListener('input', toggleEyeIconVisibility);
        togglePasswordIcon.addEventListener('click', togglePasswordVisibility);

        // Ensure the eye icon is hidden on page load
        toggleEyeIconVisibility(); // Added to ensure the visibility state is correct on load
    } else {
        console.error("Required elements not found: 'password' or 'toggle-password'");
    }
});
