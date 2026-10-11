
<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/functions.php';

require_role(['Admin']);

/*
|--------------------------------------------------------------------------
| Administrator Information
|--------------------------------------------------------------------------
*/

$adminName = $_SESSION['name'] ?? 'Administrator';
$adminTitle = admin_display_title($pdo);

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

function dashboard_count(PDO $pdo, string $table): int
{
    $allowedTables = [
        'user',
        'student',
        'faculty',
        'course',
        'course_section'
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");

    return (int) $stmt->fetchColumn();
}

$totalUsers = dashboard_count($pdo, 'user');
$totalStudents = dashboard_count($pdo, 'student');
$totalFaculty = dashboard_count($pdo, 'faculty');
$totalCourses = dashboard_count($pdo, 'course');
$totalSections = dashboard_count($pdo, 'course_section');

/*
|--------------------------------------------------------------------------
| Start Dashboard Layout
|--------------------------------------------------------------------------
*/

page_start('Dashboard Overview', 'Admin', 'dashboard.php');

?>

<style>
    .admin-welcome {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .admin-welcome h2 {
        margin: 0 0 8px;
        font-size: 24px;
        color: #172554;
    }

    .admin-welcome p {
        margin: 0;
        color: #64748b;
        line-height: 1.6;
    }

    .admin-role {
        display: inline-block;
        margin-top: 14px;
        padding: 7px 14px;
        border-radius: 20px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 13px;
        font-weight: 600;
    }

    .admin-section-title {
        margin: 26px 0 16px;
        font-size: 19px;
        font-weight: 700;
        color: #1e293b;
    }

    .admin-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .admin-stat-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 22px;
        min-width: 0;
        box-shadow: 0 3px 12px rgba(15, 23, 42, 0.04);
    }

    .admin-stat-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
    }

    .admin-stat-number {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }

    .admin-stat-note {
        margin-top: 10px;
        color: #94a3b8;
        font-size: 12px;
    }

    .admin-quick-links {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .admin-quick-link {
        display: block;
        padding: 20px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .admin-quick-link:hover {
        border-color: #93c5fd;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
    }

    .admin-quick-link strong {
        display: block;
        color: #1e293b;
        font-size: 15px;
        margin-bottom: 7px;
    }

    .admin-quick-link span {
        color: #64748b;
        font-size: 13px;
        line-height: 1.5;
    }

    @media (max-width: 1100px) {
        .admin-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .admin-stats,
        .admin-quick-links {
            grid-template-columns: 1fr;
        }

        .admin-welcome {
            padding: 18px;
        }
    }
</style>

<!-- Welcome Section -->

<section class="admin-welcome">

    <h2>
        Welcome, <?= e($adminName) ?>
    </h2>

    <p>
        Manage university information, monitor academic
        records, and access administrative tools from
        your dashboard.
    </p>

    <span class="admin-role">
        <?= e($adminTitle) ?>
    </span>

</section>

<!-- Statistics Section -->

<h2 class="admin-section-title">
    University Overview
</h2>

<div class="admin-stats">

    <div class="admin-stat-card">
        <div class="admin-stat-label">
            Total Users
        </div>

        <div class="admin-stat-number">
            <?= number_format($totalUsers) ?>
        </div>

        <div class="admin-stat-note">
            Registered university users
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-label">
            Total Students
        </div>

        <div class="admin-stat-number">
            <?= number_format($totalStudents) ?>
        </div>

        <div class="admin-stat-note">
            Undergraduate and graduate students
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-label">
            Total Faculty
        </div>

        <div class="admin-stat-number">
            <?= number_format($totalFaculty) ?>
        </div>

        <div class="admin-stat-note">
            Faculty records
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-label">
            Total Courses
        </div>

        <div class="admin-stat-number">
            <?= number_format($totalCourses) ?>
        </div>

        <div class="admin-stat-note">
            Courses in the catalog
        </div>
    </div>

    <div class="admin-stat-card">
        <div class="admin-stat-label">
            Course Sections
        </div>

        <div class="admin-stat-number">
            <?= number_format($totalSections) ?>
        </div>

        <div class="admin-stat-note">
            Scheduled course sections
        </div>
    </div>

</div>

<!-- Quick Access Section -->

<h2 class="admin-section-title">
    Quick Access
</h2>

<div class="admin-quick-links">

    <a href="/admin/users.php" class="admin-quick-link">
        <strong>Users Management →</strong>
        <span>
            View and manage university user records.
        </span>
    </a>

    <a href="/admin/academic.php" class="admin-quick-link">
        <strong>Academic Management →</strong>
        <span>
            Browse departments and university courses.
        </span>
    </a>

    <a href="/admin/sections.php" class="admin-quick-link">
        <strong>Course Sections →</strong>
        <span>
            View scheduled sections and enrollment availability.
        </span>
    </a>

    <a href="/faculty/master-schedule.php" class="admin-quick-link">
        <strong>Master Schedule →</strong>
        <span>
            Search university course offerings and schedules.
        </span>
    </a>

</div>

<?php

page_end();

?>
