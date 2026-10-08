<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Admin']);

$advisees = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Advisor'
);

$assignedSections = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Course_Section'
);

$totalEnrollments = one(
    $pdo,
    'SELECT COUNT(*) AS total FROM Enrollment'
);

page_start(
    'Welcome, ' . ($_SESSION['name'] ?? 'Admin'),
    'Admin',
    'dashboard.php'
);

?>

<div class="dashboard-page admin-dashboard">

    <section class="dashboard-hero">
        <div>
            <p class="dashboard-eyebrow">Administration</p>
            <h2>University overview</h2>
            <p>Current academic and staffing activity</p>
        </div>
        <span class="dashboard-badge">Live data</span>
    </section>

    <section class="dashboard-stats" aria-label="Administration summary">
        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Advisees</span>
            <strong><?= e($advisees['total']) ?></strong>
            <span class="dashboard-stat-note">Advisor assignments</span>
        </article>

        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Assigned sections</span>
            <strong><?= e($assignedSections['total']) ?></strong>
            <span class="dashboard-stat-note">Course sections</span>
        </article>

        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Total enrollments</span>
            <strong><?= e($totalEnrollments['total']) ?></strong>
            <span class="dashboard-stat-note">Active enrollment records</span>
        </article>
    </section>

</div>

<?php

page_end();

?>