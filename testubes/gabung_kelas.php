<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'siswa') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kodeKelas = $_POST['kode_kelas'];

    // Cek apakah kode kelas valid dan belum diikuti oleh siswa
    $stmt = $conn->prepare("SELECT id FROM classes WHERE kode_kelas = ?");
    $stmt->bind_param("s", $kodeKelas);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $kelas = $result->fetch_assoc();
        $kelasId = $kelas['id'];

        // Cek apakah siswa sudah bergabung di kelas ini
        $stmt = $conn->prepare("SELECT * FROM class_students WHERE kelas_id = ? AND siswa_id = ?");
        $stmt->bind_param("ii", $kelasId, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Siswa belum bergabung, tambahkan ke kelas
            $stmt = $conn->prepare("INSERT INTO class_students (kelas_id, siswa_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $kelasId, $_SESSION['user_id']);
            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Terjadi kesalahan saat bergabung ke kelas.";
            }
        } else {
            $error = "Anda sudah bergabung di kelas ini.";
        }
    } else {
        $error = "Kode kelas tidak valid.";
    }
}
?>