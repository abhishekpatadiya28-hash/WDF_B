<?php
session_start();
require_once __DIR__ . "/db.php";

/*
 * StudentHub Student Management
 * Access: Admin only
 * Uses the existing student table. Does not create or delete tables.
 *
 * IMPORTANT:
 * This page expects your login system to set:
 *   $_SESSION['role'] = 'admin';
 * If your login system uses a different session key, update the admin check below.
 */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['student_csrf_token'])) {
    $_SESSION['student_csrf_token'] = bin2hex(random_bytes(32));
}

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function redirectWithMessage(string $message, string $type = "success"): void {
    $_SESSION['student_flash'] = ["message" => $message, "type" => $type];
    header("Location: student.php");
    exit;
}

function validStudentInput(array $data, array &$errors): void {
    if ($data["name"] === "" || mb_strlen($data["name"]) > 100) {
        $errors[] = "Name is required and must be 100 characters or fewer.";
    }
    if ($data["username"] === "" || mb_strlen($data["username"]) > 50) {
        $errors[] = "Username is required and must be 50 characters or fewer.";
    }
    if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL) || mb_strlen($data["email"]) > 100) {
        $errors[] = "Enter a valid email address (maximum 100 characters).";
    }
    if ($data["mobile"] === "" || mb_strlen($data["mobile"]) > 15 || !preg_match('/^[0-9+().\-\s]{7,15}$/', $data["mobile"])) {
        $errors[] = "Enter a valid mobile number (7–15 characters).";
    }
    if (!in_array($data["course"], ["BCA", "B.Tech", "BBA", "MCA"], true)) {
        $errors[] = "Select a valid course.";
    }
    if (!in_array((string)$data["year"], ["1", "2", "3", "4"], true)) {
        $errors[] = "Select a valid academic year.";
    }
    if (!in_array($data["gender"], ["Male", "Female", "Other"], true)) {
        $errors[] = "Select a valid gender.";
    }
}

$flash = $_SESSION['student_flash'] ?? null;
unset($_SESSION['student_flash']);

$errors = [];
$editingStudent = null;
$search = trim($_GET["search"] ?? "");
$courseFilter = trim($_GET["course"] ?? "");
$yearFilter = trim($_GET["year"] ?? "");

