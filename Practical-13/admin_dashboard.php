
<?php
require "auth.php";

if ($_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Admin Dashboard</title>
    <link rel="stylesheet" href="admin_dashboard.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<header>
    <div class="logo">🎓 Student<span>Hub</span></div>

    <nav>
        <a href="dashboard.php">Student Site</a>
        <a href="registration.php">Registrations</a>
    </nav>

    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<main class="container">

    <section class="welcome">
        <div>
            <p class="page-label">ADMINISTRATION</p>
            <h1>Welcome Back, <?php
                echo htmlspecialchars($_SESSION["name"], ENT_QUOTES, "UTF-8");
            ?> 👋</h1>
            <p>Manage your StudentHub website from one place.</p>
        </div>

        <span class="admin-badge">Administrator</span>
    </section>

    <h2 class="section-title">Dashboard Overview</h2>

    <section class="dashboard-cards">

        <div class="card">
            <div class="card-icon">👨‍🎓</div>
            <h3>Total Students</h3>
            <h2>—</h2>
            <p>Registered student accounts</p>
        </div>

        <div class="card">
            <div class="card-icon">📝</div>
            <h3>Assignments</h3>
            <h2>—</h2>
            <p>Academic assignment section</p>
        </div>

        <div class="card">
            <div class="card-icon">📚</div>
            <h3>Study Notes</h3>
            <h2>—</h2>
            <p>Learning resources section</p>
        </div>

        <div class="card">
            <div class="card-icon">📅</div>
            <h3>Attendance</h3>
            <h2>—</h2>
            <p>Student attendance section</p>
        </div>

    </section>

    <h2 class="section-title">Admin Quick Access</h2>

    <section class="admin-actions">

        <div class="action-card">
            <div class="action-icon">👥</div>
            <h3>Manage Students</h3>
            <p>View and manage registered student accounts.</p>
            <a href="student.php">View Students →</a>
        </div>

       

<div class="action-card">
    <div class="action-icon">📅</div>
    <h3>Manage Events</h3>
    <p>Add, view, update and delete college events.</p>
    <a href="manage_events.php">Manage Events →</a>
</div>



        <div class="action-card">
            <div class="action-icon">📋</div>
            <h3>Manage Assignments</h3>
            <p>Open the assignment section for academic work.</p>
            <a href="assignment.html">Open Assignments →</a>
        </div>

    </section>

    <section class="notice">
        <div class="notice-icon">ℹ️</div>
        <div>
            <h3>Admin Information</h3>
            <p>
                Your admin account has access to this dashboard.
                Keep your login credentials secure.
            </p>
        </div>
    </section>

</main>

<footer>
    <p>© 2026 StudentHub. All rights reserved.</p>
</footer>

</body>
</html>
