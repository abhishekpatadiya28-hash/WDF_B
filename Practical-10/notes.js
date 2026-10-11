
let subjects = document.querySelectorAll(".subject");

subjects.forEach(function(subject) {

    subject.addEventListener("click", function() {

        alert("You selected: " + subject.innerText);

    });

});



let progress = document.querySelectorAll("progress");

progress.forEach(function(item) {

    item.title = item.value + "% completed";

});