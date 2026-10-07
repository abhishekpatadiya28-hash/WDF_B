
let today = new Date().getDay();

let dayNames = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
];

let todayName = dayNames[today];


alert("Today is " + todayName);


let table = document.getElementById("timetable");

if (today >= 1 && today <= 5) {
    table.rows[0].cells[today].style.backgroundColor = "#007bff";
    table.rows[0].cells[today].style.color = "white";
}