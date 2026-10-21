<?php

include "db.php";

$sql = "SELECT * FROM events";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub | Events</title>

    <!-- Poppins Font -->
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


        /* ACTIVE EVENTS PAGE */

        nav a[href="events.php"] {
            color: #2563eb;

            font-weight: 700;
        }
        .container {
            width: calc(100% - 308px);

            max-width: 1380px;

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

            margin-bottom: 58px;
        }


        .table-container {
            width: 100%;

            background: #ffffff;

            padding: 22px 24px;

            border-radius: 14px;

            border: 1px solid #e2e8f0;

            box-shadow: 0 4px 15px rgba(15, 23, 42, 0.06);

            overflow-x: auto;
        }


        table {
            width: 100%;

            border-collapse: collapse;

            table-layout: auto;
        }

        th {
            background: #2563eb;

            color: #ffffff;

            padding: 14px 16px;

            text-align: left;

            font-size: 15px;

            font-weight: 600;

            line-height: 1.4;
        }


        td {
            padding: 15px 16px;

            color: #334155;

            font-size: 15px;

            font-weight: 400;

            line-height: 1.4;

            border-bottom: 1px solid #e2e8f0;

            vertical-align: middle;
        }


        tbody tr:hover td {
            background: #f8faff;
        }



        tbody tr:last-child td {
            border-bottom: none;
        }



        .no-events {
            text-align: center;

            padding: 28px 16px;

            color: #64748b;

            font-size: 14px;
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

            .container {
                width: 90%;
            }

            nav {
                gap: 20px;

                margin-left: 30px;
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

            .container {
                width: 92%;

                margin-top: 50px;
            }

            h1 {
                font-size: 32px;
            }

            .subtitle {
                margin-bottom: 45px;
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

                margin-bottom: 38px;
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

            .table-container {
                padding: 15px;

                border-radius: 12px;
            }

            th,
            td {
                padding: 11px;

                font-size: 13px;
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

            <a href="dashboard.php">
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

            <a href="registration.php">
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
            📅 Events
        </h1>


        <!-- PAGE DESCRIPTION -->

        <p class="subtitle">
            Explore upcoming events at StudentHub.
        </p>




        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Event Name</th>

                        <th>Date</th>

                        <th>Location</th>

                        <th>Description</th>

                    </tr>

                </thead>


                <tbody>

                    <?php

                    if ($result && $result->num_rows > 0) {

                        while ($row = $result->fetch_assoc()) {

                            echo "<tr>";

                            echo "<td>"
                                . htmlspecialchars($row["event_id"])
                                . "</td>";

                            echo "<td>"
                                . htmlspecialchars($row["event_name"])
                                . "</td>";

                            echo "<td>"
                                . htmlspecialchars($row["event_date"])
                                . "</td>";

                            echo "<td>"
                                . htmlspecialchars($row["location"])
                                . "</td>";

                            echo "<td>"
                                . htmlspecialchars($row["description"])
                                . "</td>";

                            echo "</tr>";
                        }

                    } else {

                        echo "<tr>";

                        echo "<td colspan='5' class='no-events'>";
                        echo "No events available.";
                        echo "</td>";

                        echo "</tr>";
                    }

                    ?>

                </tbody>

            </table>

        </div>

    </main>



    <footer>

        © <?php echo date("Y"); ?> StudentHub | Events

    </footer>


</body>

</html>