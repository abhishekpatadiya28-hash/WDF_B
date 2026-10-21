
<?php
require "db.php";

$sql = "SELECT 
            registrations.registration_id,
            registrations.student_id,
            students.name AS student_name,
            registrations.event_id,
            events.event_name,
            registrations.registered_at
        FROM registrations
        INNER JOIN students 
            ON registrations.student_id = students.id
        INNER JOIN events 
            ON registrations.event_id = events.event_id
        ORDER BY registrations.registered_at DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Registrations</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 30px;
            background: #f5f7fb;
            color: #1e293b;
            font-family: Poppins, Arial, sans-serif;
        }
        .container {
            max-width: 1100px;
            margin: 30px auto;
        }
        h1 { margin-bottom: 8px; }
        p { color: #64748b; }
        .table-card {
            margin-top: 25px;
            padding: 24px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }
        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th { background: #eff6ff; }
        .back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .empty { padding: 20px; color: #64748b; }
    </style>
</head>
<body>
<div class="container">
    <h1>Event Registrations</h1>
    <p>View students registered for college events.</p>

    <div class="table-card">
        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Registration ID</th>
                        <th>Student Name</th>
                        <th>Event Name</th>
                        <th>Registration Date</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["registration_id"]) ?></td>
                        <td><?= htmlspecialchars($row["student_name"]) ?></td>
                        <td><?= htmlspecialchars($row["event_name"]) ?></td>
                        <td><?= htmlspecialchars($row["registered_at"]) ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty">No event registrations found.</div>
        <?php endif; ?>
    </div>

    <a class="back" href="admin_dashboard.php">Back to Admin Dashboard</a>
</div>
</body>
</html>
