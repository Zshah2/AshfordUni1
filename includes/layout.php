<?php
require_once __DIR__ . '/auth.php';

function nav_items(string $role): array
{
    switch ($role) {
        case 'Student':
            return [
                ['Overview', '/student/dashboard.php', 'layout-dashboard'],
                ['Course Registration', '/student/registration.php', 'pencil'],
                ['My Schedule', '/student/schedule.php', 'calendar'],
                ['Master Schedule', '/faculty/master-schedule.php', 'calendar'],
                ['Unofficial Transcript', '/student/transcript.php', 'file-text'],
                ['Degree Audit', '/student/degree-audit.php', 'check-circle'],
                ['Holds', '/student/holds.php', 'flag'],
                ['My Advisor', '/student/advisor.php', 'user'],
                ['Majors & Minors', '/student/programs.php', 'graduation-cap'],
                ['Catalog', '/student/catalog.php', 'book-open'],
                ['Buildings', '/student/buildings.php', 'building'],
                ['My Information', '/student/information.php', 'user'],
            ];
        case 'Faculty':
            return [
                ['Overview', '/faculty/dashboard.php', 'layout-dashboard'],
                ['My Schedule', '/faculty/schedule.php', 'calendar'],
                ['Master Schedule', '/faculty/master-schedule.php', 'calendar'],
                ['Course Rosters', '/faculty/rosters.php', 'users'],
                ['Attendance History', '/faculty/attendance-history.php', 'check-circle'],
                ['Advisees', '/faculty/advisees.php', 'users'],
                ['Catalog', '/faculty/catalog.php', 'book-open'],
                ['My Profile', '/faculty/profile.php', 'user'],
            ];
        case 'Admin':
            return [
                ['Overview', '/admin/dashboard.php', 'layout-dashboard'],
                ['Roster', '/admin/users.php', 'users'],
                ['Academic Management', '/admin/academic.php', 'book-open'],
                ['Course Sections', '/admin/sections.php', 'layers'],
                ['Master Schedule', '/faculty/master-schedule.php', 'calendar'],
            ];
        case 'StatStaff':
            return [
                ['Overview', '/statistics/dashboard.php', 'layout-dashboard'],
                ['Anonymous Reports', '/statistics/reports.php', 'file-text'],
                ['Master Schedule', '/faculty/master-schedule.php', 'calendar'],
            ];
        default:
            return [];
    }
}

/** Icons are inline SVG so they do not depend on operating-system symbol fonts. */
function sidebar_icon(string $name): string
{
    $paths = [
        'layout-dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
        'book-open' => '<path d="M12 7v14M3 18V5a2 2 0 0 1 2-2h3a4 4 0 0 1 4 4 4 4 0 0 1 4-4h3a2 2 0 0 1 2 2v13a2 2 0 0 0-2-2h-3a4 4 0 0 0-4 4 4 4 0 0 0-4-4H5a2 2 0 0 0-2 2Z"/>',
        'layers' => '<path d="m12 2 9 5-9 5-9-5 9-5ZM3 12l9 5 9-5M3 17l9 5 9-5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'pencil' => '<path d="m15 5 4 4M4 20l4.5-1 11-11a2.83 2.83 0 0 0-4-4l-11 11L4 20Z"/>',
        'file-text' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h8"/>',
        'check-circle' => '<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>',
        'flag' => '<path d="M4 22V4m0 0c5-3 11 3 16 0v12c-5 3-11-3-16 0"/>',
        'graduation-cap' => '<path d="m2 10 10-6 10 6-10 6-10-6ZM6 12v5c3 3 9 3 12 0v-5M22 10v7"/>',
        'building' => '<rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22v-4h6v4M8 6h1M15 6h1M8 10h1M15 10h1M8 14h1M15 14h1"/>',
    ];
    $path = $paths[$name] ?? $paths['layout-dashboard'];
    return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
}

function page_start(string $title, string $role, string $active = ''): void
{
    $name = $_SESSION['name'] ?? 'User';
    $email = $_SESSION['email'] ?? '';

    // Preserve role-based navigation and System Admin display title.
    $displayRole = $role;
    if ($role === 'Admin') {
        global $pdo;
        if (isset($pdo) && $pdo instanceof PDO) {
            $displayRole = admin_display_title($pdo);
        }
    }

    $portalName = $role === 'StatStaff' ? 'Statistics Portal' : $displayRole . ' Portal';
    // The URL is the source of truth; $active is kept for backwards compatibility.
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
    $currentPath = rtrim($currentPath, '/');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($title) ?> | Ashford University</title>
        <link rel="stylesheet" href="/assets/css/style.css">
    </head>
    <body>
    <header class="topbar">
        <div class="brand">
            <div class="seal">N</div>
            <div>
                <b>Ashford University</b>
                <small><?= e($portalName) ?></small>
            </div>
        </div>
        <div class="account">
            <div>
                <strong><?= e($name) ?></strong>
                <small><?= e($email) ?></small>
            </div>
            <a class="btn light" href="/logout.php">Logout</a>
        </div>
    </header>

    <div class="shell">
        <aside class="sidebar">
            <?php foreach (nav_items($role) as $item): ?>
                <?php
                [$label, $url, $icon] = $item;
                $isActive = $currentPath === rtrim($url, '/');
                ?>
                <a class="<?= $isActive ? 'active' : '' ?>" href="<?= e($url) ?>"<?= $isActive ? ' aria-current="page"' : '' ?> style="display:flex;align-items:center;gap:12px;">
                    <span aria-hidden="true" style="width:20px;height:20px;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;"><?= sidebar_icon($icon) ?></span>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </aside>
        <main class="content">
            <h1><?= e($title) ?></h1>
    <?php
}

function page_end(): void
{
    ?>
        </main>
    </div>
    <script src="/assets/js/app.js"></script>
    </body>
    </html>
    <?php
}

function flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $flashMessage = $_SESSION['flash'];
        unset($_SESSION['flash']);
        ?>
        <div class="alert <?= e($flashMessage['type']) ?>">
            <?= e($flashMessage['message']) ?>
        </div>
        <?php
    }
}
