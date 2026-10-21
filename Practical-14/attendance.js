
window.onload = function() {
    alert("Welcome to Attendance Page!");
};



let attendance = document.querySelectorAll(".subject-attendance td:nth-child(2)");

attendance.forEach(function(cell) {

    let value = parseInt(cell.innerText);

    if (value < 75) {
        cell.style.color = "red";
    }

});



attendance.forEach(function(cell) {

    cell.addEventListener("click", function() {
        alert("Attendance: " + cell.innerText);
    });

});