
let hour = new Date().getHours();
let welcome = document.querySelector(".welcome");

if (hour < 12) {
    welcome.innerHTML = "🎓 GOOD MORNING, WELCOME TO STUDENTHUB";
} 
else if (hour < 18) {
    welcome.innerHTML = "🎓 GOOD AFTERNOON, WELCOME TO STUDENTHUB";
}
 else {
    welcome.innerHTML = "🎓 GOOD EVENING, WELCOME TO STUDENTHUB";
}


document.querySelector(".start-btn").addEventListener("click", function () {
    alert("Welcome to StudentHub! Let's get started.");
});


document.querySelector(".bottom button").addEventListener("click", function () {
    alert("Great choice! Let's create your StudentHub account.");
});