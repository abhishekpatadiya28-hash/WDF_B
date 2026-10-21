document.getElementById("editProfile").addEventListener("click", function() {

    let name = prompt("Enter your name:");

    if (name != null && name.trim() != "") {
        document.getElementById("studentName").innerText = name;
        alert("Profile updated successfully!");
    }

});