
<?php
require "auth.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

require "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$message = "";
$uploadDir = __DIR__ . "/uploads/event_posters/";
$uploadUrl = "uploads/event_posters/";
$maxSize = 2 * 1024 * 1024;

$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function removePosterFile($path) {
    if (!$path) return;

    $base = realpath(__DIR__ . "/uploads/event_posters");
    $file = realpath(__DIR__ . "/" . $path);

    if ($base && $file &&
        str_starts_with($file, $base . DIRECTORY_SEPARATOR) &&
        is_file($file)) {
        unlink($file);
    }
}

function uploadPoster($file, $dir, $url, $maxSize, $allowedTypes) {
    if (!$file || $file["error"] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        throw new Exception("Poster upload failed.");
    }

    if ($file["size"] > $maxSize) {
        throw new Exception("Poster size must not exceed 2 MB.");
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file["tmp_name"]);

    if (!isset($allowedTypes[$mime])) {
        throw new Exception("Only JPG, PNG and WEBP posters are allowed.");
    }

    if (getimagesize($file["tmp_name"]) === false) {
        throw new Exception("The uploaded file is not a valid image.");
    }

    $filename = bin2hex(random_bytes(16)) . "." . $allowedTypes[$mime];

    if (!move_uploaded_file($file["tmp_name"], $dir . $filename)) {
        throw new Exception("Unable to save the poster.");
    }

    return $url . $filename;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        if (!hash_equals(
            $_SESSION["csrf_token"],
            $_POST["csrf_token"] ?? ""
        )) {
            throw new Exception("Invalid request. Refresh and try again.");
        }

        $action = $_POST["action"] ?? "";

        if ($action === "delete") {
            $id = filter_var(
                $_POST["event_id"] ?? "",
                FILTER_VALIDATE_INT
            );

            if (!$id || $id < 1) {
                throw new Exception("Invalid event ID.");
            }

            $stmt = $conn->prepare(
                "SELECT poster FROM events WHERE event_id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $event = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$event) {
                throw new Exception("Event not found.");
            }

            $stmt = $conn->prepare(
                "DELETE FROM events WHERE event_id = ?"
            );
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            removePosterFile($event["poster"]);
            $message = "Event deleted successfully.";

        } elseif ($action === "save") {
            $id = (int)($_POST["event_id"] ?? 0);
            $name = trim($_POST["event_name"] ?? "");
            $date = $_POST["event_date"] ?? "";
            $location = trim($_POST["location"] ?? "");
            $description = trim($_POST["description"] ?? "");

            if ($name === "" || $date === "") {
                throw new Exception("Event name and date are required.");
            }

            if (mb_strlen($name) > 100 ||
                mb_strlen($location) > 150) {
                throw new Exception("Event name or location is too long.");
            }

            $dateObj = DateTime::createFromFormat("!Y-m-d", $date);

            if (!$dateObj || $dateObj->format("Y-m-d") !== $date) {
                throw new Exception("Please enter a valid date.");
            }

            $oldPoster = null;

            if ($id > 0) {
                $stmt = $conn->prepare(
                    "SELECT poster FROM events WHERE event_id = ?"
                );
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $oldEvent = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$oldEvent) {
                    throw new Exception("Event not found.");
                }

                $oldPoster = $oldEvent["poster"];
            }

            $newPoster = uploadPoster(
                $_FILES["poster"] ?? null,
                $uploadDir,
                $uploadUrl,
                $maxSize,
                $allowedTypes
            );

            $poster = $newPoster ?? $oldPoster;

            try {
                if ($id > 0) {
                    $stmt = $conn->prepare(
                        "UPDATE events SET event_name = ?,
                         event_date = ?, location = ?,
                         description = ?, poster = ?
                         WHERE event_id = ?"
                    );
                    $stmt->bind_param(
                        "sssssi",
                        $name, $date, $location,
                        $description, $poster, $id
                    );
                } else {
                    $stmt = $conn->prepare(
                        "INSERT INTO events
                         (event_name, event_date, location,
                          description, poster)
                         VALUES (?, ?, ?, ?, ?)"
                    );
                    $stmt->bind_param(
                        "sssss",
                        $name, $date, $location,
                        $description, $poster
                    );
                }

                $stmt->execute();
                $stmt->close();

            } catch (Throwable $e) {
                if ($newPoster) {
                    removePosterFile($newPoster);
                }
                throw $e;
            }

            if ($newPoster && $oldPoster) {
                removePosterFile($oldPoster);
            }

            $message = $id > 0
                ? "Event updated successfully."
                : "Event added successfully.";

        } else {
            throw new Exception("Invalid action.");
        }

    } catch (Throwable $e) {
        error_log($e->getMessage());

        if ($e instanceof mysqli_sql_exception &&
            $e->getCode() === 1451) {
            $message = "This event has linked records. Resolve those records before deleting it.";
        } elseif ($e instanceof mysqli_sql_exception) {
            $message = "Database error. Check your events table.";
        } else {
            $message = $e->getMessage();
        }
    }
}

