<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'guru') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['kelas_id'])) {
    header("Location: dashboard.php");
    exit();
}

$kelasId = $_GET['kelas_id'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $jenisMateri = $_POST['jenis_materi'];

    // Validasi input (tambahkan validasi sesuai kebutuhan)
    if (empty($judul) || empty($_FILES['file_materi'])) {
        $error = "Judul dan file materi harus diisi.";
    } else {
        $targetDir = "uploads/"; // Direktori untuk menyimpan file materi
        $materiFileName = basename($_FILES["file_materi"]["name"]);
        $targetFilePath = $targetDir . $materiFileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

        // Validasi jenis file yang diizinkan
        $allowedTypes = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
        if (in_array($fileType, $allowedTypes)) {
            // Pindahkan file yang diunggah ke direktori tujuan
            if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $targetFilePath)) {
                // Simpan path file materi ke database
                $stmt = $conn->prepare("INSERT INTO materials (kelas_id, judul, deskripsi, file_materi, jenis_materi) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $kelasId, $judul, $deskripsi, $targetFilePath, $jenisMateri);
                if ($stmt->execute()) {
                    header("Location: kelas.php?id=$kelasId");
                    exit();
                } else {
                    $error = "Terjadi kesalahan saat menyimpan materi ke database.";
                }
            } else {
                $error = "Maaf, terjadi kesalahan saat mengunggah file materi Anda.";
            }
        } else {
            $error = 'Maaf, hanya file PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX yang diizinkan untuk diunggah.';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Unggah Materi</title>
    <link rel="stylesheet" href="./assets/css/materi_form.css">
</head>
<body>
    <header>
        </header>

    <main>
        <div class="container">
            <h2>Unggah Materi</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="text" name="judul" placeholder="Judul Materi" required><br>
                <textarea name="deskripsi" placeholder="Deskripsi"></textarea><br>
                <select name="jenis_materi" required>
                    <option value="materi">Materi</option>
                    <option value="pertemuan">Pertemuan</option>
                </select><br>
                <input type="file" name="file_materi" required><br>
                <button type="submit">Unggah Materi</button>
            </form>
            <a href="kelas.php?id=<?php echo $kelasId; ?>" class="button">Kembali</a>
        </div>
    </main>
</body>
</html>
