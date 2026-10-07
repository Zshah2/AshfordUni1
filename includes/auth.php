<?php

// Start the session if it has not already been started
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// Make sure the user is logged in
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {

        header('Location: /index.php');

        exit;
    }
}


// Make sure the user has permission to view the page
function require_role(array $roles): void
{
    require_login();

    $userType = $_SESSION['user_type'] ?? '';

    if (!in_array($userType, $roles, true)) {

        http_response_code(403);

        exit(
            '403 - You do not have permission to view this page.'
        );
    }
}


// Safely display information on a page
function e($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


// Send each type of user to the correct dashboard
function role_home(string $role): string
{
    switch ($role) {

        case 'Student':
            return '/student/dashboard.php';

        case 'Faculty':
            return '/faculty/dashboard.php';

        case 'Admin':
            return '/admin/dashboard.php';

        case 'StatStaff':
            return '/statistics/dashboard.php';

        default:
            return '/index.php';
    }
}

?>
