
<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../student_db.php";

function respond($status, $data) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    respond(401, ["success" => false, "message" => "Admin login required."]);
}

if (empty($_SESSION["api_csrf_token"])) {
    $_SESSION["api_csrf_token"] = bin2hex(random_bytes(32));
}

$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $stmt = $conn->prepare(
        "SELECT id, name, username, email, mobile, course, year,
                gender, role, created_at
         FROM students ORDER BY id DESC"
    );

    if (!$stmt || !$stmt->execute()) {
        respond(500, ["success" => false, "message" => "Could not load students."]);
    }

    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    respond(200, [
        "success" => true,
        "csrf_token" => $_SESSION["api_csrf_token"],
        "students" => $students
    ]);
}

if (!in_array($method, ["POST", "PUT", "DELETE"], true)) {
    header("Allow: GET, POST, PUT, DELETE");
    respond(405, ["success" => false, "message" => "Method not allowed."]);
}

$token = $_SERVER["HTTP_X_CSRF_TOKEN"] ?? "";

if (!is_string($token) ||
    !hash_equals($_SESSION["api_csrf_token"], $token)) {
    respond(403, ["success" => false, "message" => "Security check failed. Refresh the page."]);
}

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    respond(400, ["success" => false, "message" => "Invalid JSON data."]);
}

if ($method === "DELETE") {
    $id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

    if (!$id || $id < 1) {
        respond(400, ["success" => false, "message" => "Invalid student ID."]);
    }

    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();

    if (!$deleted) {
        respond(404, ["success" => false, "message" => "Student not found."]);
    }

    respond(200, ["success" => true, "message" => "Student deleted successfully."]);
}

$id = filter_var($input["id"] ?? null, FILTER_VALIDATE_INT);

$name = trim($input["name"] ?? "");
$username = trim($input["username"] ?? "");
$email = trim($input["email"] ?? "");
$mobile = trim($input["mobile"] ?? "");
$course = trim($input["course"] ?? "");
$year = (string)($input["year"] ?? "");
$gender = trim($input["gender"] ?? "");

if (
    $name === "" || mb_strlen($name) > 100 ||
    $username === "" || mb_strlen($username) > 50 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100 ||
    !preg_match('/^[0-9+().\-\s]{7,15}$/', $mobile) ||
    !in_array($course, ["BCA", "B.Tech", "BBA", "MCA"], true) ||
    !in_array($year, ["1", "2", "3", "4"], true) ||
    !in_array($gender, ["Male", "Female", "Other"], true)
) {
    respond(422, ["success" => false, "message" => "Please enter valid student details."]);
}

if ($method === "PUT" && (!$id || $id < 1)) {
    respond(400, ["success" => false, "message" => "Invalid student ID."]);
}

if ($method === "POST") {
    $password = $input["password"] ?? "";

    if (!is_string($password) || strlen($password) < 8) {
        respond(422, ["success" => false, "message" => "Password must contain at least 8 characters."]);
    }

    $check = $conn->prepare(
        "SELECT id FROM students WHERE username = ? OR email = ? LIMIT 1"
    );
    $check->bind_param("ss", $username, $email);
} else {
    $check = $conn->prepare(
        "SELECT id FROM students
         WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1"
    );
    $check->bind_param("ssi", $username, $email, $id);
}

$check->execute();
$duplicate = $check->get_result()->fetch_assoc();
$check->close();

if ($duplicate) {
    respond(409, ["success" => false, "message" => "Username or email already exists."]);
}

$yearNumber = (int)$year;

if ($method === "POST") {
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $role = "student";

    $stmt = $conn->prepare(
        "INSERT INTO students
         (name, username, email, mobile, password, course, year, gender, role)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        "ssssssiss",
        $name, $username, $email, $mobile,
        $passwordHash, $course, $yearNumber, $gender, $role
    );

    if (!$stmt->execute()) {
        $stmt->close();
        respond(500, ["success" => false, "message" => "Could not add student."]);
    }

    $stmt->close();
    respond(201, ["success" => true, "message" => "Student added successfully."]);
}

$stmt = $conn->prepare(
    "UPDATE students
     SET name = ?, username = ?, email = ?, mobile = ?,
         course = ?, year = ?, gender = ?
     WHERE id = ?"
);

$stmt->bind_param(
    "sssssisi",
    $name, $username, $email, $mobile,
    $course, $yearNumber, $gender, $id
);

if (!$stmt->execute()) {
    $stmt->close();
    respond(500, ["success" => false, "message" => "Could not update student."]);
}

$changed = $stmt->affected_rows;
$stmt->close();

if ($changed === 0) {
    $check = $conn->prepare("SELECT id FROM students WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$exists) {
        respond(404, ["success" => false, "message" => "Student not found."]);
    }
}

respond(200, ["success" => true, "message" => "Student updated successfully."]);

