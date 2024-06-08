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
    $email = $_POST['email'];

    // Validasi input (tambahkan validasi email yang lebih kuat)
    if (empty($email)) {
        $error = "Email harus diisi.";
    } else {
        // Cek apakah email terdaftar
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // Generate token reset password
            $token = bin2hex(random_bytes(16));

            // Simpan token ke database (tambahkan kolom 'reset_token' dan 'reset_token_expires' di tabel 'users')
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token berlaku 1 jam
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE username = ?");
            $stmt->bind_param("sss", $token, $expiresAt, $email);
            if ($stmt->execute()) {
                // Kirim email berisi tautan reset password (Anda perlu mengimplementasikan logika pengiriman email)
                $resetLink = "http://localhost/your-project/reset_password.php?token=$token";

                // ... (logika pengiriman email)
                $success = "Permintaan reset password berhasil. Silakan cek email Anda.";
            } else {
                $error = "Terjadi kesalahan saat memproses permintaan reset password.";
            }
        } else {
            $error = "Email tidak terdaftar.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lupa Password</title>
    <link rel="stylesheet" href="./assets/css/auth.css">
</head>
<body>
    <div class="container">
        <h2>Lupa Password</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Email" required><br>
            <button type="submit">Kirim Permintaan Reset Password</button>
        </form>
        <div class="additional-links">
            <a href="index.php">Kembali</a> | 
            <a href="login.php">Login</a>
        </div>
    </div>
</body>
</html>
