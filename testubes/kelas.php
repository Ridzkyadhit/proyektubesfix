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

$kelasId = $_GET['id'];
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['user_role'];

// Ambil data kelas
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $kelasId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$kelas = $result->fetch_assoc();

// Ambil data tugas
$stmt = $conn->prepare("SELECT * FROM assignments WHERE kelas_id = ?");
$stmt->bind_param("i", $kelasId);
$stmt->execute();
$tugasResult = $stmt->get_result();
$tugas = $tugasResult->fetch_all(MYSQLI_ASSOC);

// Ambil data materi
$stmt = $conn->prepare("SELECT * FROM materials WHERE kelas_id = ?");
$stmt->bind_param("i", $kelasId);
$stmt->execute();
$materiResult = $stmt->get_result();
$materi = $materiResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $kelas['nama_kelas']; ?></title>
    <link rel="stylesheet" href="./assets/css/kelas.css">
</head>
<body>
    <header>
    <h1>Google Classroom KW</h1>
        <nav>
            <a href="dashboard.php" class="button">Kembali ke Dashboard</a> 
            <a href="logout.php">Logout (<?php echo $username; ?>)</a>
        </nav>
    </header>

    <main>
        <div class="kelas-container">
            <h2><?php echo $kelas['nama_kelas']; ?></h2>
            <p>Kode Kelas: <?php echo $kelas['kode_kelas']; ?></p>

            <?php if ($userRole == 'guru'): ?>
                <a href="edit_kelas.php?id=<?php echo $kelasId; ?>" class="button">Edit Kelas</a>
                <a href="buat_tugas.php?kelas_id=<?php echo $kelasId; ?>" class="button">Buat Tugas</a>
                <a href="unggah_materi.php?kelas_id=<?php echo $kelasId; ?>" class="button">Unggah Materi</a>
            <?php endif; ?>

            <h3>Tugas:</h3>
            <ul>
                <?php foreach ($tugas as $t): ?>
                    <li><a href="tugas.php?id=<?php echo $t['id']; ?>"><?php echo $t['judul']; ?></a> (Deadline: <?php echo $t['deadline']; ?>)</li>
                <?php endforeach; ?>
            </ul>

            <h3>Materi:</h3>
            <ul>
                <?php foreach ($materi as $m): ?>
                    <li class="materi-item">
                        <a href="<?php echo $m['file_materi']; ?>" target="_blank"><?php echo $m['judul']; ?></a>
                        <?php if ($m['jenis_materi'] == 'pertemuan'): ?>
                            <span class="label-pertemuan">Pertemuan</span>
                            <?php if ($userRole == 'guru'): ?>
                                <a href="edit_materi.php?id=<?php echo $m['id']; ?>" class="edit-link">Edit</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </main>
</body>
</html>
