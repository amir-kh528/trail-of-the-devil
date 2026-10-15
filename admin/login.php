<?php
session_start();

$adminUsername = 'admin';
$adminPassword = 'admin123';

if (isset($_POST['logout']) && (int) $_POST['logout'] === 1) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: view_messages.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: view_messages.php');
        exit;
    }

    $error = 'نام کاربری یا رمز عبور اشتباه است.';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="login-page-body">
    <div class="login-shell">
        <div class="login-card">
            <div class="login-header">
                <span class="login-badge">پنل مدیریت</span>
                <h1>ورود به حساب کاربری</h1>
            </div>

            <?php if ($error !== ''): ?>
                <div class="status show error" aria-live="polite"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" class="login-form">
                <label class="field-label" for="username">نام کاربری</label>
                <input id="username" name="username" type="text" placeholder="نام کاربری" autocomplete="username" required>

                <label class="field-label" for="password">رمز عبور</label>
                <input id="password" name="password" type="password" placeholder="رمز عبور" autocomplete="current-password" required>

                <button type="submit" class="btn primary login-btn">ورود</button>
            </form>
        </div>
    </div>
</body>
</html>
