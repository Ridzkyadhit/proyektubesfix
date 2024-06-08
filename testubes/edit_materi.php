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

$materiId = $_GET['id'];

// Ambil data materi dari database
$stmt = $conn->prepare("SELECT * FROM materials WHERE id = ?");
$stmt->bind_param("i", $materiId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$materi = $result->fetch_assoc();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $jenisMateri = $_POST['jenis_materi'];

    // Validasi input (tambahkan validasi sesuai kebutuhan)
    if (empty($judul)) {
        $error = "Judul materi harus diisi.";
    } else {
        // Update data materi di database (tanpa mengubah file)
        $stmt = $conn->prepare("UPDATE materials SET judul = ?, deskripsi = ?, jenis_materi = ? WHERE id = ?");
        $stmt->bind_param("sssi", $judul, $deskripsi, $jenisMateri, $materiId);

        if ($stmt->execute()) {
            // Penanganan unggah file baru (jika ada)
            if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
                $targetDir = "uploads/";
                $materiFileName = basename($_FILES["file_materi"]["name"]);
                $targetFilePath = $targetDir . $materiFileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                // Validasi jenis file yang diizinkan
                $allowedTypes = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
                if (in_array($fileType, $allowedTypes)) {
                    // Pindahkan file yang diunggah ke direktori tujuan
                    if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $targetFilePath)) {
                        // Update path file materi di database
                        $stmt = $conn->prepare("UPDATE materials SET file_materi = ? WHERE id = ?");
                        $stmt->bind_param("si", $targetFilePath, $materiId);
                        $stmt->execute();

                        // Hapus file lama (jika ada)
                        if (!empty($materi['file_materi'])) {
                            unlink($materi['file_materi']);
                        }
                    } else {
                        $error = "Maaf, terjadi kesalahan saat mengunggah file materi Anda.";
                    }
                } else {
                    $error = 'Maaf, hanya file PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX yang diizinkan untuk diunggah.';
                }
            }

            header("Location: kelas.php?id={$materi['kelas_id']}");
            exit();
        } else {
            $error = "Terjadi kesalahan saat mengedit materi.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Materi</title>
    <link rel="stylesheet" href="./assets/css/materi_form.css">
</head>
<body>
    <header>
        </header>

    <main>
        <div class="container">
            <h2>Edit Materi</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="text" name="judul" value="<?php echo $materi['judul']; ?>" placeholder="Judul Materi" required><br>
                <textarea name="deskripsi" placeholder="Deskripsi"><?php echo $materi['deskripsi']; ?></textarea><br>
                <select name="jenis_materi" required>
                    <option value="materi" <?php if ($materi['jenis_materi'] == 'materi') echo 'selected'; ?>>Materi</option>
                    <option value="pertemuan" <?php if ($materi['jenis_materi'] == 'pertemuan') echo 'selected'; ?>>Pertemuan</option>
                </select><br>
                <input type
