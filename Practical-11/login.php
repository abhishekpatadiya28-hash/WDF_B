
<?php
require "db.php";

session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax"
]);

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: " . ($_SESSION["role"] === "admin"
        ? "admin_dashboard.php" : "dashboard.php"));
    exit;
}

$message = "";

if (isset($_GET["timeout"])) {
    $message = "Your session expired. Please log in again.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identity = trim($_POST["identity"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($identity === "" || $password === "") {
        $message = "Enter your username/email and password.";
    } else {
        $stmt = $conn->prepare(
            "SELECT id, name, username, email, password, role
             FROM students
             WHERE username = ? OR email = ?
             LIMIT 1"
        );

        $stmt->bind_param("ss", $identity, $identity);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user["password"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["last_activity"] = time();

            if (password_needs_rehash($user["password"], PASSWORD_DEFAULT)) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare(
                    "UPDATE students SET password = ? WHERE id = ?"
                );
                $update->bind_param("si", $newHash, $user["id"]);
                $update->execute();
                $update->close();
            }

            header("Location: " . ($user["role"] === "admin"
                ? "admin_dashboard.php" : "dashboard.php"));
            exit;
        } else {
            $message = "Invalid username/email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Login</title>
    <link rel="stylesheet" href="registration.css">
</head>
<body>
<div class="container">
    <h2>StudentHub Login</h2>

    <?php if ($message !== ""): ?>
        <p class="error" role="alert">
            <?php echo htmlspecialchars($message, ENT_QUOTES, "UTF-8"); ?>
        </p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="identity">Username or Email:</label>
        <input type="text" id="identity" name="identity"
               autocomplete="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password"
               autocomplete="current-password" required>

        <button type="submit">Login</button>
    </form>

    <p>New student? <a href="registration.php">Register here</a></p>
</div>
</body>
</html>
