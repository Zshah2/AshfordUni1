<?php

session_start();

require_once __DIR__ . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

if ($email === '' || $password === '') {
    $error = 'Please enter your email and password.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Please enter a valid email address.';
} elseif (!str_ends_with(strtolower($email), '@ashford.edu')) {
    $error = 'Please use your Ashford University email address.';
} elseif ($password !== $confirmPassword) {
    $error = 'Passwords do not match.';
} elseif (strlen($password) < 8) {
    $error = 'Password must be at least 8 characters.';
} else {
    $stmt = $pdo->prepare('SELECT user_ID FROM login WHERE user_Email = ?');
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $error = 'An account with this email already exists.';
    } else {
        try {
            $pdo->beginTransaction();

            $userID = (int) $pdo->query('SELECT MAX(user_ID) AS max_id FROM User')->fetch()['max_id'] + 1;
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $userStatement = $pdo->prepare(
                'INSERT INTO User
                 (user_ID, first_Name, middle_Name, last_Name, gender, DOB,
                  street, city, state, zip_Code, user_Type)
                 VALUES (?, ?, NULL, ?, ?, CURDATE(), ?, ?, ?, ?, ?)'
            );
            $userStatement->execute([
                $userID,
                'New',
                'Student',
                'Other',
                'Unknown',
                'Unknown',
                'Unknown',
                'Unknown',
                'Student'
            ]);

            $loginStatement = $pdo->prepare(
                'INSERT INTO login
                 (user_ID, user_Email, user_Password, no_Of_Tries, lock_var, user_Type)
                 VALUES (?, ?, ?, 0, FALSE, ?)'
            );
            $loginStatement->execute([$userID, $email, $hashedPassword, 'Student']);

            $pdo->commit();
            $_SESSION['registration_success'] = 'Account created successfully. You can now log in.';
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Unable to create the account. Please try again.';
        }
    }
}

if (isset($error)) {
    $_SESSION['registration_error'] = $error;
    $_SESSION['registration_input'] = ['email' => $email];
}

header('Location: index.php');
exit;
