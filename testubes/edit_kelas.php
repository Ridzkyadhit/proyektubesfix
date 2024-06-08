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

$kelasId = $_GET['id'];

// Ambil data kelas dari database
$stmt = $conn->prepare("SELECT * FROM classes WHERE id = ?");
$stmt->bind_param("i", $kelasId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$kelas = $result->fetch_assoc();

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $namaKelas = $_POST['nama_kelas'];
    $deskripsi = $_POST['deskripsi'];

    // Validasi input (tambahkan validasi sesuai kebutuhan)
    if (empty($namaKelas)) {
        $error = "Nama kelas harus diisi.";
    } else {
        // Update data kelas di database
        $stmt = $conn->prepare("UPDATE classes SET nama_kelas = ?, deskripsi = ? WHERE id = ?");
        $stmt->bind_param("ssi", $namaKelas, $deskripsi, $kelasId);
        if ($stmt->execute()) {
            header("Location: kelas.php?id=$kelasId");
            exit();
        } else {
            $error = "Terjadi kesalahan saat mengedit kelas.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Kelas - <?php echo $kelas['nama_kelas']; ?></title>
    <link rel="stylesheet" href="./assets/css/kelas_form.css">
</head>
<body>
    <header>
        </header>

    <main>
        <div class="container">
            <h2>Edit Kelas</h2>
            <?php if (!empty($error)): ?>
                <p style="color: red;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="nama_kelas" value="<?php echo $kelas['nama_kelas']; ?>" required><br>
                <textarea name="deskripsi"><?php echo $kelas['deskripsi']; ?></textarea><br>
                <button type="submit">Simpan Perubahan</button>
            </form>
            <a href="kelas.php?id=<?php echo $kelasId; ?>" class="button">Kembali</a>
        </div>
    </main>
</body>
</html>
