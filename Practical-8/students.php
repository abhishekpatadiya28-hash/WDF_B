
<?php

include "db.php";

$sql = "SELECT * FROM students";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>StudentHub - Students</title>

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
        }



        header {
            background: #ffffff;

            height: 70px;

            padding: 0 55px;

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
        }

        .logo span {
            color: #2563eb;
        }


        nav {
            display: flex;

            align-items: center;

            gap: 25px;

            margin-left: 60px;
        }

        nav a {
            text-decoration: none;

            color: #334155;

            font-size: 15px;

            font-weight: 500;

            white-space: nowrap;

            transition: 0.2s;
        }

        nav a:hover {
            color: #2563eb;
        }


        /* ACTIVE PAGE */

        nav a[href="students.php"] {
            color: #2563eb;

            font-weight: 700;
        }



        .container {
            width: 90%;

            max-width: 1400px;

            margin: 50px auto;
        }



        h1 {
            text-align: center;

            color: #1e293b;

            font-size: 32px;

            font-weight: 700;

            margin-bottom: 8px;
        }



        .subtitle {
            text-align: center;

            color: #64748b;

            font-size: 15px;

            font-weight: 400;

            margin-bottom: 32px;
        }



        .table-container {
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
        }

        th {
            background: #2563eb;

            color: #ffffff;

            padding: 14px 16px;

            text-align: left;

            font-size: 15px;

            font-weight: 600;
        }

        td {
            padding: 15px 16px;

            color: #334155;

            font-size: 14px;

            font-weight: 400;

            border-bottom: 1px solid #e2e8f0;
        }



        tbody tr:hover td {
            background: #f8faff;
        }



        tbody tr:last-child td {
            border-bottom: none;
        }



        footer {
            text-align: center;

            margin-top: 45px;

            padding: 20px;

            color: #64748b;

            font-size: 13px;
        }


        .no-students {
            text-align: center;

            padding: 30px;

            color: #64748b;

            font-size: 14px;
        }



        @media (max-width: 1200px) {

            header {
                padding: 0 30px;
            }

            nav {
                margin-left: 30px;

                gap: 18px;
            }

            nav a {
                font-size: 14px;
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
            }

            .container {
                width: 94%;

                margin-top: 40px;
            }

        }


        @media (max-width: 600px) {

            .logo {
                font-size: 24px;
            }

            nav {
                gap: 12px;
            }

            nav a {
                font-size: 13px;
            }

            h1 {
                font-size: 27px;
            }

            .subtitle {
                font-size: 14px;
            }

            .table-container {
                padding: 15px;
            }

            th,
            td {
                padding: 11px;

                font-size: 13px;
            }

        }

    </style>

</head>


<body>



    <header>

        <div class="logo">
            Student<span>Hub</span>
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

        </nav>

    </header>




    <main class="container">


        <h1>
            👨‍🎓 Students
        </h1>


        <p class="subtitle">
            View and manage student information
        </p>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Name</th>

                        <th>Email</th>

                        <th>Mobile</th>

                        <th>Course</th>

                        <th>Year</th>

                    </tr>

                </thead>


                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php echo htmlspecialchars($row["student_id"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["email"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["mobile"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["course"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row["year"]); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="6"
                                class="no-students"
                            >
                                No students found.
                            </td>

                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>



    <footer>

        © <?php echo date("Y"); ?> StudentHub. All rights reserved.

    </footer>


</body>

</html>
```
