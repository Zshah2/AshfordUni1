
<?php

require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

// Only Admin users can access this dashboard
require_role(['Admin']);

/*
|--------------------------------------------------------------------------
| ADMIN SECURITY
|--------------------------------------------------------------------------
| Level 1 = System Admin
| Level 2 = Admin
|--------------------------------------------------------------------------
*/

$adminTitle = admin_display_title($pdo);

/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
| Display only the four main university statistics.
|--------------------------------------------------------------------------
*/

$totalStudents = one(
    $pdo,
    "SELECT COUNT(*) AS total FROM Student"
);

$totalFaculty = one(
    $pdo,
    "SELECT COUNT(*) AS total FROM Faculty"
);

$totalCourses = one(
    $pdo,
    "SELECT COUNT(*) AS total FROM Course"
);

$totalCourseSections = one(
    $pdo,
    "SELECT COUNT(*) AS total FROM Course_Section"
);

/*
|--------------------------------------------------------------------------
| PAGE START
|--------------------------------------------------------------------------
| Keep 'Admin' so the sidebar navigation works.
|--------------------------------------------------------------------------
*/

page_start(
    'Welcome, ' . ($_SESSION['name'] ?? 'Admin'),
    'Admin',
    'dashboard.php'
);

?>

<style>
.admin-dashboard {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.admin-dashboard .dashboard-hero {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-dashboard .dashboard-stats {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.admin-dashboard .dashboard-stat {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1.5rem;
    min-width: 0;
}

.admin-dashboard .dashboard-stat-label {
    font-size: 0.9rem;
    font-weight: 600;
}

.admin-dashboard .dashboard-stat strong {
    font-size: 2rem;
    line-height: 1.2;
}

.admin-dashboard .dashboard-stat-note {
    font-size: 0.8rem;
    opacity: 0.7;
}

@media (max-width: 1100px) {
    .admin-dashboard .dashboard-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .admin-dashboard .dashboard-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-page admin-dashboard">

    <section class="dashboard-hero">

        <div>
            <p class="dashboard-eyebrow">
                <?= e($adminTitle) ?> Dashboard
            </p>

            <h2>University Overview</h2>

            <p>
                Current university information
            </p>
        </div>

        <span class="dashboard-badge">
            Live Data
        </span>

    </section>

    <section
        class="dashboard-stats"
        aria-label="University statistics"
    >

        <!-- TOTAL STUDENTS -->
        <article class="dashboard-stat card">

            <span class="dashboard-stat-label">
                Total Students
            </span>

            <strong>
                <?= e($totalStudents['total']) ?>
            </strong>

            <span class="dashboard-stat-note">
                Student records
            </span>

        </article>


        <!-- FACULTY MEMBERS -->
        <article class="dashboard-stat card">

            <span class="dashboard-stat-label">
                Faculty Members
            </span>

            <strong>
                <?= e($totalFaculty['total']) ?>
            </strong>

            <span class="dashboard-stat-note">
                Faculty records
            </span>

        </article>


        <!-- TOTAL COURSES -->
        <article class="dashboard-stat card">

            <span class="dashboard-stat-label">
                Total Courses
            </span>

            <strong>
                <?= e($totalCourses['total']) ?>
            </strong>

            <span class="dashboard-stat-note">
                University catalog courses
            </span>

        </article>


        <!-- COURSE SECTIONS -->
        <article class="dashboard-stat card">

            <span class="dashboard-stat-label">
                Course Sections
            </span>

            <strong>
                <?= e($totalCourseSections['total']) ?>
            </strong>

            <span class="dashboard-stat-note">
                Total course sections
            </span>

        </article>

    </section>

</div>

<?php

page_end();

?>
