<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'guru') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$submissionId = $_GET['id'];

// Ambil data submission
$stmt = $conn->prepare("SELECT s.*, a.judul, u.nama_lengkap FROM submissions s JOIN assignments a ON s.tugas_id = a.id JOIN users u ON s.siswa_id = u.id WHERE s.id = ?");
$stmt->bind_param("i", $submissionId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$submission = $result->fetch_assoc();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nilai = $_POST['nilai'];
    $komentar = $_POST['komentar'];

    // Validasi input (tambahkan validasi sesuai kebutuhan)
    if (empty($nilai)) {
        $error = "Nilai harus diisi.";
    } else {
        // Update data submission di database
        $stmt = $conn->prepare("UPDATE submissions SET nilai = ?, komentar_guru = ? WHERE id = ?");
        $stmt->bind_param("isi", $nilai, $komentar, $submissionId);
        if ($stmt->execute()) {
            header("Location: tugas.php?id={$submission['tugas_id']}"); // Redirect ke halaman tugas setelah berhasil menilai
            exit();
        } else {
            $error = "Terjadi kesalahan saat menyimpan nilai dan komentar.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nilai Tugas</title>
    <link rel="stylesheet" href="./assets/css/tugas_styles.css">
</head>
<body>
    <header>
        </header>

    <main>
        <div class="tugas-container">
            <h2>Nilai Tugas - <?php echo $submission['judul']; ?></h2>
            <p>Nama Siswa: <?php echo $submission['nama_lengkap']; ?></p>

            <?php if (!empty($submission['file_submission'])): ?>
                <a href="<?php echo $submission['file_submission']; ?>" download>Unduh Tugas Siswa</a>
            <?php else: ?>
                <p>Siswa belum mengumpulkan tugas.</p>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="nilai">Nilai:</label>
                <input type="number" id="nilai" name="nilai" min="0" max="100" value="<?php echo $submission['nilai']; ?>" required><br>
                <label for="komentar">Komentar:</label>
                <textarea id="komentar" name="komentar"><?php echo $submission['komentar_guru']; ?></textarea><br>
                <button type="submit">Simpan Nilai dan Komentar</button>
            </form>
            <a href="tugas.php?id=<?php echo $submission['tugas_id']; ?>" class="button">Kembali ke Tugas</a>
        </div>
    </main>
</body>
</html>
