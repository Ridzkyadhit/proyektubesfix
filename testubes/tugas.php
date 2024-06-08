<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$tugasId = $_GET['id'];
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Ambil data tugas
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->bind_param("i", $tugasId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$tugas = $result->fetch_assoc();

// Ambil data kelas
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $tugas['kelas_id']);
$stmt->execute();
$result = $stmt->get_result();
$kelas = $result->fetch_assoc();

// Ambil data submission untuk tugas ini
$stmt = $conn->prepare("SELECT s.*, u.nama_lengkap FROM submissions s JOIN users u ON s.siswa_id = u.id WHERE s.tugas_id = ?");
$stmt->bind_param("i", $tugasId);
$stmt->execute();
$result = $stmt->get_result();
$submissions = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $tugas['judul']; ?></title>
    <link rel="stylesheet" href="./assets/css/tugas_styles.css"> 
</head>
<body>
    <header>
        <h1>Google Classroom KW</h1>
        <nav>
            <a href="kelas.php?id=<?php echo $kelas['id']; ?>" class="button">Kembali ke Kelas</a>
            <a href="dashboard.php" class="button">Ke Dashboard</a>
            <a href="logout.php">Logout (<?php echo $username; ?>)</a>
        </nav>
    </header>

    <main>
        <div class="tugas-container">
            <h2><?php echo $tugas['judul']; ?></h2>
            <p>Kelas: <a href="kelas.php?id=<?php echo $kelas['id']; ?>"><?php echo $kelas['nama_kelas']; ?></a></p>
            <p>Deadline: <?php echo $tugas['deadline']; ?></p>
            <p>Deskripsi: <?php echo $tugas['deskripsi']; ?></p>

            <?php if (!empty($tugas['file_tugas'])): ?>
                <a href="<?php echo $tugas['file_tugas']; ?>" download>Unduh Materi Tugas</a>
            <?php endif; ?>

            <?php if ($userRole == 'siswa'): ?>
                <?php if (empty($submissions) || !$submissions[0]['nilai']): // Periksa apakah ada submission dan apakah sudah dinilai ?>
                    <form method="POST" action="kumpul_tugas.php?tugas_id=<?php echo $tugasId; ?>" enctype="multipart/form-data">
                        <input type="file" name="file_submission" required>
                        <button type="submit">Kumpul Tugas</button>
                    </form>
                <?php else: ?>
                    <p>Anda sudah mengumpulkan tugas ini pada <?php echo $submissions[0]['tanggal_pengumpulan']; ?></p>
                    <?php if (!empty($submissions[0]['file_submission'])): ?>
                        <a href="<?php echo $submissions[0]['file_submission']; ?>" download>Lihat Tugas Anda</a>
                    <?php endif; ?>
                    <p>Nilai: <?php echo $submissions[0]['nilai']; ?></p>
                    <p>Komentar Guru: <?php echo $submissions[0]['komentar_guru']; ?></p>
                <?php endif; ?>
            <?php elseif ($userRole == 'guru'): ?>
                <h3>Tugas yang Dikumpulkan:</h3>
                <?php if (empty($submissions)): ?>
                    <p>Belum ada siswa yang mengumpulkan tugas ini.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($submissions as $submission): ?>
                            <li>
                                <p>Nama Siswa: <?php echo $submission['nama_lengkap']; ?></p>
                                <?php if (!empty($submission['file_submission'])): ?>
                                    <a href="<?php echo $submission['file_submission']; ?>" download>Unduh Tugas Siswa</a>
                                <?php endif; ?>
                                <a href="nilai_tugas.php?id=<?php echo $submission['id']; ?>" class="button">Nilai Tugas</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
