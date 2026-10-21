
window.onload = function() {
    alert("Welcome to Assignments Page!");
};



let assignments = document.querySelectorAll("section p b");

assignments.forEach(function(assignment) {

    assignment.addEventListener("click", function() {
        alert("Assignment selected: " + assignment.innerText);
    });

});



let tasks = document.querySelectorAll("li");

tasks.forEach(function(task) {

    task.addEventListener("click", function() {
        alert("Priority Task: " + task.innerText);
    });

});



let completed = 3;
let total = 8;

alert("You have completed " + completed + " out of " + total + " assignments.");