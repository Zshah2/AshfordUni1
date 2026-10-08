<?php
session_start();

require_once __DIR__ . '/config/database.php';

if (!empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/auth.php';
    header('Location: ' . role_home($_SESSION['user_type']));
    exit;
}

$error = $_SESSION['login_error'] ?? '';
$registrationError = $_SESSION['registration_error'] ?? '';
$registrationSuccess = $_SESSION['registration_success'] ?? '';
$passwordResetSuccess = $_SESSION['password_reset_success'] ?? '';
$registrationInput = $_SESSION['registration_input'] ?? [];

unset(
    $_SESSION['login_error'],
    $_SESSION['registration_error'],
    $_SESSION['registration_success'],
    $_SESSION['password_reset_success'],
    $_SESSION['registration_input']
);

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

        <?php if ($registrationSuccess): ?>
            <div class="login-success" role="status">
                <?php echo htmlspecialchars($registrationSuccess); ?>
            </div>
        <?php endif; ?>

        <?php if ($passwordResetSuccess): ?>
    <div class="login-success" role="status">
        <?php echo htmlspecialchars($passwordResetSuccess); ?>
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
            Need an account?
            <button
                class="login-register-button"
                type="button"
                data-open-registration
            >Create one</button>
        </p>

        <p class="login-footer">
            &copy; 2026 Ashford University
        </p>
    </main>

    <div
        class="registration-modal"
        data-registration-modal
        aria-hidden="true"
        role="dialog"
        aria-modal="true"
        aria-labelledby="registration-title"
    >
        <div class="registration-modal__backdrop" data-close-registration></div>

        <section class="registration-modal__dialog registration-modal__dialog--small">
            <button
                class="registration-modal__close"
                type="button"
                aria-label="Close registration form"
                data-close-registration
            >&times;</button>

            <div class="registration-modal__header">
                <span class="registration-modal__eyebrow">Student account</span>
                <h2 id="registration-title">Create your account</h2>
                <p>Use your university email and a password of at least 8 characters.</p>
            </div>

            <?php if ($registrationError): ?>
                <div class="registration-modal__error" role="alert">
                    <?php echo htmlspecialchars($registrationError); ?>
                </div>
            <?php endif; ?>

            <form class="registration-form" action="register.php" method="post">
                <label>
                    University email
                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($registrationInput['email'] ?? ''); ?>"
                        autocomplete="email"
                        placeholder="first.last@ashford.edu"
                        pattern="^[^@]+@ashford\.edu$"
                        title="Use your Ashford University email address."
                        required
                    >
                </label>

                <label>
                    Password
                    <input
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </label>

                <label>
                    Confirm password
                    <input
                        type="password"
                        name="confirm_password"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </label>

                <button class="login-button registration-form__submit" type="submit">Create account</button>
            </form>
        </section>
    </div>
<script src="assets/js/app.js"></script>

</body>
</html>
