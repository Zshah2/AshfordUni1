<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/auth.php';
    header('Location: ' . role_home($_SESSION['user_type']));
    exit;
}

$error = $_SESSION['login_error'] ?? '';
unset($_SESSION['login_error']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ashford University | Student Portal</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body class="login-page">

    <main class="login-card">
        <img
            class="login-logo"
            src="assets/img/ashford-university-logo.svg"
            alt="Ashford University"
        >
        <h1>Welcome back</h1>
        <p>Sign in to access your university portal.</p>

        <?php if ($error): ?>
            <div class="login-error" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form class="login-form" action="login.php" method="post">
            <label for="email">
                University email
                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="email"
                    placeholder="first.last@ashford.edu"
                    required
                >
            </label>

            <label for="password">
                Password
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    required
                >
            </label>

            <button class="login-button" type="submit">Sign in</button>
        </form>

        <p class="login-help">
            Need an account? <a href="register.php">Create one</a>
        </p>

        <p class="login-footer">
            &copy; 2026 Ashford University
        </p>
    </main>

</body>
</html>