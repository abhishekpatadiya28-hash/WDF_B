
let hour = new Date().getHours();

let greeting;

if (hour < 12) {
    greeting = "Good Morning ☀️";
} 
else if (hour < 18) {
    greeting = "Good Afternoon 🌤️";
} 
else {
    greeting = "Good Evening 🌙";
}

document.getElementById("welcomeMessage").innerText = greeting + " 👋";



let goals = document.querySelector(".goal-box");

goals.addEventListener("click", function() {
    alert("Keep going! Complete your today's goals. 🎯");
});