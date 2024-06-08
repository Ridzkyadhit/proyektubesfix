<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'guru') {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $namaKelas = $_POST['nama_kelas'];
    $deskripsi = $_POST['deskripsi'];
    $guruId = $_SESSION['user_id'];

    // Generate kode kelas unik (contoh sederhana, bisa diganti dengan algoritma yang lebih kompleks)
    $kodeKelas = substr(md5(uniqid()), 0, 6);

    // Validasi input
    if (empty($namaKelas)) {
        $error = "Nama kelas harus diisi.";
    } else {
        // Simpan data kelas ke database
        $stmt = $conn->prepare("INSERT INTO classes (nama_kelas, deskripsi, guru_id, kode_kelas) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $namaKelas, $deskripsi, $guruId, $kodeKelas);
        if ($stmt->execute()) {
            $kelasId = $conn->insert_id; // Ambil ID kelas yang baru dibuat

            // Penanganan unggah materi (jika ada)
            if (isset($_FILES['materi']) && $_FILES['materi']['error'] == 0) {
                $targetDir = "uploads/"; // Direktori untuk menyimpan file materi
                $materiFileName = basename($_FILES["materi"]["name"]);
                $targetFilePath = $targetDir . $materiFileName;
                $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

                // Validasi jenis file yang diizinkan
                $allowedTypes = array('pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx');
                if (in_array($fileType, $allowedTypes)) {
                    // Pindahkan file yang diunggah ke direktori tujuan
                    if (move_uploaded_file($_FILES["materi"]["tmp_name"], $targetFilePath)) {
                        // Simpan path file materi ke database
                        $stmt = $conn->prepare("INSERT INTO materials (kelas_id, judul, deskripsi, file_materi) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("isss", $kelasId, $materiFileName, $deskripsi, $targetFilePath);
                        $stmt->execute();
                    } else {
                        $error = "Maaf, terjadi kesalahan saat mengunggah file materi Anda.";
                    }
                } else {
                    $error = 'Maaf, hanya file PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX yang diizinkan untuk diunggah.';
                }
            }

            // Proses pembuatan tugas (jika ada)
            $judulTugas = $_POST['judul_tugas'];
            $deskripsiTugas = $_POST['deskripsi_tugas'];
            $deadlineTugas = $_POST['deadline_tugas'];

            if (!empty($judulTugas) && !empty($deadlineTugas)) {
                // Simpan data tugas ke database
                $stmt = $conn->prepare("INSERT INTO assignments (kelas_id, judul, deskripsi, deadline) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $kelasId, $judulTugas, $deskripsiTugas, $deadlineTugas);
                if (!$stmt->execute()) {
                    $error = "Terjadi kesalahan saat membuat tugas."; // Tambahkan pesan error jika pembuatan tugas gagal
                }
            }

            if (empty($error)) { // Hanya redirect jika tidak ada error
                header("Location: dashboard.php");
                exit();
            }
        } else {$error = "Terjadi kesalahan saat membuat kelas.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Kelas Baru</title>
    <link rel="stylesheet" href="./assets/css/kelas_form.css">
</head>
<body>
    <div class="container">
        <h2>Buat Kelas Baru</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="text" name="nama_kelas" placeholder="Nama Kelas" required><br>
            <textarea name="deskripsi" placeholder="Deskripsi"></textarea><br>

            <h3>Tugas Pertama (Opsional):</h3>
            <input type="text" name="judul_tugas" placeholder="Judul Tugas"><br>
            <textarea name="deskripsi_tugas" placeholder="Deskripsi Tugas"></textarea><br>
            <input type="datetime-local" name="deadline_tugas"><br>

            <h3>Materi (Opsional):</h3>
            <input type="file" name="materi"><br>

            <button type="submit">Buat Kelas</button>
        </form>
    </div>
</body>
</html>
