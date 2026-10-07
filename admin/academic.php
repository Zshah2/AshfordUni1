<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Admin']);

// Get all departments
$departments = all_rows(
    $pdo,
    "SELECT dept_ID, dept_Name, Email, Phone_No
     FROM Department
     ORDER BY dept_Name"
);

// Get all courses
$courses = all_rows(
    $pdo,
    "SELECT course_ID, course_Name, course_Credits, course_Type
     FROM Course
     ORDER BY course_ID"
);

page_start(
    'Academic Management',
    'Admin',
    'academic.php'
);

?>

<div class="grid2">

    <div class="card">

        <h2>Departments</h2>

        <?php foreach ($departments as $department): ?>

            <p>
                <strong>
                    <?= e($department['dept_Name']) ?>
                </strong>

                <br>

                <?= e($department['Email']) ?>

                <br>

                <?= e($department['Phone_No']) ?>
            </p>

        <?php endforeach; ?>

    </div>


    <div class="card">

        <h2>Courses</h2>

        <?php foreach ($courses as $course): ?>

            <p>

                <strong>
                    <?= e($course['course_ID']) ?>
                </strong>

                -
                <?= e($course['course_Name']) ?>

                (<?= e($course['course_Credits']) ?> credits)

            </p>

        <?php endforeach; ?>

    </div>

</div>

<?php

page_end();

?>