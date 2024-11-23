function togglePassword() {
    var passwordInput = document.getElementById("password");
    var toggleIcon = document.querySelector(".toggle-password");

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.style.backgroundImage = "url('assets/eye.svg')"; 
    } else {
        passwordInput.type = "password";
        toggleIcon.style.backgroundImage = "url('assets/eye-slash.svg')";
    }
}
