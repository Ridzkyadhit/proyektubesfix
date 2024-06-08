<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Cek apakah token valid dan belum kadaluarsa
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires >= NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $userId = $user['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];

            // Validasi input
            if (empty($newPassword) || empty($confirmPassword)) {
                $error = "Password baru dan konfirmasi password harus diisi.";
            } elseif ($newPassword != $confirmPassword) {
                $error = "Password baru dan konfirmasi password tidak cocok.";
            } else {
                // Hash password baru
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update password dan hapus token reset
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                if ($stmt->execute()) {
                    $success = "Password berhasil diubah. Silakan login.";
                    header("Location: index.php"); 
                    exit();
                } else {
                    $error = "Terjadi kesalahan saat mengubah password.";
                }
            }
        }
    } else {
        $error = "Token reset password tidak valid atau sudah kadaluarsa.";
    }
} else {
    $error = "Token reset password tidak ditemukan.";
    header("Location: index.php"); // Redirect jika tidak ada token
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="./assets/css/auth.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="new_password">Password Baru:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Password Baru" required><br>
            <label for="confirm_password">Konfirmasi Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required><br>
            <button type="submit">Ubah Password</button>
        </form>
    </div>
</body>
</html>