/* Handle add, update, and delete requests. */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $postedToken = $_POST["csrf_token"] ?? "";
    if (!is_string($postedToken) || !hash_equals($_SESSION["student_csrf_token"], $postedToken)) {
        redirectWithMessage("Security check failed. Please try again.", "error");
    }

    $action = $_POST["action"] ?? "";

    if ($action === "delete") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            redirectWithMessage("Invalid student ID.", "error");
        }

        $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
        if (!$stmt) {
            redirectWithMessage("Could not prepare the delete request.", "error");
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();

        redirectWithMessage($deleted ? "Student deleted successfully." : "Student was not found.", $deleted ? "success" : "error");
    }

    if ($action === "add" || $action === "update") {
        $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
        $data = [
            "name" => trim($_POST["name"] ?? ""),
            "username" => trim($_POST["username"] ?? ""),
            "email" => trim($_POST["email"] ?? ""),
            "mobile" => trim($_POST["mobile"] ?? ""),
            "course" => trim($_POST["course"] ?? ""),
            "year" => trim($_POST["year"] ?? ""),
            "gender" => trim($_POST["gender"] ?? "")
        ];

        validStudentInput($data, $errors);

        if ($action === "update" && (!$id || $id < 1)) {
            $errors[] = "Invalid student ID.";
        }

        /* Check duplicate username/email without changing the existing table. */
        if (!$errors) {
            if ($action === "add") {
                $stmt = $conn->prepare("SELECT id FROM studentS WHERE username = ? OR email = ? LIMIT 1");
                $stmt->bind_param("ss", $data["username"], $data["email"]);
            } else {
                $stmt = $conn->prepare("SELECT id FROM studentS WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1");
                $stmt->bind_param("ssi", $data["username"], $data["email"], $id);
            }
            $stmt->execute();
            $duplicate = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($duplicate) {
                $errors[] = "That username or email is already in use by another student.";
            }
        }

        if (!$errors && $action === "add") {
            $password = $_POST["password"] ?? "";
            if (strlen($password) < 8) {
                $errors[] = "The initial password must contain at least 8 characters.";
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $role = "student";
                $year = (int)$data["year"];

                $stmt = $conn->prepare(
                    "INSERT INTO students (name, username, email, mobile, password, course, year, gender, role)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                if (!$stmt) {
                    $errors[] = "Could not prepare the add request.";
                } else {
                    $stmt->bind_param(
                        "ssssssiss",
                        $data["name"], $data["username"], $data["email"], $data["mobile"],
                        $passwordHash, $data["course"], $year, $data["gender"], $role
                    );
                    if ($stmt->execute()) {
                        $stmt->close();
                        redirectWithMessage("Student added successfully.");
                    }
                    $stmt->close();
                    $errors[] = "Could not add the student. Check the details and try again.";
                }
            }
        }

        if (!$errors && $action === "update") {
            $year = (int)$data["year"];
            $stmt = $conn->prepare(
                "UPDATE students SET name = ?, username = ?, email = ?, mobile = ?, course = ?, year = ?, gender = ?
                 WHERE id = ?"
            );
            if (!$stmt) {
                $errors[] = "Could not prepare the update request.";
            } else {
                $stmt->bind_param(
                    "sssssisi",
                    $data["name"], $data["username"], $data["email"], $data["mobile"],
                    $data["course"], $year, $data["gender"], $id
                );
                if ($stmt->execute()) {
                    $stmt->close();
                    redirectWithMessage("Student details updated successfully.");
                }
                $stmt->close();
                $errors[] = "Could not update the student. Check the details and try again.";
            }
        }

        if ($errors) {
            $editingStudent = $data;
            if ($action === "update") {
                $editingStudent["id"] = $id;
            }
        }
    }
}

/* Load an existing student into the edit form. */
if ($editingStudent === null && isset($_GET["edit"])) {
    $editId = filter_var($_GET["edit"], FILTER_VALIDATE_INT);
    if ($editId && $editId > 0) {
        $stmt = $conn->prepare("SELECT id, name, username, email, mobile, course, year, gender FROM studentS WHERE id = ?");
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editingStudent = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$editingStudent) {
            $flash = ["message" => "Student not found.", "type" => "error"];
        }
    }
}

/* Build the filtered student list. Password hashes are never selected. */
$sql = "SELECT id, name, username, email, mobile, course, year, gender, created_at, role FROM studentS WHERE 1=1";
$types = "";
$params = [];

