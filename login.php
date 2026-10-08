<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = 'Please enter your email and password.';
    header('Location: index.php');
    exit;
}

$sql = "SELECT login.*, user.first_Name, user.last_Name
        FROM login
        JOIN user ON user.user_ID = login.user_ID
        WHERE login.user_Email = ?
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);

$user = $stmt->fetch();

if (!$user) {
    $_SESSION['login_error'] = 'Invalid email or password.';
    header('Location: index.php');
    exit;
}

if ($user['lock_var']) {
    $_SESSION['login_error'] = 'This account is locked. Contact an administrator.';
    header('Location: index.php');
    exit;
}

$passwordCorrect =
    password_verify($password, $user['user_Password']) ||
    hash_equals((string) $user['user_Password'], $password);

if (!$passwordCorrect) {

    $tries = (int) $user['no_Of_Tries'] + 1;

    if ($tries >= 3) {
        $locked = 1;
    } else {
        $locked = 0;
    }

    $sql = "UPDATE login
            SET no_Of_Tries = ?, lock_var = ?
            WHERE user_ID = ?";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $tries,
        $locked,
        $user['user_ID']
    ]);

    if ($locked) {
        $_SESSION['login_error'] =
            'Account locked after 3 unsuccessful attempts.';
    } else {
        $_SESSION['login_error'] =
            'Invalid email or password.';
    }

    header('Location: index.php');
    exit;
}

$sql = "UPDATE login
        SET no_Of_Tries = 0, lock_var = 0
        WHERE user_ID = ?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user['user_ID']]);

$_SESSION['user_id'] = (int) $user['user_ID'];
$_SESSION['user_type'] = $user['user_Type'];
$_SESSION['email'] = $user['user_Email'];
$_SESSION['name'] = $user['first_Name'] . ' ' . $user['last_Name'];

header('Location: ' . role_home($user['user_Type']));
exit;
?>