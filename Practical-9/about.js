
window.onload = function() {
    alert("Welcome to StudentHub About Page!");
};


let reasons = document.querySelectorAll(".why li");

reasons.forEach(function(reason) {

    reason.addEventListener("click", function() {
        alert("StudentHub Feature: " + reason.innerText);
    });

});



let developer = document.querySelector(".developer");

developer.addEventListener("click", function() {
    alert("StudentHub is developed by Abhishek Patadiya.");
});