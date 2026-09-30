document.getElementById("registrationForm").addEventListener("submit", function(event) {

    event.preventDefault();

    let name = document.getElementById("name").value.trim();
    let email = document.getElementById("email").value.trim();
    let mobile = document.getElementById("mobile").value.trim();
    let password = document.getElementById("password").value;
    let confirmPassword = document.getElementById("confirmPassword").value;
    let course = document.getElementById("course").value;
    let year = document.getElementById("year").value;
    let gender = document.querySelector('input[name="gender"]:checked');
    let terms = document.getElementById("terms").checked;

    let nameRegex = /^[A-Za-z ]{3,30}$/;
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let mobileRegex = /^[6-9]\d{9}$/;
    let passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/;

    if (!nameRegex.test(name)) {
        alert("Enter a valid name.");
        return;
    }

    if (!emailRegex.test(email)) {
        alert("Enter a valid email.");
        return;
    }

    if (!mobileRegex.test(mobile)) {
        alert("Enter a valid 10-digit mobile number.");
        return;
    }

    if (!passwordRegex.test(password)) {
        alert("Password must contain 8 characters, a letter, a number and a special character.");
        return;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    if (course === "") {
        alert("Please select a course.");
        return;
    }

    if (year < 1 || year > 4) {
        alert("Enter a valid year.");
        return;
    }

    if (!gender) {
        alert("Please select gender.");
        return;
    }

    if (!terms) {
        alert("Please accept the Terms and Conditions.");
        return;
    }

    document.getElementById("message").innerHTML =
        "Registration Successful!";
});