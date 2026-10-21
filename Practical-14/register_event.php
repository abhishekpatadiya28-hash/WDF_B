
<?php
include "db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {

    $student_id = filter_input(INPUT_POST, "student_id", FILTER_VALIDATE_INT);
    $event_id = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);

    if (!$student_id || !$event_id) {
        $message = "Please select a valid student and event.";
        $message_type = "error";
    } else {
        try {
            // Check that the student exists.
            $checkStudent = $conn->prepare(
                "SELECT id FROM students WHERE id = ?"
            );
            $checkStudent->bind_param("i", $student_id);
            $checkStudent->execute();
            $studentResult = $checkStudent->get_result();

            // Check that the event exists.
            $checkEvent = $conn->prepare(
                "SELECT event_id FROM events WHERE event_id = ?"
            );
            $checkEvent->bind_param("i", $event_id);
            $checkEvent->execute();
            $eventResult = $checkEvent->get_result();

            if ($studentResult->num_rows === 0) {
                $message = "Selected student does not exist.";
                $message_type = "error";
            } elseif ($eventResult->num_rows === 0) {
                $message = "Selected event does not exist.";
                $message_type = "error";
            } else {
                // Check for duplicate registration.
                $check = $conn->prepare(
                    "SELECT registration_id FROM registrations
                     WHERE student_id = ? AND event_id = ?"
                );
                $check->bind_param("ii", $student_id, $event_id);
                $check->execute();
                $result = $check->get_result();

                if ($result->num_rows > 0) {
                    $message = "Student is already registered for this event.";
                    $message_type = "error";
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO registrations (student_id, event_id)
                         VALUES (?, ?)"
                    );
                    $stmt->bind_param("ii", $student_id, $event_id);

                    if ($stmt->execute()) {
                        $message = "Registration successful!";
                        $message_type = "success";
                    } else {
                        $message = "Registration failed. Please try again.";
                        $message_type = "error";
                    }

                    $stmt->close();
                }

                $check->close();
            }

            $checkStudent->close();
            $checkEvent->close();

        } catch (mysqli_sql_exception $e) {
            $message = "Unable to complete registration. Please check the database.";
            $message_type = "error";
        }
    }
}

// Load students and events.
$students = $conn->query(
    "SELECT id, name FROM students ORDER BY name"
);

$events = $conn->query(
    "SELECT event_id, event_name FROM events ORDER BY event_name"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub | Register Event</title>

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
            min-height: 100vh;
        }

        header {
            min-height: 70px;
            background: #fff;
            padding: 15px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 18px;
            border-bottom: 1px solid #e2e8f0;
        }

        .logo {
            font-size: 27px;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
        }

        .logo span {
            color: #2563eb;
        }

        nav {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        nav a {
            text-decoration: none;
            color: #334155;
            font-size: 14px;
            font-weight: 500;
        }

        nav a:hover,
        nav a[href="register_event.php"] {
            color: #2563eb;
        }

        nav a[href="register_event.php"] {
            font-weight: 700;
        }

        .container {
            width: 90%;
            max-width: 720px;
            margin: 50px auto;
        }

        h1 {
            text-align: center;
            font-size: 32px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 35px;
        }

        .form-card {
            background: #fff;
            padding: 32px;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.06);
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
        }

        select {
            width: 100%;
            height: 48px;
            padding: 0 14px;
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
        }

        select:focus {
            outline: 2px solid #bfdbfe;
            border-color: #2563eb;
        }

        button {
            width: 100%;
            min-height: 48px;
            border: none;
            border-radius: 8px;
            background: #2563eb;
            color: #fff;
            font-family: inherit;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 13px 16px;
            margin-bottom: 24px;
            border-radius: 8px;
            font-size: 14px;
            text-align: center;
        }

        .message.success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .message.error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            header {
                justify-content: center;
            }

            .logo {
                font-size: 24px;
            }

            nav {
                gap: 12px;
            }

            nav a {
                font-size: 12px;
            }

            .container {
                width: 94%;
                margin-top: 30px;
            }

            h1 {
                font-size: 26px;
            }

            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
<header>
    <div class="logo">🎓 Student<span>Hub</span></div>

    <nav>
        <a href="home.html">Home</a>
        <a href="dashboard.html">Dashboard</a>
        <a href="students.php">Students</a>
        <a href="events.php">Events</a>
        <a href="register_event.php">Register Event</a>
        <a href="registration.php">Registrations</a>
        <a href="attendance.html">Attendance</a>
        <a href="notes.html">Notes</a>
        <a href="Assignments.html">Assignments</a>
        <a href="timetable.html">Timetable</a>
        <a href="profile.html">Profile</a>
        <a href="about.html">About</a>
    </nav>
</header>

<main class="container">
    <h1>📝 Register for an Event</h1>
    <p class="subtitle">
        Select a student and event to complete registration.
    </p>

    <div class="form-card">
        <?php if ($message !== ""): ?>
            <div class="message <?= htmlspecialchars($message_type) ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register_event.php">
            <div class="form-group">
                <label for="student_id">Select Student</label>
                <select name="student_id" id="student_id" required>
                    <option value="">Select Student</option>

                    <?php while ($row = $students->fetch_assoc()): ?>
                        <option value="<?= (int)$row["id"] ?>">
                            <?= htmlspecialchars($row["name"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="event_id">Select Event</label>
                <select name="event_id" id="event_id" required>
                    <option value="">Select Event</option>

                    <?php while ($row = $events->fetch_assoc()): ?>
                        <option value="<?= (int)$row["event_id"] ?>">
                            <?= htmlspecialchars($row["event_name"]) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit" name="register">
                Register for Event
            </button>
        </form>
    </div>
</main>

<footer>
    © <?= date("Y") ?> StudentHub | Register Event
</footer>
</body>
</html>
