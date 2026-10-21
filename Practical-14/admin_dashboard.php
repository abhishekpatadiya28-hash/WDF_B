
<?php
require_once "auth.php";
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$name = $_SESSION["name"] ?? "Administrator";

$totalUsers = 0;
$totalStudents = 0;
$totalEvents = 0;
$totalRegistrations = 0;

$errors = [];
$latestStudents = [];
$latestRegistrations = [];

$userResult = $conn->query(
    "SELECT COUNT(*) AS total FROM students"
);

if ($userResult) {
    $totalUsers = (int) $userResult->fetch_assoc()["total"];
} else {
    $errors[] = "Unable to load user count.";
}

$studentResult = $conn->query(
    "SELECT COUNT(*) AS total FROM students WHERE role = 'student'"
);

if ($studentResult) {
    $totalStudents = (int) $studentResult->fetch_assoc()["total"];
} else {
    $errors[] = "Unable to load student count.";
}

$eventResult = $conn->query(
    "SELECT COUNT(*) AS total FROM events"
);

if ($eventResult) {
    $totalEvents = (int) $eventResult->fetch_assoc()["total"];
} else {
    $errors[] = "Unable to load event count.";
}

$registrationResult = $conn->query(
    "SELECT COUNT(*) AS total FROM registrations"
);

if ($registrationResult) {
    $totalRegistrations = (int) $registrationResult->fetch_assoc()["total"];
} else {
    $errors[] = "Unable to load event registration count.";
}

$studentListResult = $conn->query(
    "SELECT id, name, username, email, course, year
     FROM students
     ORDER BY id DESC
     LIMIT 5"
);

if ($studentListResult) {
    while ($row = $studentListResult->fetch_assoc()) {
        $latestStudents[] = $row;
    }
} else {
    $errors[] = "Unable to load latest student records.";
}

$registrationListResult = $conn->query(
    "SELECT
        r.registration_id,
        r.registered_at,
        s.name AS student_name,
        s.username,
        e.event_name
     FROM registrations r
     INNER JOIN students s ON r.student_id = s.id
     INNER JOIN events e ON r.event_id = e.event_id
     ORDER BY r.registered_at DESC, r.registration_id DESC
     LIMIT 5"
);

if ($registrationListResult) {
    while ($row = $registrationListResult->fetch_assoc()) {
        $latestRegistrations[] = $row;
    }
} else {
    $errors[] = "Unable to load latest event registrations.";
}

