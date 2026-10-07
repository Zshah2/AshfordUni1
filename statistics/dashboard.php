<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['StatStaff']);


// Count all students
$totalStudents = one(
    $pdo,
    "SELECT COUNT(*) AS total
     FROM Student"
);


// Count undergraduate students
$undergraduateStudents = one(
    $pdo,
    "SELECT COUNT(*) AS total
     FROM Student
     WHERE student_Type = 'Undergraduate'"
);


// Count graduate students
$graduateStudents = one(
    $pdo,
    "SELECT COUNT(*) AS total
     FROM Student
     WHERE student_Type = 'Graduate'"
);


// Count all enrollments
$totalEnrollments = one(
    $pdo,
    "SELECT COUNT(*) AS total
     FROM Enrollment"
);


page_start(
    'Statistics Dashboard',
    'StatStaff',
    'dashboard.php'
);

?>

<p class="muted">
    Anonymous academic statistics and enrollment information.
</p>


<div class="cards">

    <div class="card stat">

        <strong>
            <?= e($totalStudents['total']) ?>
        </strong>

        <span>Total Students</span>

    </div>


    <div class="card stat">

        <strong>
            <?= e($undergraduateStudents['total']) ?>
        </strong>

        <span>Undergraduate Students</span>

    </div>


    <div class="card stat">

        <strong>
            <?= e($graduateStudents['total']) ?>
        </strong>

        <span>Graduate Students</span>

    </div>


    <div class="card stat">

        <strong>
            <?= e($totalEnrollments['total']) ?>
        </strong>

        <span>Total Enrollments</span>

    </div>

</div>


<?php

page_end();

?>