<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /index.php');
        exit;
    }
}

function require_role(array $roles): void
{
    require_login();

    $userType = $_SESSION['user_type'] ?? '';
    if (!in_array($userType, $roles, true)) {
        http_response_code(403);
        exit('403 - You do not have permission to view this page.');
    }
}

/**
 * Read the current administrator's numeric permission level.
 * A missing record does not grant administrator privileges.
 */
function current_admin_security_level(PDO $pdo): ?int
{
    require_role(['Admin']);

    $stmt = $pdo->prepare(
        'SELECT security_Level FROM Admin_Permissions WHERE admin_ID = :admin_id'
    );
    $stmt->execute(['admin_id' => (int) $_SESSION['user_id']]);
    $level = $stmt->fetchColumn();

    return $level === false ? null : (int) $level;
}

function is_system_admin(PDO $pdo): bool
{
    return current_admin_security_level($pdo) === 1;
}

function require_system_admin(PDO $pdo): void
{
    if (!is_system_admin($pdo)) {
        http_response_code(403);
        exit('403 - System Admin access required.');
    }
}

/**
 * Call before changing an existing administrator account.
 * Level 2 administrators may edit non-admin users, but not Admin accounts.
 * This does not replace server-side validation and authorization in action handlers.
 */
function require_admin_account_edit_permission(PDO $pdo, int $targetUserId): void
{
    require_role(['Admin']);

    $stmt = $pdo->prepare(
        'SELECT user_Type FROM `user` WHERE user_ID = :user_id'
    );
    $stmt->execute(['user_id' => $targetUserId]);
    $targetType = $stmt->fetchColumn();

    if ($targetType === false) {
        http_response_code(404);
        exit('404 - User not found.');
    }

    if ($targetType === 'Admin') {
        require_system_admin($pdo);
    }
}

function admin_display_title(PDO $pdo): string
{
    return is_system_admin($pdo) ? 'System Admin' : 'Admin';
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

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
