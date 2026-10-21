
const API_URL = "api/students.php";

const form = document.getElementById("student-form");
const list = document.getElementById("student-list");
const message = document.getElementById("message");
const passwordField = document.getElementById("password-field");
const passwordInput = document.getElementById("password");
const formTitle = document.getElementById("form-title");
const saveButton = document.getElementById("save-button");
const cancelButton = document.getElementById("cancel-button");

let csrfToken = "";
let editingId = null;
let students = [];

function showMessage(text, isError = false) {
    message.textContent = text;
    message.style.color = isError ? "#b91c1c" : "#166534";
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        ...options,
        credentials: "same-origin",
        headers: {
            ...(options.headers || {}),
            ...(options.method && options.method !== "GET"
                ? { "X-CSRF-Token": csrfToken }
                : {}),
            ...(options.body ? { "Content-Type": "application/json" } : {})
        }
    });

    const result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(result.message || "Request failed.");
    }

    return result;
}

async function loadStudents() {
    list.replaceChildren();

    const loadingRow = document.createElement("tr");
    const loadingCell = document.createElement("td");
    loadingCell.colSpan = 7;
    loadingCell.textContent = "Loading students...";
    loadingRow.append(loadingCell);
    list.append(loadingRow);

    try {
        const result = await request(API_URL);

        csrfToken = result.csrf_token;
        students = result.students;
        displayStudents();
    } catch (error) {
        list.replaceChildren();

        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 7;
        cell.textContent = error.message;
        row.append(cell);
        list.append(row);

        showMessage(error.message, true);
    }
}

function displayStudents() {
    list.replaceChildren();

    if (students.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 7;
        cell.textContent = "No student records found.";
        row.append(cell);
        list.append(row);
        return;
    }

    students.forEach(student => {
        const row = document.createElement("tr");

        [
            student.id,
            student.name,
            student.username,
            student.email,
            student.course,
            student.year
        ].forEach(value => {
            const cell = document.createElement("td");
            cell.textContent = value ?? "";
            row.append(cell);
        });

        const actions = document.createElement("td");

        const editButton = document.createElement("button");
        editButton.type = "button";
        editButton.textContent = "Edit";
        editButton.className = "edit";
        editButton.addEventListener("click", () => editStudent(student.id));

        const deleteButton = document.createElement("button");
        deleteButton.type = "button";
        deleteButton.textContent = "Delete";
        deleteButton.className = "delete";
        deleteButton.addEventListener("click", () => deleteStudent(student.id));

        actions.append(editButton, deleteButton);
        row.append(actions);
        list.append(row);
    });
}

function editStudent(id) {
    const student = students.find(item => Number(item.id) === Number(id));

    if (!student) {
        showMessage("Student not found.", true);
        return;
    }

    editingId = Number(student.id);

    ["name", "username", "email", "mobile", "course", "year", "gender"]
        .forEach(field => {
            form.elements[field].value = student[field] ?? "";
        });

    passwordField.hidden = true;
    passwordInput.required = false;
    passwordInput.value = "";

    formTitle.textContent = "Update Student";
    saveButton.textContent = "Save Changes";
    cancelButton.hidden = false;

    form.scrollIntoView({ behavior: "smooth", block: "start" });
}

function resetForm() {
    form.reset();
    editingId = null;

    passwordField.hidden = false;
    passwordInput.required = true;

    formTitle.textContent = "Add Student";
    saveButton.textContent = "Add Student";
    cancelButton.hidden = true;
}

form.addEventListener("submit", async event => {
    event.preventDefault();

    const data = Object.fromEntries(new FormData(form).entries());

    if (editingId !== null) {
        delete data.password;
        data.id = editingId;
    }

    saveButton.disabled = true;

    try {
        const result = await request(API_URL, {
            method: editingId !== null ? "PUT" : "POST",
            body: JSON.stringify(data)
        });

        showMessage(result.message);
        resetForm();
        await loadStudents();
    } catch (error) {
        showMessage(error.message, true);
    } finally {
        saveButton.disabled = false;
    }
});

async function deleteStudent(id) {
    if (!confirm("Are you sure you want to delete this student?")) {
        return;
    }

    try {
        const result = await request(API_URL, {
            method: "DELETE",
            body: JSON.stringify({ id })
        });

        if (editingId === Number(id)) {
            resetForm();
        }

        showMessage(result.message);
        await loadStudents();
    } catch (error) {
        showMessage(error.message, true);
    }
}

cancelButton.addEventListener("click", resetForm);
document.getElementById("refresh-button").addEventListener("click", loadStudents);

loadStudents();

