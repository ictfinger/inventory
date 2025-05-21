<?php
session_start();
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Hash password dengan md5
    $password_md5 = md5($password);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND role = 'admin' LIMIT 1");
    $stmt->bind_param('ss', $username, $password_md5);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $user['username'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Username atau password salah, atau Anda tidak memiliki akses admin.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #e9f4fb; }
        .login-card {
            max-width: 380px;
            margin: 90px auto;
            border-radius: 16px;
            box-shadow: 0 2px 18px #b7e3fe44;
            border: 1px solid #aad6f3;
            background: #fff;
        }
        .login-card .form-label { font-weight: 500; }
    </style>
</head>
<body>
    <div class="login-card p-4">
        <h3 class="text-center mb-4">Login Admin</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>
    </div>
</body>
</html>