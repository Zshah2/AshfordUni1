
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/otp.php';

$pending = $_SESSION['otp_pending'] ?? null;

if (!$pending || !is_array($pending)) {
    header('Location: /index.php');
    exit;
}

$userId = (int) $pending['user_id'];
$email = (string) $pending['email'];

$error = '';
$message = '';

if (time() - (int) $pending['started_at'] > 900) {
    unset($_SESSION['otp_pending']);
    $_SESSION['login_error'] =
        'Your login session expired. Please sign in again.';
    header('Location: /index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? 'verify';

    if ($action === 'resend') {

        try {
            $sent = issue_login_otp($pdo, $userId, $email);
        } catch (Throwable $e) {
            error_log('OTP resend failed: ' . $e->getMessage());
            $sent = false;
        }

        if ($sent) {
            $message = 'A new verification code has been sent.';
        } else {
            $error =
                'Unable to resend yet. Wait at least 60 seconds ' .
                'or check email service availability.';
        }

    } elseif ($action === 'verify') {

        $code = trim($_POST['otp'] ?? '');

        try {
            $valid = verify_login_otp($pdo, $userId, $code);
        } catch (Throwable $e) {
            error_log('OTP verification failed: ' . $e->getMessage());
            $valid = false;
        }

        if ($valid) {

            // Confirm the account is still active and authorized.
            $stmt = $pdo->prepare(
                "SELECT login.user_ID, login.user_Email,
                        login.user_Type, login.lock_var,
                        user.first_Name, user.last_Name
                 FROM login
                 JOIN user ON user.user_ID = login.user_ID
                 WHERE login.user_ID = ?
                 LIMIT 1"
            );
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            if (
                !$user ||
                (int) $user['lock_var'] !== 0 ||
                $user['user_Email'] !== $email ||
                $user['user_Type'] !== $pending['role']
            ) {
                unset($_SESSION['otp_pending']);
                $_SESSION['login_error'] =
                    'Account details changed. Please sign in again.';
                header('Location: /index.php');
                exit;
            }

            session_regenerate_id(true);

            unset($_SESSION['otp_pending']);

            $_SESSION['user_id'] = (int) $user['user_ID'];
            $_SESSION['user_type'] = $user['user_Type'];
            $_SESSION['email'] = $user['user_Email'];
            $_SESSION['name'] =
                $user['first_Name'] . ' ' . $user['last_Name'];

            header('Location: ' . role_home($user['user_Type']));
            exit;
        }

        $error = 'Invalid, expired, or exhausted verification code.';

    } else {
        $error = 'Invalid action.';
    }
}

function otp_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification | Ashford University</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: #f3f5f9;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verification-card {
            width: 100%;
            max-width: 440px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(20, 45, 78, 0.12);
        }

        .card-header {
            background: #142d4e;
            color: white;
            padding: 28px 20px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 22px;
            margin: 0;
        }

        .card-header p {
            color: #e9be67;
            font-size: 12px;
            letter-spacing: 2px;
            margin: 10px 0 0;
        }

        .card-body {
            padding: 32px;
        }

        h2 {
            color: #142d4e;
            margin: 0 0 12px;
            font-size: 23px;
        }

        .description {
            color: #526176;
            line-height: 1.6;
            font-size: 14px;
        }

        .email {
            font-weight: bold;
            color: #142d4e;
            overflow-wrap: anywhere;
        }

        label {
            display: block;
            font-weight: bold;
            font-size: 14px;
            color: #142d4e;
            margin: 24px 0 10px;
        }

        input {
            width: 100%;
            padding: 16px;
            font-size: 26px;
            text-align: center;
            letter-spacing: 7px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
        }

        input:focus {
            outline: 2px solid #e9be67;
            border-color: #142d4e;
        }

        button {
            width: 100%;
            padding: 14px;
            border: 0;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
        }

        .verify-button {
            background: #142d4e;
            color: white;
            margin-top: 18px;
        }

        .resend-button {
            background: #f3f5f9;
            color: #142d4e;
            margin-top: 12px;
        }

        .notice {
            padding: 12px;
            border-radius: 6px;
            margin: 18px 0;
            font-size: 14px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #142d4e;
            font-size: 14px;
        }

        .footnote {
            font-size: 12px;
            text-align: center;
            color: #64748b;
            margin-top: 20px;
        }
    </style>
</head>

<body>

<div class="verification-card">

    <div class="card-header">
        <h1>ASHFORD UNIVERSITY</h1>
        <p>ACCOUNT SECURITY</p>
    </div>

    <div class="card-body">

        <h2>Verify Your Email</h2>

        <p class="description">
            We sent a six-digit verification code to:
        </p>

        <p class="email">
            <?= otp_escape($email) ?>
        </p>

        <p class="description">
            Enter your code below to complete your login.
            Each code expires after 5 minutes.
        </p>

        <?php if ($error): ?>
            <div class="notice error">
                <?= otp_escape($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="notice success">
                <?= otp_escape($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/verify-otp.php">
            <input type="hidden" name="action" value="verify">

            <label for="otp">Verification Code</label>

            <input
                type="text"
                id="otp"
                name="otp"
                maxlength="6"
                minlength="6"
                pattern="[0-9]{6}"
                inputmode="numeric"
                autocomplete="one-time-code"
                placeholder="000000"
                required
                autofocus
            >

            <button type="submit" class="verify-button">
                Verify &amp; Continue
            </button>
        </form>

        <form method="POST" action="/verify-otp.php">
            <input type="hidden" name="action" value="resend">

            <button type="submit" class="resend-button">
                Resend Verification Code
            </button>
        </form>

        <a class="back-link" href="/index.php">
            Back to Login
        </a>

        <p class="footnote">
            Never share your verification code with anyone.
        </p>

    </div>
</div>

</body>
</html>
