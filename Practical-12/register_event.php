<?php

include "db.php";

$message = "";
$message_type = "";

if (isset($_POST["register"])) {

    $student_id = $_POST["student_id"];
    $event_id = $_POST["event_id"];

    $check = $conn->prepare(
        "SELECT * FROM registrations WHERE student_id = ? AND event_id = ?"
    );

    $check->bind_param("ii", $student_id, $event_id);

    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows > 0) {

        $message = "Student is already registered for this event.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO registrations (student_id, event_id) VALUES (?, ?)"
        );

        $stmt->bind_param("ii", $student_id, $event_id);

        if ($stmt->execute()) {

            $message = "Registration successful!";
            $message_type = "success";

        } else {

            $message = "Registration failed.";
            $message_type = "error";

        }

        $stmt->close();
    }

    $check->close();
}


$students = $conn->query("SELECT * FROM students");

$events = $conn->query("SELECT * FROM events");

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Register Event</title>

   
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

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
            width: 100%;

            height: 78px;

            background: #ffffff;

            padding: 0 90px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            border-bottom: 1px solid #e2e8f0;
        }



        .logo {
            font-size: 27px;

            font-weight: 700;

            color: #1e293b;

            white-space: nowrap;

            flex-shrink: 0;
        }

        .logo span {
            color: #2563eb;
        }


        nav {
            display: flex;

            align-items: center;

            justify-content: flex-end;

            gap: 25px;

            margin-left: 40px;
        }

        nav a {
            text-decoration: none;

            color: #334155;

            font-size: 15px;

            font-weight: 500;

            white-space: nowrap;

            transition: color 0.2s ease;
        }

        nav a:hover {
            color: #2563eb;
        }


        nav a[href="register_event.php"] {
            color: #2563eb;

            font-weight: 700;
        }



        .container {
            width: 90%;

            max-width: 720px;

            margin: 56px auto 0;
        }



        h1 {
            text-align: center;

            color: #1e293b;

            font-size: 34px;

            line-height: 1.25;

            font-weight: 700;

            margin-bottom: 10px;
        }



        .subtitle {
            text-align: center;

            color: #64748b;

            font-size: 16px;

            line-height: 1.5;

            font-weight: 400;

            margin-bottom: 42px;
        }



        .form-card {
            background: #ffffff;

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

            background: #ffffff;

            color: #334155;

            border: 1px solid #cbd5e1;

            border-radius: 8px;

            font-family: "Poppins", Arial, sans-serif;

            font-size: 14px;

            outline: none;

            cursor: pointer;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        select:focus {
            border-color: #2563eb;

            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }


        button {
            width: 100%;

            height: 48px;

            margin-top: 5px;

            border: none;

            border-radius: 8px;

            background: #2563eb;

            color: #ffffff;

            font-family: "Poppins", Arial, sans-serif;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.2s ease,
                transform 0.1s ease;
        }

        button:hover {
            background: #1d4ed8;
        }

        button:active {
            transform: translateY(1px);
        }

        .message {
            padding: 13px 16px;

            margin-bottom: 24px;

            border-radius: 8px;

            font-size: 14px;

            font-weight: 500;

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
            width: 100%;

            text-align: center;

            margin-top: 55px;

            padding: 20px;

            color: #64748b;

            font-size: 13px;
        }



        @media (max-width: 1400px) {

            header {
                padding: 0 55px;
            }

        }



        @media (max-width: 1100px) {

            header {
                padding: 0 35px;
            }

            .logo {
                font-size: 25px;
            }

            nav {
                gap: 15px;

                margin-left: 20px;
            }

            nav a {
                font-size: 13px;
            }

        }


        @media (max-width: 900px) {

            header {
                height: auto;

                padding: 18px 25px;

                flex-direction: column;

                gap: 18px;
            }

            nav {
                margin-left: 0;

                justify-content: center;

                flex-wrap: wrap;

                gap: 14px;
            }

            nav a {
                font-size: 13px;
            }

            .container {
                width: 94%;

                margin-top: 42px;
            }

            h1 {
                font-size: 30px;
            }

            .subtitle {
                font-size: 15px;
            }

            .form-card {
                padding: 28px;
            }

        }



        @media (max-width: 600px) {

            .logo {
                font-size: 24px;
            }

            nav {
                gap: 10px;
            }

            nav a {
                font-size: 12px;
            }

            .container {
                width: 94%;

                margin-top: 35px;
            }

            h1 {
                font-size: 27px;
            }

            .subtitle {
                font-size: 14px;

                margin-bottom: 30px;
            }

            .form-card {
                padding: 20px;

                border-radius: 12px;
            }

            select,
            button {
                height: 46px;
            }

            footer {
                margin-top: 35px;
            }

        }

    </style>

</head>


<body>

    <header>

        <div class="logo">
            🎓 Student<span>Hub</span>
        </div>


        <nav>

            <a href="home.html">
                Home
            </a>

            <a href="dashboard.html">
                Dashboard
            </a>

            <a href="students.php">
                Students
            </a>

            <a href="events.php">
                Events
            </a>

            <a href="register_event.php">
                Register Event
            </a>

            <a href="registrations.php">
                Registrations
            </a>

            <a href="attendance.html">
                Attendance
            </a>

            <a href="notes.html">
                Notes
            </a>

            <a href="Assignments.html">
                Assignments
            </a>

            <a href="timetable.html">
                Timetable
            </a>

            <a href="profile.html">
                Profile
            </a>

            <a href="about.html">
                About
            </a>

        </nav>

    </header>




    <main class="container">


        <!-- PAGE HEADING -->

        <h1>
            📝 Register for an Event
        </h1>


        <!-- PAGE DESCRIPTION -->

        <p class="subtitle">
            Select a student and event to complete registration.
        </p>



        <div class="form-card">


            <!-- MESSAGE -->

            <?php if ($message != ""): ?>

                <div class="message <?php echo $message_type; ?>">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


           

            <form method="POST">



                <div class="form-group">

                    <label for="student_id">
                        Select Student
                    </label>

                    <select
                        name="student_id"
                        id="student_id"
                        required
                    >

                        <option value="">
                            Select Student
                        </option>


                        <?php

                        while ($row = $students->fetch_assoc()) {

                            echo "<option value='"
                                . htmlspecialchars($row["student_id"])
                                . "'>";

                            echo htmlspecialchars($row["name"]);

                            echo "</option>";
                        }

                        ?>

                    </select>

                </div>



               

                <div class="form-group">

                    <label for="event_id">
                        Select Event
                    </label>

                    <select
                        name="event_id"
                        id="event_id"
                        required
                    >

                        <option value="">
                            Select Event
                        </option>


                        <?php

                        while ($row = $events->fetch_assoc()) {

                            echo "<option value='"
                                . htmlspecialchars($row["event_id"])
                                . "'>";

                            echo htmlspecialchars($row["event_name"]);

                            echo "</option>";
                        }

                        ?>

                    </select>

                </div>



                

                <button
                    type="submit"
                    name="register"
                >
                    Register for Event
                </button>


            </form>

        </div>

    </main>



    <footer>

        © <?php echo date("Y"); ?> StudentHub | Register Event

    </footer>


</body>

</html>