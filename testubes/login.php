<?php
session_start();
require_once 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errorUsername = $errorPassword = $errorLogin = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($username)) {
        $errorUsername = "Username harus diisi.";
    } 

    if (empty($password)) {
        $errorPassword = "Password harus diisi.";
    }

    // Jika tidak ada error validasi, lanjutkan proses login
    if (empty($errorUsername) && empty($errorPassword)) {
        // Sanitasi input
        $username = $conn->real_escape_string($username);

        // Query untuk mengambil data pengguna
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username']; 
                header("Location: dashboard.php");
                exit();
            } else {
                $errorLogin = "Username atau password salah.";
            }
        } else {
            $errorLogin = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="./assets/css/auth.css">
    <script src="./assets/js/script.js"></script>
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <?php if (!empty($errorUsername)): ?>
            <div class="error-message"><?php echo $errorUsername; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Username" value="<?php echo isset($username) ? $username : ''; ?>" required><br>
            
            <?php if (!empty($errorPassword)): ?>
            <div class="error-message"><?php echo $errorPassword; ?></div>
        <?php endif; ?>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Password" required><br>

            <?php if (!empty($errorLogin)): ?>
            <div class="error-message"><?php echo $errorLogin; ?></div>
        <?php endif; ?>

            <button type="submit">Login</button>
        </form>
        <div class="additional-links">
            <a href="index.php">Kembali</a> | 
            <a href="signup.php">Daftar</a> |
            <a href="lupa_password.php">Lupa Password?</a>
        </div>
    </div>
</body>
</html>
