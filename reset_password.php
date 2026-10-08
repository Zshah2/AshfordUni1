<?php

session_start();

require_once __DIR__ . '/config/database.php';

if (
    empty($_SESSION['reset_user_id']) ||
    empty($_SESSION['password_reset_verified']) ||
    empty($_SESSION['password_reset_id'])
) {
    header('Location: forgot_password.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';

    $confirmPassword =
        $_POST['confirm_password'] ?? '';

    if ($password === '') {

        $error = 'Please enter a new password.';

    } elseif (strlen($password) < 8) {

        $error =
            'Password must be at least 8 characters.';

    } elseif ($password !== $confirmPassword) {

        $error = 'Passwords do not match.';

    } else {

        $userID =
            (int) $_SESSION['reset_user_id'];

        $resetID =
            (int) $_SESSION['password_reset_id'];

        $hashedPassword =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );

        try {

            $pdo->beginTransaction();

            /*
             * Update password and unlock account.
             */
            $stmt = $pdo->prepare(
                'UPDATE login
                 SET user_Password = ?,
                     no_Of_Tries = 0,
                     lock_var = FALSE
                 WHERE user_ID = ?'
            );

            $stmt->execute([
                $hashedPassword,
                $userID
            ]);

            /*
             * Mark verification code as used.
             */
            $stmt = $pdo->prepare(
                'UPDATE Password_Reset
                 SET used = TRUE
                 WHERE reset_ID = ?
                 AND user_ID = ?'
            );

            $stmt->execute([
                $resetID,
                $userID
            ]);

            $pdo->commit();

            unset(
                $_SESSION['reset_user_id'],
                $_SESSION['reset_email'],
                $_SESSION['password_reset_verified'],
                $_SESSION['password_reset_id'],
                $_SESSION['demo_reset_code']
            );

            $_SESSION['password_reset_success'] =
                'Password reset successfully. You can now sign in with your new password.';

            header('Location: index.php');
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $error =
                'Unable to reset your password. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Reset Password | Ashford University
    </title>

    <link
        rel="stylesheet"
        href="assets/css/login.css"
    >
</head>

<body class="login-page">

<main class="login-card">

    <img
        class="login-logo"
        src="assets/img/ashford-university-logo.svg"
        alt="Ashford University"
    >

    <h1>Reset Password</h1>

    <p>
        Enter your new password below.
    </p>

    <?php if ($error): ?>

        <div class="login-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form
        class="login-form"
        method="post"
    >

        <label for="password">

            New Password

            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                autocomplete="new-password"
                required
            >

        </label>

        <label for="confirm_password">

            Confirm New Password

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                autocomplete="new-password"
                required
            >

        </label>

        <button
            class="login-button"
            type="submit"
        >
            Reset Password
        </button>

    </form>

</main>

</body>
</html>
