<?php

session_start();

require_once __DIR__ . '/config/database.php';

if (empty($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit;
}

$error = '';

$userID = (int) $_SESSION['reset_user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $code = trim($_POST['code'] ?? '');

    if (!preg_match('/^[0-9]{6}$/', $code)) {

        $error = 'Please enter the 6-digit reset code.';

    } else {

        $stmt = $pdo->prepare(
            'SELECT reset_ID
             FROM Password_Reset
             WHERE user_ID = ?
             AND reset_Code = ?
             AND used = FALSE
             AND expires_At > NOW()
             ORDER BY reset_ID DESC
             LIMIT 1'
        );

        $stmt->execute([
            $userID,
            $code
        ]);

        $reset = $stmt->fetch();

        if ($reset) {

            $_SESSION['password_reset_verified'] = true;

            $_SESSION['password_reset_id'] =
                (int) $reset['reset_ID'];

            header('Location: reset_password.php');
            exit;

        } else {

            $error =
                'The reset code is incorrect or has expired.';
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
        Verify Code | Ashford University
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

    <h1>Verify Reset Code</h1>

    <p>
        Enter the 6-digit verification code.
    </p>

    <?php if (!empty($_SESSION['demo_reset_code'])): ?>

        <div class="login-success">

            Demo Reset Code:

            <strong>
                <?php
                echo htmlspecialchars(
                    $_SESSION['demo_reset_code']
                );
                ?>
            </strong>

        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="login-error" role="alert">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>

    <form
        class="login-form"
        method="post"
    >

        <label for="code">

            Verification Code

            <input
                type="text"
                id="code"
                name="code"
                maxlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                required
            >

        </label>

        <button
            class="login-button"
            type="submit"
        >
            Verify Code
        </button>

    </form>

    <p class="login-help">
        <a href="forgot_password.php">
            Request another code
        </a>
    </p>

</main>

</body>
</html>