function escapeHtml($value) {
    return htmlspecialchars((string) ($value ?? ""), ENT_QUOTES, "UTF-8");
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

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Poppins", Arial, sans-serif;
            background: #f5f7fb;
            color: #1e293b;
        }

        header {
            min-height: 76px;
            background: #fff;
            padding: 14px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo {
            font-size: 27px;
            font-weight: 700;
            white-space: nowrap;
        }

        .logo span {
            color: #2563eb;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        nav a {
            color: #334155;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        nav a:hover,
        nav a.active {
            color: #2563eb;
        }

        .logout-btn {
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 13px 23px;
            font-family: inherit;
            font-size: 14px;
            cursor: pointer;
        }

        .logout-btn:hover {
            background: #1d4ed8;
        }

        .container {
            width: 90%;
            max-width: 1500px;
            margin: 45px auto;
        }

        .welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 48px;
        }

        .page-label {
            color: #2563eb;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1.5px;
        }

        .welcome h1 {
            font-size: clamp(26px, 3vw, 38px);
            margin: 12px 0;
        }

        .welcome p:last-child {
            color: #64748b;
        }

        .admin-badge {
            background: #eff6ff;
            color: #2563eb;
            border-radius: 30px;
            padding: 12px 20px;
            font-size: 13px;
            font-weight: 600;
        }

        .section-title {
            font-size: 25px;
            margin: 0 0 24px;
        }

        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 45px;
        }

        .card,
        .data-section,
        .action-card,
        .notice {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
        }

        .card {
            padding: 27px;
        }

        .card-icon {
            font-size: 29px;
            margin-bottom: 22px;
        }

        .card h3 {
            color: #64748b;
            font-size: 15px;
            font-weight: 500;
            margin: 0 0 14px;
        }

        .card h2 {
            color: #2563eb;
            font-size: 34px;
            margin: 0 0 12px;
        }

        .card p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }

        .data-section {
            padding: 28px;
            margin-bottom: 35px;
            overflow: hidden;
        }

        .data-section h2 {
            font-size: 24px;
            margin: 0 0 24px;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th,
        td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #f1f5f9;
            font-weight: 700;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .empty-row {
            text-align: center;
            color: #64748b;
            padding: 25px;
        }

        .error-message {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 14px;
        }

        .admin-actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 35px;
        }

        .action-card {
            padding: 25px;
        }

        .action-icon {
            font-size: 28px;
        }

        .action-card h3 {
            margin: 15px 0 10px;
        }

        .action-card p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.7;
        }

        .action-card a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }

        .notice {
            display: flex;
            align-items: flex-start;
            gap: 18px;
            padding: 25px;
            margin-bottom: 35px;
        }

        .notice-icon {
            font-size: 25px;
        }

        .notice h3 {
            margin: 0 0 8px;
        }

        .notice p {
            color: #64748b;
            margin: 0;
            font-size: 14px;
        }

        footer {
            background: #1e293b;
            color: #cbd5e1;
            text-align: center;
            padding: 23px;
            font-size: 13px;
        }

        @media (max-width: 1100px) {
            .dashboard-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .admin-actions {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            header {
                justify-content: center;
                padding: 20px;
            }

            nav {
                gap: 13px;
            }

            .container {
                width: 94%;
                margin: 30px auto;
            }

            .dashboard-cards,
            .admin-actions {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .welcome {
                margin-bottom: 35px;
            }

            .data-section {
                padding: 18px;
            }

            .section-title,
            .data-section h2 {
                font-size: 21px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">🎓 Student<span>Hub</span></div>

    <nav>
        <a href="admin_dashboard.php" class="active">Admin Dashboard</a>
        <a href="dashboard.php">Student Site</a>
        <a href="students.php">Students</a>
        <a href="events.php">Events</a>
        <a href="register_event.php">Register Event</a>
        <a href="registrations.php">Registrations</a>
    </nav>

    <form action="logout.php" method="POST">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</header>

<main class="container">

    <section class="welcome">
        <div>
            <p class="page-label">ADMINISTRATION</p>
            <h1>Welcome Back, <?= escapeHtml($name) ?> 👋</h1>
            <p>Manage your StudentHub website from one place.</p>
        </div>

        <span class="admin-badge">Administrator</span>
    </section>

    <h2 class="section-title">Dashboard Overview</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <div><?= escapeHtml($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="dashboard-cards">

        <div class="card">
            <div class="card-icon">👥</div>
            <h3>Total Users</h3>
            <h2><?= $totalUsers ?></h2>
            <p>All records in the students table</p>
        </div>

        <div class="card">
            <div class="card-icon">🎓</div>
            <h3>Total Students</h3>
            <h2><?= $totalStudents ?></h2>
            <p>Accounts with the student role</p>
        </div>

        <div class="card">
            <div class="card-icon">🗓️</div>
            <h3>Total Events</h3>
            <h2><?= $totalEvents ?></h2>
            <p>Events stored in the database</p>
        </div>

        <div class="card">
            <div class="card-icon">📝</div>
            <h3>Event Registrations</h3>
            <h2><?= $totalRegistrations ?></h2>
            <p>Registrations for college events</p>
        </div>

    </section>

    <section class="data-section">
        <h2>Latest Student Records</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($latestStudents)): ?>
                        <tr>
                            <td colspan="6" class="empty-row">
                                No student records found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($latestStudents as $student): ?>
                            <tr>
                                <td><?= (int) $student["id"] ?></td>
                                <td><?= escapeHtml($student["name"]) ?></td>
                                <td><?= escapeHtml($student["username"]) ?></td>
                                <td><?= escapeHtml($student["email"]) ?></td>
                                <td><?= escapeHtml($student["course"]) ?></td>
                                <td><?= escapeHtml($student["year"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="data-section">
        <h2>Latest Event Registrations</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>Student Name</th>
                        <th>Username</th>
                        <th>Event Name</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($latestRegistrations)): ?>
                        <tr>
                            <td colspan="5" class="empty-row">
                                No event registrations found yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($latestRegistrations as $registration): ?>
                            <tr>
                                <td><?= (int) $registration["registration_id"] ?></td>
                                <td><?= escapeHtml($registration["student_name"]) ?></td>
                                <td><?= escapeHtml($registration["username"]) ?></td>
                                <td><?= escapeHtml($registration["event_name"]) ?></td>
                                <td><?= escapeHtml($registration["registered_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <h2 class="section-title">Admin Quick Access</h2>

    <section class="admin-actions">

        <div class="action-card">
            <div class="action-icon">👥</div>
            <h3>Manage Students</h3>
            <p>View student accounts and their academic details.</p>
            <a href="students.php">View Students →</a>
        </div>

        <div class="action-card">
            <div class="action-icon">📅</div>
            <h3>Manage Events</h3>
            <p>View and manage college events.</p>
            <a href="events.php">Manage Events →</a>
        </div>

        <div class="action-card">
            <div class="action-icon">📝</div>
            <h3>Register for Events</h3>
            <p>Register students for college events.</p>
            <a href="register_event.php">Register Event →</a>
        </div>

        <div class="action-card">
            <div class="action-icon">📋</div>
            <h3>View Registrations</h3>
            <p>View student registrations for college events.</p>
            <a href="registrations.php">View Registrations →</a>
        </div>

        <div class="action-card">
            <div class="action-icon">📚</div>
            <h3>Assignments</h3>
            <p>Open the academic assignment section.</p>
            <a href="Assignments.html">Open Assignments →</a>
        </div>

        <div class="action-card">
            <div class="action-icon">📖</div>
            <h3>Study Notes</h3>
            <p>Open learning resources for students.</p>
            <a href="notes.html">Open Notes →</a>
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
    <p>© <?= date("Y") ?> StudentHub. All rights reserved.</p>
</footer>

</body>
</html>