if ($search !== "") {
    $sql .= " AND (name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $term = "%" . $search . "%";
    $types .= "sss";
    array_push($params, $term, $term, $term);
}
if ($courseFilter !== "" && in_array($courseFilter, ["BCA", "B.Tech", "BBA", "MCA"], true)) {
    $sql .= " AND course = ?";
    $types .= "s";
    $params[] = $courseFilter;
}
if ($yearFilter !== "" && in_array($yearFilter, ["1", "2", "3", "4"], true)) {
    $sql .= " AND year = ?";
    $types .= "i";
    $params[] = (int)$yearFilter;
}
$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$students = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$form = $editingStudent ?? [
    "id" => "",
    "name" => "",
    "username" => "",
    "email" => "",
    "mobile" => "",
    "course" => "",
    "year" => "",
    "gender" => ""
];
$isEditing = !empty($form["id"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management | StudentHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f5f7fb; color: #1e293b; }
        header { min-height: 70px; background: #fff; padding: 14px 6%; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
        .logo { font-size: 24px; font-weight: 700; color: #1e293b; text-decoration: none; }
        .logo span { color: #2563eb; }
        .nav-links { display: flex; gap: 18px; align-items: center; flex-wrap: wrap; }
        .nav-links a { color: #475569; text-decoration: none; font-size: 14px; font-weight: 500; }
        .nav-links a:hover, .nav-links a.active { color: #2563eb; font-weight: 600; }
        main { width: min(1400px, 92%); margin: 30px auto; }
        .page-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 22px; }
        h1 { font-size: clamp(23px, 3vw, 30px); }
        h2 { font-size: 20px; margin-bottom: 18px; }
        .muted { color: #64748b; font-size: 13px; margin-top: 5px; }
        .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 24px; margin-bottom: 22px; box-shadow: 0 4px 14px rgba(15, 23, 42, .04); }
        .form-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 7px; }
        input, select { width: 100%; padding: 11px 12px; border: 1px solid #cbd5e1; border-radius: 8px; color: #1e293b; background: #fff; font-size: 14px; outline: none; }
        input:focus, select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .1); }
        .field { min-width: 0; }
        .actions { display: flex; align-items: center; gap: 9px; flex-wrap: wrap; margin-top: 20px; }
        .btn { display: inline-flex; justify-content: center; align-items: center; border: 0; border-radius: 8px; padding: 10px 15px; text-decoration: none; cursor: pointer; font-size: 13px; font-weight: 600; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-light { background: #eaf0f8; color: #334155; }
        .btn-edit { background: #eff6ff; color: #1d4ed8; }
        .btn-delete { background: #fef2f2; color: #b91c1c; }
        .alert { padding: 12px 15px; border-radius: 9px; margin-bottom: 18px; font-size: 14px; }
        .alert-success { color: #166534; background: #f0fdf4; border: 1px solid #bbf7d0; }
        .alert-error { color: #991b1b; background: #fef2f2; border: 1px solid #fecaca; }
        .filter-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto auto; gap: 10px; align-items: end; }
        .table-wrap { overflow-x: auto; width: 100%; }
        table { width: 100%; border-collapse: collapse; min-width: 1050px; font-size: 13px; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        th { color: #475569; background: #f8fafc; font-weight: 600; white-space: nowrap; }
        tbody tr:hover { background: #f8fafc; }
        .table-actions { display: flex; gap: 7px; align-items: center; }
        .table-actions .btn { padding: 7px 10px; }
        .empty { text-align: center; color: #64748b; padding: 28px; }
        .count { color: #64748b; font-size: 13px; margin-bottom: 12px; }
        @media (max-width: 850px) { .form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .filter-grid { grid-template-columns: 1fr 1fr; } .filter-grid .search-field { grid-column: 1 / -1; } }
        @media (max-width: 560px) { header { padding: 14px 4%; } .nav-links { gap: 12px; } main { width: 94%; margin: 20px auto; } .panel { padding: 17px; } .form-grid, .filter-grid { grid-template-columns: 1fr; } .filter-grid .search-field { grid-column: auto; } .page-heading { align-items: flex-start; } }
    </style>
</head>
<body>
<header>
    <a class="logo" href="dashboard.php">🎓 Student<span>Hub</span></a>
    <nav class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a class="active" href="student.php">Students</a>
        <a href="events.php">Events</a>
        <a href="registrations.php">Registrations</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main>
    <div class="page-heading">
        <div>
            <h1>Student Management</h1>
            <p class="muted">Add, update, search and manage student records.</p>
        </div>
    </div>

    <?php if ($flash): ?>
        <div class="alert <?= $flash["type"] === "error" ? "alert-error" : "alert-success" ?>"><?= h($flash["message"]) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-error">
            <strong>Please correct the following:</strong>
            <ul style="margin: 8px 0 0 20px;">
                <?php foreach ($errors as $error): ?><li><?= h($error) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <section class="panel" id="student-form">
        <h2><?= $isEditing ? "Edit Student" : "Add New Student" ?></h2>
        <form method="POST" action="student.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION["student_csrf_token"]) ?>">
            <input type="hidden" name="action" value="<?= $isEditing ? "update" : "add" ?>">
            <?php if ($isEditing): ?><input type="hidden" name="id" value="<?= h($form["id"]) ?>"><?php endif; ?>

            <div class="form-grid">
                <div class="field">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" maxlength="100" required value="<?= h($form["name"]) ?>">
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" maxlength="50" required value="<?= h($form["username"]) ?>">
                </div>
                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" maxlength="100" required value="<?= h($form["email"]) ?>">
                </div>
                <div class="field">
                    <label for="mobile">Mobile</label>
                    <input id="mobile" name="mobile" type="tel" maxlength="15" required value="<?= h($form["mobile"]) ?>">
                </div>
                <div class="field">
                    <label for="course">Course</label>
                    <select id="course" name="course" required>
                        <option value="">Select course</option>
                        <?php foreach (["BCA", "B.Tech", "BBA", "MCA"] as $course): ?>
                            <option value="<?= h($course) ?>" <?= ($form["course"] ?? "") === $course ? "selected" : "" ?>><?= h($course) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="year">Academic Year</label>
                    <select id="year" name="year" required>
                        <option value="">Select year</option>
                        <?php for ($y = 1; $y <= 4; $y++): ?>
                            <option value="<?= $y ?>" <?= (string)($form["year"] ?? "") === (string)$y ? "selected" : "" ?>>Year <?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender" required>
                        <option value="">Select gender</option>
                        <?php foreach (["Male", "Female", "Other"] as $gender): ?>
                            <option value="<?= h($gender) ?>" <?= ($form["gender"] ?? "") === $gender ? "selected" : "" ?>><?= h($gender) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!$isEditing): ?>
                    <div class="field">
                        <label for="password">Initial Password</label>
                        <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
                    </div>
                <?php endif; ?>
            </div>

            <div class="actions">
                <button class="btn btn-primary" type="submit"><?= $isEditing ? "Save Changes" : "Add Student" ?></button>
                <?php if ($isEditing): ?><a class="btn btn-light" href="student.php">Cancel</a><?php endif; ?>
            </div>
            <?php if (!$isEditing): ?><p class="muted">The initial password will be securely hashed before it is saved.</p><?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <h2>Student Records</h2>
        <form method="GET" action="student.php" class="filter-grid">
            <div class="field search-field">
                <label for="search">Search</label>
                <input id="search" name="search" placeholder="Name, username or email" value="<?= h($search) ?>">
            </div>
            <div class="field">
                <label for="course-filter">Course</label>
                <select id="course-filter" name="course">
                    <option value="">All courses</option>
                    <?php foreach (["BCA", "B.Tech", "BBA", "MCA"] as $course): ?>
                        <option value="<?= h($course) ?>" <?= $courseFilter === $course ? "selected" : "" ?>><?= h($course) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="year-filter">Year</label>
                <select id="year-filter" name="year">
                    <option value="">All years</option>
                    <?php for ($y = 1; $y <= 4; $y++): ?>
                        <option value="<?= $y ?>" <?= $yearFilter === (string)$y ? "selected" : "" ?>>Year <?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Apply</button>
            <a class="btn btn-light" href="student.php">Reset</a>
        </form>

        <p class="count" style="margin-top: 18px;">Showing <?= count($students) ?> student record(s).</p>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Mobile</th>
                        <th>Course</th><th>Year</th><th>Gender</th><th>Role</th><th>Created At</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!$students): ?>
                    <tr><td colspan="11" class="empty">No student records match your search.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= h($student["id"]) ?></td>
                            <td><?= h($student["name"]) ?></td>
                            <td><?= h($student["username"]) ?></td>
                            <td><?= h($student["email"]) ?></td>
                            <td><?= h($student["mobile"]) ?></td>
                            <td><?= h($student["course"]) ?></td>
                            <td><?= h($student["year"]) ?></td>
                            <td><?= h($student["gender"]) ?></td>
                            <td><?= h($student["role"]) ?></td>
                            <td><?= h($student["created_at"]) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a class="btn btn-edit" href="student.php?edit=<?= (int)$student["id"] ?>">Edit</a>
                                    <form method="POST" action="student.php" onsubmit="return confirm('Delete this student record? This action cannot be undone.');">
                                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION["student_csrf_token"]) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int)$student["id"] ?>">
                                        <button class="btn btn-delete" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
