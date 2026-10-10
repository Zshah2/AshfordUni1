
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


// Only System Admin can access protected pages
function require_system_admin(PDO $pdo): void
{
    // Must be logged in as an Admin
    require_role(['Admin']);

    // Retrieve security level from MySQL
    $stmt = $pdo->prepare(
        "SELECT security_Level
         FROM Admin_Permissions
         WHERE admin_ID = :admin_id"
    );

    $stmt->execute([
        'admin_id' => $_SESSION['user_id']
    ]);

    $securityLevel = $stmt->fetchColumn();

    // Only security level 1 is System Admin
    if ((int) $securityLevel !== 1) {
        http_response_code(403);
        exit('403 - System Admin access required.');
    }
}


// Get the administrator's display title
function admin_display_title(PDO $pdo): string
{
    require_role(['Admin']);

    $stmt = $pdo->prepare(
        "SELECT security_Level
         FROM Admin_Permissions
         WHERE admin_ID = :admin_id"
    );

    $stmt->execute([
        'admin_id' => $_SESSION['user_id']
    ]);

    $securityLevel = $stmt->fetchColumn();

    return (int) $securityLevel === 1
        ? 'System Admin'
        : 'Admin';
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
