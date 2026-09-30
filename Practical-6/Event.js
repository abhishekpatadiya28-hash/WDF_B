let events = [];
let currentPage = 1;

let search = document.getElementById("search");
let category = document.getElementById("category");
let sort = document.getElementById("sort");
let eventBox = document.getElementById("events");
let pagination = document.getElementById("pagination");


// Get JSON data
fetch("Event.json")
    .then(response => response.json())
    .then(data => {

        events = data;

        // Add categories to dropdown
        let categories = [];

        events.forEach(event => {
            if (!categories.includes(event.category)) {
                categories.push(event.category);
            }
        });

        categories.forEach(item => {
            let option = document.createElement("option");

            option.value = item;
            option.textContent = item;

            category.appendChild(option);
        });

        displayEvents();
    });


// Display events
function displayEvents() {

    let result = [...events];

    // Search
    let text = search.value.toLowerCase();

    result = result.filter(event =>
        event.title.toLowerCase().includes(text)
    );


    // Category filter
    if (category.value != "") {

        result = result.filter(event =>
            event.category == category.value
        );
    }


    // Sort
    result.sort((a, b) => {

        if (sort.value == "asc") {
            return new Date(a.date) - new Date(b.date);
        } else {
            return new Date(b.date) - new Date(a.date);
        }

    });


    // Pagination
    let perPage = 3;

    let start = (currentPage - 1) * perPage;

    let pageEvents = result.slice(
        start,
        start + perPage
    );


    // Clear old events
    eventBox.innerHTML = "";


    // Display cards
    pageEvents.forEach(event => {

        let card = document.createElement("div");

        card.className = "card";

        card.innerHTML = `
            <h2>${event.title}</h2>
            <p>Date: ${event.date}</p>
            <p>Category: ${event.category}</p>
            <p>Location: ${event.location}</p>
        `;

        eventBox.appendChild(card);
    });


    // Pagination buttons
    createPagination(result.length);
}


// Create pagination
function createPagination(total) {

    pagination.innerHTML = "";

    let perPage = 3;

    let pages = Math.ceil(total / perPage);

    for (let i = 1; i <= pages; i++) {

        let button = document.createElement("button");

        button.textContent = i;

        if (i == currentPage) {
            button.classList.add("active");
        }

        button.onclick = function() {

            currentPage = i;

            displayEvents();
        };

        pagination.appendChild(button);
    }
}


// Search
search.addEventListener("input", function() {

    currentPage = 1;

    displayEvents();
});


// Category
category.addEventListener("change", function() {

    currentPage = 1;

    displayEvents();
});


// Sort
sort.addEventListener("change", function() {

    currentPage = 1;

    displayEvents();
});