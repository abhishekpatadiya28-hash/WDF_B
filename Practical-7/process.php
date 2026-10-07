<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: registration.html");
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$mobile = trim($_POST["mobile"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirmPassword"] ?? "";
$course = trim($_POST["course"] ?? "");
$year = trim($_POST["year"] ?? "");
$gender = trim($_POST["gender"] ?? "");
$terms = $_POST["terms"] ?? "";

$name = htmlspecialchars($name, ENT_QUOTES, "UTF-8");
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$mobile = preg_replace("/\D/", "", $mobile);
$course = htmlspecialchars($course, ENT_QUOTES, "UTF-8");
$year = htmlspecialchars($year, ENT_QUOTES, "UTF-8");
$gender = htmlspecialchars($gender, ENT_QUOTES, "UTF-8");

$errors = [];

if ($name === "" || !preg_match("/^[a-zA-Z ]{2,50}$/", $name)) {
    $errors[] = "Please enter a valid name.";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $errors[] = "Mobile number must contain exactly 10 digits.";
}

if (strlen($password) < 6) {
    $errors[] = "Password must contain at least 6 characters.";
}

if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}

$allowedCourses = ["BCA", "B.Tech", "BBA", "MCA"];

if (!in_array($course, $allowedCourses, true)) {
    $errors[] = "Please select a valid course.";
}

if (!filter_var($year, FILTER_VALIDATE_INT) || (int)$year < 1 || (int)$year > 4) {
    $errors[] = "Year must be between 1 and 4.";
}

$allowedGender = ["Male", "Female", "Other"];

if (!in_array($gender, $allowedGender, true)) {
    $errors[] = "Please select a valid gender.";
}

if ($terms !== "accepted") {
    $errors[] = "You must accept the Terms and Conditions.";
}

if (!empty($errors)) {

    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Registration Error</title>
    </head>
    <body>

    <h2>Registration Failed</h2>";

    foreach ($errors as $error) {
        echo "<p>" . htmlspecialchars($error, ENT_QUOTES, "UTF-8") . "</p>";
    }

    echo '<a href="registration.html">Go Back</a>';

    echo "</body>
    </html>";

    exit;
}

$file = "registrations.csv";

$isNewFile = !file_exists($file) || filesize($file) === 0;

$handle = fopen($file, "a");

if ($handle === false) {

    echo "<h2>Error</h2>";
    echo "<p>Unable to save registration data.</p>";

    exit;
}

if ($isNewFile) {

    fputcsv($handle, [
        "Name",
        "Email",
        "Mobile",
        "Password",
        "Course",
        "Year",
        "Gender",
        "Terms"
    ]);
}

fputcsv($handle, [
    $name,
    $email,
    $mobile,
    password_hash($password, PASSWORD_DEFAULT),
    $course,
    $year,
    $gender,
    "Accepted"
]);

fclose($handle);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Registration Successful</title>
</head>
<body>

<h2>Registration Successful!</h2>

<p>Your registration has been submitted successfully.</p>

<a href='registration.html'>Register Another Student</a>

</body>
</html>";

?>