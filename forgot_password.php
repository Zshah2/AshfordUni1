<?php

session_start();

require_once __DIR__ . '/config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');

    if ($email === '') {

        $error = 'Please enter your university email.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = 'Please enter a valid email address.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT user_ID
             FROM login
             WHERE user_Email = ?
             LIMIT 1'
        );

        $stmt->execute([$email]);

        $user = $stmt->fetch();

        if ($user) {

            $userID = (int) $user['user_ID'];

            // Invalidate old reset codes
            $stmt = $pdo->prepare(
                'UPDATE Password_Reset
                 SET used = TRUE
                 WHERE user_ID = ?
                 AND used = FALSE'
            );

            $stmt->execute([$userID]);

            // Generate 6-digit code
            $resetCode = (string) random_int(100000, 999999);

            // Code expires after 15 minutes
            $expiresAt = date(
                'Y-m-d H:i:s',
                time() + 900
            );

            $stmt = $pdo->prepare(
                'INSERT INTO Password_Reset
                 (user_ID, reset_Code, expires_At, used)
                 VALUES (?, ?, ?, FALSE)'
            );

            $stmt->execute([
                $userID,
                $resetCode,
                $expiresAt
            ]);

            $_SESSION['reset_user_id'] = $userID;
            $_SESSION['reset_email'] = $email;

            /*
             * FOR YOUR CLASS DEMO:
             * Store the code in session so it can be displayed.
             *
             * Once actual email is implemented,
             * remove this.
             */
            $_SESSION['demo_reset_code'] = $resetCode;

            header('Location: verify_reset.php');
            exit;

        } else {

            /*
             * Generic response so we don't reveal
             * which email addresses have accounts.
             */
            $error = 'No account could be found with that university email.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Forgot Password | Ashford University
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

    <h1>Forgot Password</h1>

    <p>
        Enter your university email to reset your password.
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

        <button
            class="login-button"
            type="submit"
        >
            Send Reset Code
        </button>

    </form>

    <p class="login-help">
        <a href="index.php">
            Back to Sign In
        </a>
    </p>

</main>

</body>
</html>
