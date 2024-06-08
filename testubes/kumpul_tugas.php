<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'siswa') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['tugas_id'])) {
    header("Location: dashboard.php");
    exit();
}

$tugasId = $_GET['tugas_id'];
$siswaId = $_SESSION['user_id'];

$error = ""; // Inisialisasi variabel error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Penanganan unggah file submission
    if (isset($_FILES['file_submission']) && $_FILES['file_submission']['error'] == 0) {
        $targetDir = "uploads/submissions/";
        $submissionFileName = basename($_FILES["file_submission"]["name"]);
        $targetFilePath = $targetDir . $submissionFileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Validasi jenis file yang diizinkan
        $allowedTypes = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
        if (in_array($fileType, $allowedTypes)) {
            // Pindahkan file yang diunggah ke direktori tujuan
            if (move_uploaded_file($_FILES["file_submission"]["tmp_name"], $targetFilePath)) {
                // Simpan data submission ke database
                $stmt = $conn->prepare("INSERT INTO submissions (tugas_id, siswa_id, file_submission, tanggal_pengumpulan) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iis", $tugasId, $siswaId, $targetFilePath);

                if ($stmt->execute()) {
                    header("Location: tugas.php?id=$tugasId");
                    exit();
                } else {
                    $error = "Terjadi kesalahan saat menyimpan submission ke database: " . $stmt->error;
                }
            } else {
                $error = "Maaf, terjadi kesalahan saat mengunggah file submission Anda.";
            }
        } else {
            $error = 'Maaf, hanya file PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX yang diizinkan untuk diunggah.';
        }
    } else {
        $error = "Harap pilih file submission.";
    }
}

// Ambil data tugas
$stmt = $conn->prepare("SELECT * FROM assignments WHERE id = ?");
$stmt->bind_param("i", $tugasId);
$stmt->execute();
$result = $stmt->get_result();
$tugas = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kumpul Tugas</title>
    <link rel="stylesheet" href="./assets/css/tugas_styles.css">
</head>
<body>
    <header>
        </header>
    <main>
        <div class="tugas-container">
            <h2>Kumpul Tugas - <?php echo $tugas['judul']; ?></h2>
            
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <input type="file" name="file_submission" required>
                <button type="submit">Kumpul Tugas</button>
            </form>
            <a href="tugas.php?id=<?php echo $tugasId; ?>" class="button">Kembali ke Tugas</a>
        </div>
    </main>
</body>
</html>
