<?php

require_once __DIR__ . '/auth.php';


// Navigation links for each type of user
function nav_items(string $role): array
{
    switch ($role) {

        case 'Student':
            return [
                ['Overview', '/student/dashboard.php'],
                ['Course Registration', '/student/registration.php'],
                ['My Schedule', '/student/schedule.php'],
                ['Master Schedule', '/faculty/master-schedule.php'],
                ['Unofficial Transcript', '/student/transcript.php'],
                ['Degree Audit', '/student/degree-audit.php'],
                ['Holds', '/student/holds.php'],
                ['My Advisor', '/student/advisor.php'],
                ['Majors & Minors', '/student/programs.php'],
                ['Catalog', '/student/catalog.php'],
                ['Buildings', '/student/buildings.php'],
                ['My Information', '/student/information.php']
            ];

        case 'Faculty':
            return [
                ['Overview', '/faculty/dashboard.php'],
                ['My Schedule', '/faculty/schedule.php'],
                ['Master Schedule', '/faculty/master-schedule.php'],
                ['Course Rosters', '/faculty/rosters.php'],
                ['Attendance History', '/faculty/attendance-history.php'],
                ['Advisees', '/faculty/advisees.php'],
                ['Catalog', '/faculty/catalog.php'],
                ['My Profile', '/faculty/profile.php']
            ];

        case 'Admin':
            return [
                ['Overview', '/admin/dashboard.php'],
                ['Users', '/admin/users.php'],
                ['Academic Management', '/admin/academic.php'],
                ['Sections', '/admin/sections.php'],
                ['Master Schedule', '/faculty/master-schedule.php']
            ];

        case 'StatStaff':
            return [
                ['Overview', '/statistics/dashboard.php'],
                ['Anonymous Reports', '/statistics/reports.php'],
                ['Master Schedule', '/faculty/master-schedule.php']
            ];

        default:
            return [];
    }
}


// Display the beginning of each portal page
function page_start(
    string $title,
    string $role,
    string $active = ''
): void {

    $name = $_SESSION['name'] ?? 'User';
    $email = $_SESSION['email'] ?? '';


    if ($role === 'StatStaff') {
        $portalName = 'Statistics Portal';
    } else {
        $portalName = $role . ' Portal';
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
            <?= e($title) ?> | Ashford University
        </title>

        <link
            rel="stylesheet"
            href="/assets/css/style.css"
        >

    </head>

    <body>

    <header class="topbar">

        <div class="brand">

            <div class="seal">
                N
            </div>

            <div>

                <b>Ashford University</b>

                <small>
                    <?= e($portalName) ?>
                </small>

            </div>

        </div>


        <div class="account">

            <div>

                <strong>
                    <?= e($name) ?>
                </strong>

                <small>
                    <?= e($email) ?>
                </small>

            </div>

            <a
                class="btn light"
                href="/logout.php"
            >
                Logout
            </a>

        </div>

    </header>


    <div class="shell">

        <aside class="sidebar">

            <?php foreach (nav_items($role) as $item): ?>

                <?php

                $label = $item[0];
                $url = $item[1];

                if ($active === $url) {
                    $class = 'active';
                } else {
                    $class = '';
                }

                ?>

                <a
                    class="<?= $class ?>"
                    href="<?= e($url) ?>"
                >
                    <?= e($label) ?>
                </a>

            <?php endforeach; ?>

        </aside>


        <main class="content">

            <h1>
                <?= e($title) ?>
            </h1>

    <?php
}


// Finish each portal page
function page_end(): void
{
    ?>

        </main>

    </div>

    <script src="/Ashford-University/assets/js/app.js"></script>

    </body>
    </html>

    <?php
}


// Display a message after an action is completed
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

?>