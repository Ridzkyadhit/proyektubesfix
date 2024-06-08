<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Ambil data kelas terbaru
$latestClasses = [];
if ($user_role == 'guru') {
    $stmt = $conn->prepare("SELECT * FROM classes WHERE guru_id = ? ORDER BY id DESC LIMIT 3");
} else if ($user_role == 'siswa') {
    $stmt = $conn->prepare("SELECT c.* FROM classes c JOIN class_students cs ON c.id = cs.kelas_id WHERE cs.siswa_id = ? ORDER BY c.id DESC LIMIT 3");
}

if (isset($stmt)) { 
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $latestClasses = $result->fetch_all(MYSQLI_ASSOC);
}

// Query untuk mengambil 3 tugas terbaru 
if ($user_role == 'guru') {
    $stmt = $conn->prepare("SELECT * FROM assignments WHERE kelas_id IN (SELECT id FROM classes WHERE guru_id = ?) ORDER BY deadline ASC LIMIT 3");
} else if ($user_role == 'siswa') {
    $stmt = $conn->prepare("SELECT a.* FROM assignments a JOIN class_students cs ON a.kelas_id = cs.kelas_id WHERE cs.siswa_id = ? ORDER BY a.deadline ASC LIMIT 3");
}
if (isset($stmt)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $latestAssignments = $result->fetch_all(MYSQLI_ASSOC);
}

// Ambil data tugas yang akan datang untuk ditampilkan di kalender
$stmt = $conn->prepare("SELECT * FROM assignments WHERE deadline >= CURDATE() ORDER BY deadline ASC");
$stmt->execute();
$result = $stmt->get_result();
$upcomingAssignments = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="./assets/css/style.css">
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.14/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/web-component@6.1.14/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.14/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: [
                    <?php foreach ($upcomingAssignments as $assignment): ?>
                        {
                            title: '<?php echo $assignment['judul']; ?>',
                            start: '<?php echo $assignment['deadline']; ?>',
                            url: 'tugas.php?id=<?php echo $assignment['id']; ?>'
                        },
                    <?php endforeach; ?>
                ]
            });
            calendar.render();
        });
    </script>
</head>
<body>
    <header>
        <h1>Google Classroom KW</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <?php if ($user_role == 'guru'): ?>
                <a href="buat_kelas.php" class="create-class-button">+ Buat Kelas</a>
            <?php endif; ?>
            <a href="logout.php">Logout (<?php echo $username; ?>)</a>
        </nav>
    </header>

    <main>
        <div class="dashboard-container">
            <aside class="sidebar">
                <h3>Kelas Terbaru</h3>
                <ul>
                    <?php foreach ($latestClasses as $class): ?>
                        <li><a href="kelas.php?id=<?php echo $class['id']; ?>"><?php echo $class['nama_kelas']; ?></a></li>
                    <?php endforeach; ?>
                </ul>

                <h3>Tugas Mendatang</h3>
                <ul>
                    <?php foreach ($latestAssignments as $assignment): ?>
                        <li><a href="tugas.php?id=<?php echo $assignment['id']; ?>"><?php echo $assignment['judul']; ?></a> (<?php echo $assignment['deadline']; ?>)</li>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <section class="main-content">
                <?php if (!empty($latestClasses)): ?>
                    <div class="class-grid-container">
                        <?php if ($user_role == 'guru'): ?>
                            <h2>Kelas yang Anda Ajar:</h2>
                        <?php elseif ($user_role == 'siswa'): ?>
                            <h2>Kelas yang Anda Ikuti:</h2>
                            <div class="join-class-section">
                                <h3>Gabung Kelas:</h3>
                                <form method="POST" action="gabung_kelas.php">
                                    <input type="text" name="kode_kelas" placeholder="Kode Kelas" required>
                                    <button type="submit">Gabung Kelas</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <div class="class-grid">
                            <?php foreach ($latestClasses as $class): ?>
                                <div class='class-card'>
                                    <h3><a href='kelas.php?id=<?php echo $class['id']; ?>'><?php echo $class['nama_kelas']; ?></a></h3>
                                    <p>Kode Kelas: <?php echo $class['kode_kelas']; ?></p>
                                    <?php if ($user_role == 'guru'): ?>
                                        <a href="edit_kelas.php?id=<?php echo $class['id']; ?>" class="button">Edit Kelas</a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <section class="calendar-tugas-container">
                    <section class="calendar-section">
                        <h2>Kalender</h2>
                        <div id='calendar'></div>
                    </section>
                </section>
            </section>
        </div>
    </main>
</body>
</html>