$editEvent = null;

if (isset($_GET["edit"])) {
    $editId = filter_var($_GET["edit"], FILTER_VALIDATE_INT);

    if ($editId && $editId > 0) {
        $stmt = $conn->prepare(
            "SELECT * FROM events WHERE event_id = ?"
        );
        $stmt->bind_param("i", $editId);
        $stmt->execute();
        $editEvent = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

$events = $conn->query(
    "SELECT * FROM events ORDER BY event_date DESC, event_id DESC"
);

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StudentHub | Manage Events</title>
<link rel="stylesheet" href="admin_dashboard.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    background: #f5f7fb;
    color: #1e293b;
    font-family: Poppins, Arial, sans-serif;
}

.event-container {
    max-width: 1200px;
    width: 92%;
    margin: 35px auto;
}

.event-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin: 24px 0;
}

.event-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.event-form input,
.event-form textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font: inherit;
}

.event-form textarea,
.full {
    grid-column: 1 / -1;
}

.btn {
    display: inline-block;
    padding: 10px 16px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border: none;
    border-radius: 7px;
    font: inherit;
    cursor: pointer;
}

.btn-danger { background: #dc2626; }

.table-wrap { overflow-x: auto; }

table {
    width: 100%;
    min-width: 750px;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}

th { background: #2563eb; color: white; }

.poster {
    width: 85px;
    height: 65px;
    object-fit: cover;
    border-radius: 6px;
}

.notice {
    background: #eff6ff;
    padding: 12px;
    border-radius: 7px;
}

@media (max-width: 600px) {
    .event-form { grid-template-columns: 1fr; }
    .event-form textarea, .full { grid-column: auto; }
    .event-card { padding: 16px; }
}
</style>
</head>

<body>

<main class="event-container">
    <h1>Manage Events</h1>
    <p>Add, view, update and delete StudentHub events.</p>

    <?php if ($message !== ""): ?>
        <p class="notice"><?= h($message) ?></p>
    <?php endif; ?>

    <section class="event-card">
        <h2><?= $editEvent ? "Update Event" : "Add New Event" ?></h2>

        <form class="event-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="event_id" value="<?= (int)($editEvent["event_id"] ?? 0) ?>">

            <input name="event_name" maxlength="100" required
                   placeholder="Event Name"
                   value="<?= h($editEvent["event_name"] ?? "") ?>">

            <input type="date" name="event_date" required
                   value="<?= h($editEvent["event_date"] ?? "") ?>">

            <input name="location" maxlength="150"
                   placeholder="Venue / Location"
                   value="<?= h($editEvent["location"] ?? "") ?>">

            <div>
                <label for="poster">Poster (JPG, PNG, WEBP; maximum 2 MB)</label>
                <input type="file" id="poster" name="poster"
                       accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            </div>

            <?php if (!empty($editEvent["poster"])): ?>
                <div>
                    <p>Current poster</p>
                    <img class="poster" src="<?= h($editEvent["poster"]) ?>" alt="Current poster">
                </div>
            <?php endif; ?>

            <textarea name="description" rows="4"
                      placeholder="Event Description"><?= h($editEvent["description"] ?? "") ?></textarea>

            <div class="full">
                <button class="btn" type="submit">
                    <?= $editEvent ? "Update Event" : "Add Event" ?>
                </button>

                <?php if ($editEvent): ?>
                    <a class="btn" href="manage_events.php">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <section class="event-card">
        <h2>All Events</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($events && $events->num_rows > 0): ?>
                    <?php while ($event = $events->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$event["event_id"] ?></td>
                            <td>
                                <?php if (!empty($event["poster"])): ?>
                                    <img class="poster" src="<?= h($event["poster"]) ?>" alt="Event poster">
                                <?php else: ?>
                                    No poster
                                <?php endif; ?>
                            </td>
                            <td><?= h($event["event_name"]) ?></td>
                            <td><?= h($event["event_date"]) ?></td>
                            <td><?= h($event["location"]) ?></td>
                            <td>
                                <a class="btn" href="?edit=<?= (int)$event["event_id"] ?>">Edit</a>

                                <form method="POST" style="display:inline"
                                      onsubmit="return confirm('Delete this event?')">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION["csrf_token"]) ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?= (int)$event["event_id"] ?>">
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No events available yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <a class="btn" href="admin_dashboard.php">← Back to Admin Dashboard</a>
</main>

</body>
</html>
