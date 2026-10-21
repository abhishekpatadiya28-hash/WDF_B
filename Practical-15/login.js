document.getElementById("loginForm").addEventListener("submit", function(event) {

    let username = document.getElementById("username").value;
    let password = document.getElementById("password").value;

    if (username.length < 3) {
        alert("Username must contain at least 3 characters.");
        event.preventDefault();
        return;
    }

    if (password.length < 6) {
        alert("Password must contain at least 6 characters.");
        event.preventDefault();
        return;
    }

    alert("Login successful! Welcome to StudentHub.");
});