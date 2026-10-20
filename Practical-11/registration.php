
<?php
require "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = $_POST["course"] ?? "";
    $year = filter_var($_POST["year"] ?? "", FILTER_VALIDATE_INT);
    $gender = $_POST["gender"] ?? "";
    $terms = $_POST["terms"] ?? "";

    $validCourses = ["BCA", "B.Tech", "BBA", "MCA"];
    $validGenders = ["Male", "Female", "Other"];

    if (
        $name === "" || $username === "" || $email === "" ||
        $mobile === "" || $password === "" ||
        $confirmPassword === "" || $course === "" ||
        $year === false || $year === null ||
        $gender === "" || $terms !== "accepted"
    ) {
        $message = "Please complete all fields and accept the terms.";
        $messageType = "error";
    } elseif (!preg_match("/^[a-zA-Z ]{2,100}$/", $name)) {
        $message = "Enter a valid name.";
        $messageType = "error";
    } elseif (!preg_match("/^[a-zA-Z0-9_]{3,50}$/", $username)) {
        $message = "Username must be 3-50 characters using letters, numbers, or underscore.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) ||
              strlen($email) > 100) {
        $message = "Enter a valid email address.";
        $messageType = "error";
    } elseif (!preg_match("/^[0-9]{10,15}$/", $mobile)) {
        $message = "Enter a valid mobile number with 10-15 digits.";
        $messageType = "error";
    } elseif (strlen($password) < 8) {
        $message = "Password must contain at least 8 characters.";
        $messageType = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (!in_array($course, $validCourses, true)) {
        $message = "Select a valid course.";
        $messageType = "error";
    } elseif ($year < 1 || $year > 4) {
        $message = "Year must be between 1 and 4.";
        $messageType = "error";
    } elseif (!in_array($gender, $validGenders, true)) {
        $message = "Select a valid gender.";
        $messageType = "error";
    } else {
        $stmt = $conn->prepare(
            "SELECT id FROM students WHERE username = ? OR email = ?"
        );
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username or email already exists.";
            $messageType = "error";
            $stmt->close();
        } else {
            $stmt->close();

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO students
                (name, username, email, mobile, password, course, year, gender)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssssis",
                $name,
                $username,
                $email,
                $mobile,
                $hashedPassword,
                $course,
                $year,
                $gender
            );

            if ($stmt->execute()) {
                $message = "Registration successful!";
                $messageType = "success";
            } else {
                if ($stmt->errno === 1062) {
                    $message = "Username or email already exists.";
                } else {
                    $message = "Registration failed. Please try again.";
                }
                $messageType = "error";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link rel="stylesheet" href="registration.css">
</head>
<body>

<div class="container">
    <h2>Student Registration Form</h2>

    <?php if ($message !== ""): ?>
        <p class="<?php echo $messageType; ?>" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php endif; ?>

    <form id="registrationForm" method="POST" action="registration.php">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name"
               placeholder="Enter your name" required
               maxlength="100"
               value="<?php echo htmlspecialchars($_POST["name"] ?? "", ENT_QUOTES, "UTF-8"); ?>">

        <label for="username">Username:</label>
        <input type="text" id="username" name="username"
               placeholder="Choose a username" required
               minlength="3" maxlength="50"
               pattern="[a-zA-Z0-9_]{3,50}"
               value="<?php echo htmlspecialchars($_POST["username"] ?? "", ENT_QUOTES, "UTF-8"); ?>">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email"
               placeholder="Enter your email" required
               maxlength="100"
               value="<?php echo htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES, "UTF-8"); ?>">

        <label for="mobile">Mobile Number:</label>
        <input type="tel" id="mobile" name="mobile"
               placeholder="Enter mobile number" required
               pattern="[0-9]{10,15}" maxlength="15"
               value="<?php echo htmlspecialchars($_POST["mobile"] ?? "", ENT_QUOTES, "UTF-8"); ?>">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password"
               placeholder="Enter password" required minlength="8"
               autocomplete="new-password">

        <label for="confirmPassword">Confirm Password:</label>
        <input type="password" id="confirmPassword" name="confirmPassword"
               placeholder="Confirm password" required minlength="8"
               autocomplete="new-password">

        <label for="course">Course:</label>
        <select id="course" name="course" required>
            <option value="">Select Course</option>
            <option value="BCA">BCA</option>
            <option value="B.Tech">B.Tech</option>
            <option value="BBA">BBA</option>
            <option value="MCA">MCA</option>
        </select>

        <label for="year">Year:</label>
        <input type="number" id="year" name="year"
               min="1" max="4" required
               placeholder="Enter year">

        <label>Gender:</label>
        <div class="gender">
            <input type="radio" id="male" name="gender" value="Male" required>
            <label for="male">Male</label>

            <input type="radio" id="female" name="gender" value="Female">
            <label for="female">Female</label>

            <input type="radio" id="other" name="gender" value="Other">
            <label for="other">Other</label>
        </div>

        <div class="terms">
            <input type="checkbox" id="terms" name="terms"
                   value="accepted" required>
            <label for="terms">I accept the Terms and Conditions</label>
        </div>

        <p id="message"></p>

        <button type="submit">Register</button>
    </form>
</div>

<script src="registration.js"></script>

</body>
</html>
