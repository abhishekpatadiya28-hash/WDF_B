
<?php
require_once "auth.php";
require_once "db.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$adminName = $_SESSION["name"] ?? $_SESSION["username"] ?? "Administrator";
$adminName = htmlspecialchars($adminName, ENT_QUOTES, "UTF-8");

$totalUsers = 0;
$totalStudents = 0;
$totalEvents = 0;
$totalRegistrations = 0;
$recentRegistrations = [];
$errors = [];

function getCount($conn, $sql, &$errors) {
    try {
        $result = $conn->query($sql);
        if ($result) {
            $row = $result->fetch_row();
            return (int)($row[0] ?? 0);
        }
    } catch (mysqli_sql_exception $e) {
        $errors[] = "Unable to load dashboard statistics.";
    }
    return 0;
}

$totalUsers = getCount(
    $conn,
    "SELECT COUNT(*) FROM students",
    $errors
);

$totalStudents = getCount(
    $conn,
    "SELECT COUNT(*) FROM students WHERE role = 'student'",
    $errors
);

$totalEvents = getCount(
    $conn,
    "SELECT COUNT(*) FROM events",
    $errors
);

$totalRegistrations = getCount(
    $conn,
    "SELECT COUNT(*) FROM registrations",
    $errors
);

try {
    $sql = "SELECT
                r.registration_id,
                r.registered_at,
                s.name AS student_name,
                e.event_name
            FROM registrations r
            INNER JOIN students s ON r.student_id = s.id
            INNER JOIN events e ON r.event_id = e.event_id
            ORDER BY r.registered_at DESC
            LIMIT 10";

    $result = $conn->query($sql);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentRegistrations[] = $row;
        }
    }
} catch (mysqli_sql_exception $e) {
    $errors[] = "Unable to load event registrations.";
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", Arial, sans-serif;
            background: #f5f7fb;
            color: #1e293b;
        }

        header {
            min-height: 76px;
            padding: 14px 5%;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
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

        .logout button {
            border: 0;
            border-radius: 9px;
            padding: 13px 24px;
            background: #2563eb;
            color: #fff;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .logout button:hover {
            background: #1d4ed8;
        }

        main {
            width: 90%;
            max-width: 1460px;
            margin: 45px auto;
        }

        .welcome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 48px;
        }

        .eyebrow {
            color: #2563eb;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }

        h1 {
            font-size: clamp(25px, 3vw, 38px);
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .subtitle {
            color: #64748b;
            font-size: 15px;
        }

        .role {
            background: #eff6ff;
            color: #2563eb;
            padding: 12px 22px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        h2 {
            font-size: 25px;
            margin-bottom: 22px;
        }

        .alerts {
            margin-bottom: 20px;
        }

        .alert {
            padding: 14px 18px;
            margin-bottom: 8px;
            border-radius: 9px;
            background: #fef2f2;
            color: #b91c1c;
            overflow-wrap: anywhere;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 27px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .card-icon {
            font-size: 27px;
            margin-bottom: 22px;
        }

        .card-label {
            color: #64748b;
            font-size: 15px;
            margin-bottom: 14px;
        }

        .card-number {
            color: #2563eb;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .card-note {
            color: #64748b;
            font-size: 12px;
        }

        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 15px;
            padding: 28px;
            margin-bottom: 30px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
        }

        .panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .panel-heading h2 {
            margin: 0;
        }

        .view-link {
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 600px;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
        }

        td {
            color: #334155;
        }

        .empty {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .quick-links a {
            display: block;
            padding: 20px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            text-decoration: none;
            color: #1e293b;
            font-weight: 600;
            background: #fff;
        }

        .quick-links a:hover {
            border-color: #2563eb;
            color: #2563eb;
        }

        footer {
            text-align: center;
            padding: 25px 15px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 1100px) {
            .cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            header {
                justify-content: center;
            }
        }

        @media (max-width: 650px) {
            main {
                width: 92%;
                margin-top: 30px;
            }

            header {
                flex-direction: column;
                padding: 18px 4%;
            }

            nav {
                gap: 12px 16px;
            }

            nav a {
                font-size: 13px;
            }

            .welcome {
                align-items: flex-start;
                flex-direction: column;
                margin-bottom: 35px;
            }

            .cards,
            .quick-links {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .card {
                padding: 23px;
            }

            .panel {
                padding: 18px;
            }

            h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">🎓 Student<span>Hub</span></div>

    <nav>
        <a class="active" href="admin_dashboard.php">Admin Dashboard</a>
        <a href="dashboard.php">Student Site</a>
        <a href="students.php">Students</a>
        <a href="events.php">Events</a>
        <a href="register_event.php">Register Event</a>
        <a href="registrations.php">Registrations</a>
    </nav>

    <form class="logout" action="logout.php" method="POST">
        <button type="submit">Logout</button>
    </form>
</header>

<main>
    <section class="welcome">
        <div>
            <p class="eyebrow">ADMINISTRATION</p>
            <h1>Welcome Back, <?= $adminName ?> 👋</h1>
            <p class="subtitle">Manage your StudentHub website from one place.</p>
        </div>
        <span class="role">Administrator</span>
    </section>

    <section>
        <h2>Dashboard Overview</h2>

        <?php if (!empty($errors)): ?>
            <div class="alerts">
                <?php foreach (array_unique($errors) as $error): ?>
                    <div class="alert"><?= h($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="cards">
            <article class="card">
                <div class="card-icon">👥</div>
                <p class="card-label">Total Users</p>
                <p class="card-number"><?= $totalUsers ?></p>
                <p class="card-note">Records in the students table</p>
            </article>

            <article class="card">
                <div class="card-icon">👨‍🎓</div>
                <p class="card-label">Total Students</p>
                <p class="card-number"><?= $totalStudents ?></p>
                <p class="card-note">Accounts with student role</p>
            </article>

            <article class="card">
                <div class="card-icon">🗓️</div>
                <p class="card-label">Total Events</p>
                <p class="card-number"><?= $totalEvents ?></p>
                <p class="card-note">Events stored in the database</p>
            </article>

            <article class="card">
                <div class="card-icon">📝</div>
                <p class="card-label">Event Registrations</p>
                <p class="card-number"><?= $totalRegistrations ?></p>
                <p class="card-note">Registrations for college events</p>
            </article>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Event Registrations</h2>
            <a class="view-link" href="registration.php">View All Registrations →</a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>Student Name</th>
                        <th>Event Name</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentRegistrations)): ?>
                        <tr>
                            <td colspan="4" class="empty">
                                No event registrations found yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentRegistrations as $registration): ?>
                            <tr>
                                <td><?= h($registration["registration_id"]) ?></td>
                                <td><?= h($registration["student_name"]) ?></td>
                                <td><?= h($registration["event_name"]) ?></td>
                                <td><?= h($registration["registered_at"]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Quick Management</h2>
        </div>

        <div class="quick-links">
            <a href="student.php">👨‍🎓 Manage Students →</a>
            <a href="events.php">🗓️ Manage Events →</a>
            <a href="register_event.php">📝 Register for an Event →</a>
            <a href="registration.php">📋 View Registrations →</a>
            <a href="dashboard.php">🏠 Open Student Dashboard →</a>
            <a href="home.html">🌐 Visit StudentHub Website →</a>
        </div>
    </section>
</main>

<footer>
    © <?= date("Y") ?> StudentHub | Admin Dashboard
</footer>

</body>
</html>
