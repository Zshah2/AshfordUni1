<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get student information
$sql = "SELECT
            User.first_Name,
            User.last_Name,
            Student.student_Year,
            Student.student_Type,
            Major.major_Name
        FROM User
        JOIN Student
            ON Student.student_ID = User.user_ID
        LEFT JOIN Major
            ON Major.major_ID = Student.major_ID
        WHERE User.user_ID = ?";

$student = one(
    $pdo,
    $sql,
    [$studentID]
);


// Get the Fall 2026 semester ID
$semesterID = semester_id(
    $pdo,
    'Fall 2026'
);


// Get the number of credits for the current semester
$sql = "SELECT
            COALESCE(SUM(Course.course_Credits), 0) AS total
        FROM Enrollment
        JOIN Course_Section
            ON Course_Section.CRN = Enrollment.CRN
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        WHERE Enrollment.student_ID = ?
        AND Enrollment.semester_ID = ?";

$currentCredits = one(
    $pdo,
    $sql,
    [
        $studentID,
        $semesterID
    ]
);


// Count the student's holds
$sql = "SELECT COUNT(*) AS total
        FROM Student_Hold
        WHERE student_ID = ?";

$holds = one(
    $pdo,
    $sql,
    [$studentID]
);


// Calculate credits earned from completed courses
$sql = "SELECT
            COALESCE(SUM(Course.course_Credits), 0) AS total
        FROM Student_History
        JOIN Course
            ON Course.course_ID = Student_History.course_ID
        WHERE Student_History.student_ID = ?
        AND Student_History.Grade IS NOT NULL";

$earnedCredits = one(
    $pdo,
    $sql,
    [$studentID]
);


// Get the student's Fall 2026 schedule
$sql = "SELECT
            Course.course_ID,
            Course.course_Name,
            Course.course_Credits,
            Course_Section.CRN,
            Course_Section.section_No,
            Semester.semester_Name,
            User.first_Name,
            User.last_Name
        FROM Enrollment
        JOIN Course_Section
            ON Course_Section.CRN = Enrollment.CRN
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        JOIN User
            ON User.user_ID = Course_Section.faculty_ID
        WHERE Enrollment.student_ID = ?
        AND Course_Section.semester_ID = ?
        ORDER BY Course.course_ID";

$schedule = all_rows(
    $pdo,
    $sql,
    [
        $studentID,
        $semesterID
    ]
);


// Get the student's advisor
$sql = "SELECT
            User.first_Name,
            User.last_Name,
            Faculty.faculty_Rank
        FROM Advisor
        JOIN User
            ON User.user_ID = Advisor.faculty_ID
        JOIN Faculty
            ON Faculty.faculty_ID = Advisor.faculty_ID
        WHERE Advisor.student_ID = ?
        LIMIT 1";

$advisor = one(
    $pdo,
    $sql,
    [$studentID]
);


page_start(
    'Welcome back, ' . ($student['first_Name'] ?? 'Student'),
    'Student',
    'dashboard.php'
);

?>

<div class="dashboard-page">

    <section class="dashboard-hero">
        <div>
            <p class="dashboard-eyebrow">Student overview</p>
            <h2>Fall 2026 academic summary</h2>
            <p>
                <?= e(($student['student_Year'] ?? '') . ' · ' . ($student['student_Type'] ?? '') . ' · ' . ($student['major_Name'] ?? 'No major')) ?>
            </p>
        </div>
        <span class="dashboard-badge">Active semester</span>
    </section>

    <section class="dashboard-stats" aria-label="Academic summary">
        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Fall 2026 credits</span>
            <strong><?= e($currentCredits['total'] ?? 0) ?></strong>
            <span class="dashboard-stat-note">Current semester</span>
        </article>

        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Cumulative GPA</span>
            <strong>—</strong>
            <span class="dashboard-stat-note">Not available</span>
        </article>

        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Credits earned</span>
            <strong><?= e($earnedCredits['total'] ?? 0) ?></strong>
            <span class="dashboard-stat-note">Completed courses</span>
        </article>

        <article class="dashboard-stat card">
            <span class="dashboard-stat-label">Holds</span>
            <strong><?= e((int) ($holds['total'] ?? 0) === 0 ? 'None' : $holds['total']) ?></strong>
            <span class="dashboard-stat-note">Current status</span>
        </article>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-heading">
            <div>
                <p class="dashboard-eyebrow">Academic support</p>
                <h2>Your advisor</h2>
            </div>
        </div>

        <div class="dashboard-info-card card">
            <?php if ($advisor): ?>
                <span class="dashboard-icon">A</span>
                <div>
                    <strong><?= e(($advisor['faculty_Rank'] ?: '') . ' ' . $advisor['first_Name'] . ' ' . $advisor['last_Name']) ?></strong>
                    <p>Assigned academic advisor</p>
                </div>
            <?php else: ?>
                <p>No advisor assigned.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-heading">
            <div>
                <p class="dashboard-eyebrow">Schedule</p>
                <h2>Fall 2026 courses</h2>
            </div>
            <span class="dashboard-count"><?= count($schedule) ?> course<?= count($schedule) === 1 ? '' : 's' ?></span>
        </div>

<?php if (count($schedule) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Course</th>
                <th>Course Name</th>
                <th>CRN</th>
                <th>Professor</th>
                <th>Credits</th>
                <th>Semester</th>
            </tr>


            <?php foreach ($schedule as $course): ?>

                <tr>

                    <td>
                        <?= e($course['course_ID']) ?>
                    </td>

                    <td>
                        <?= e($course['course_Name']) ?>
                    </td>

                    <td>
                        <?= e($course['CRN']) ?>
                    </td>

                    <td>
                        <?= e(
                            $course['first_Name'] . ' ' .
                            $course['last_Name']
                        ) ?>
                    </td>

                    <td>
                        <?= e($course['course_Credits']) ?>
                    </td>

                    <td>
                        <?= e($course['semester_Name']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php else: ?>

    <div class="card">
        <p>You are not registered for any Fall 2026 courses.</p>
    </div>

<?php endif; ?>


<?php

page_end();

?>
