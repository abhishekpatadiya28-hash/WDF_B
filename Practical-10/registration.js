
const form = document.getElementById("registrationForm");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");
const message = document.getElementById("message");

function checkPassword() {
    if (confirmPassword.value !== "" &&
        password.value !== confirmPassword.value) {
        confirmPassword.setCustomValidity("Passwords do not match.");
        message.textContent = "Passwords do not match.";
    } else {
        confirmPassword.setCustomValidity("");
        message.textContent = "";
    }
}

password.addEventListener("input", checkPassword);
confirmPassword.addEventListener("input", checkPassword);

form.addEventListener("submit", function(event) {
    checkPassword();

    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
    }
});
