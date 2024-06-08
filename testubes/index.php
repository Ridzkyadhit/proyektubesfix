<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        // ... (Logika untuk validasi dan proses login seperti sebelumnya)
    } elseif (isset($_POST['signup'])) {
        // ... (Logika untuk validasi dan proses pendaftaran seperti sebelumnya)
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login & Daftar</title>
    <link rel="stylesheet" href="./assets/css/index.css">
</head>
<body>
    <div class="container">
        <h1>Selamat Datang di Google Classroom KW</h1>
        <div class="button-container">
            <a href="login.php" class="button">Login</a>
            <a href="signup.php" class="button">Daftar</a>
        </div>
    </div>
    <script src="/assets/js/script.js"></script>
</body>
</html>
</html>
