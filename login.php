
<?php

/**
 * Ashford University
 * Login Authentication with Amazon SES Email OTP
 */

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/otp.php';

// Only allow POST login submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] =
        'Please enter your email and password.';

    header('Location: /index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Find user account
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        login.*,
        user.first_Name,
        user.last_Name
    FROM login
    JOIN user
        ON user.user_ID = login.user_ID
    WHERE login.user_Email = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['login_error'] =
        'Invalid email or password.';

    header('Location: /index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Check account lock
|--------------------------------------------------------------------------
*/

if ((int) $user['lock_var'] === 1) {
    $_SESSION['login_error'] =
        'This account is locked. Contact an administrator.';

    header('Location: /index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Verify password
|--------------------------------------------------------------------------
|
| Plaintext fallback retained for existing test accounts.
| Migrate all accounts to password_hash() before production.
|
*/

$storedPassword = (string) $user['user_Password'];

$passwordCorrect =
    password_verify($password, $storedPassword) ||
    hash_equals($storedPassword, $password);

if (!$passwordCorrect) {

    $tries = (int) $user['no_Of_Tries'] + 1;
    $locked = $tries >= 3 ? 1 : 0;

    $stmt = $pdo->prepare(
        "UPDATE login
         SET no_Of_Tries = ?,
             lock_var = ?
         WHERE user_ID = ?"
    );

    $stmt->execute([
        $tries,
        $locked,
        $user['user_ID']
    ]);

    $_SESSION['login_error'] = $locked
        ? 'Account locked after 3 unsuccessful attempts.'
        : 'Invalid email or password.';

    header('Location: /index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Check for existing unexpired OTP
|--------------------------------------------------------------------------
|
| Reuse an existing OTP rather than immediately attempting
| another email within the 60-second resend cooldown.
|
*/

$stmt = $pdo->prepare(
    "SELECT
        attempts,
        expires_at > UTC_TIMESTAMP() AS is_valid
     FROM login_otp
     WHERE user_ID = ?"
);

$stmt->execute([(int) $user['user_ID']]);

$existingOtp = $stmt->fetch(PDO::FETCH_ASSOC);

$canReuseOtp =
    $existingOtp &&
    (int) $existingOtp['is_valid'] === 1 &&
    (int) $existingOtp['attempts'] < 5;

/*
|--------------------------------------------------------------------------
| Clear previous authentication state
|--------------------------------------------------------------------------
*/

unset(
    $_SESSION['user_id'],
    $_SESSION['user_type'],
    $_SESSION['email'],
    $_SESSION['name'],
    $_SESSION['otp_pending'],
    $_SESSION['otp_error']
);

session_regenerate_id(true);

/*
|--------------------------------------------------------------------------
| Prepare pending OTP login
|--------------------------------------------------------------------------
|
| This does not grant access to protected dashboards.
|
*/

$_SESSION['otp_pending'] = [
    'user_id' => (int) $user['user_ID'],
    'email' => $user['user_Email'],
    'role' => $user['user_Type'],
    'name' => trim(
        $user['first_Name'] . ' ' . $user['last_Name']
    ),
    'started_at' => time()
];

/*
|--------------------------------------------------------------------------
| Send verification code through Amazon SES
|--------------------------------------------------------------------------
*/

if (!$canReuseOtp) {

    try {

        $sent = issue_login_otp(
            $pdo,
            (int) $user['user_ID'],
            $user['user_Email']
        );

    } catch (Throwable $e) {

        error_log(
            'OTP initialization failed: ' .
            $e->getMessage()
        );

        $sent = false;
    }

    if (!$sent) {

        unset($_SESSION['otp_pending']);

        $_SESSION['login_error'] =
            'Unable to send a verification code right now. ' .
            'Please wait a minute and try again.';

        header('Location: /index.php');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Reset failed password attempts
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "UPDATE login
     SET no_Of_Tries = 0,
         lock_var = 0
     WHERE user_ID = ?"
);

$stmt->execute([(int) $user['user_ID']]);

/*
|--------------------------------------------------------------------------
| Redirect to OTP verification
|--------------------------------------------------------------------------
*/

header('Location: /verify-otp.php');
exit;
