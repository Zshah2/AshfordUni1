<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get the student's personal and academic information
$sql = "SELECT
            User.user_ID,
            User.first_Name,
            User.last_Name,
            User.street,
            User.city,
            User.state,
            User.zip_Code,
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


page_start(
    'My Information',
    'Student',
    'information.php'
);

?>

<p class="muted">
    View your personal and academic information.
</p>


<div class="card">

    <h2>Student Information</h2>

    <div class="grid2">

        <p>
            <strong>Student ID</strong>
            <br>
            <?= e($student['user_ID'] ?? '') ?>
        </p>


        <p>
            <strong>Name</strong>
            <br>
            <?= e(
                ($student['first_Name'] ?? '') . ' ' .
                ($student['last_Name'] ?? '')
            ) ?>
        </p>


        <p>
            <strong>Address</strong>
            <br>
            <?= e(
                ($student['street'] ?? '') . ', ' .
                ($student['city'] ?? '') . ', ' .
                ($student['state'] ?? '') . ' ' .
                ($student['zip_Code'] ?? '')
            ) ?>
        </p>


        <p>
            <strong>Level</strong>
            <br>
            <?= e($student['student_Type'] ?? '') ?>
        </p>


        <p>
            <strong>Major</strong>
            <br>
            <?= e($student['major_Name'] ?? 'Undeclared') ?>
        </p>


        <p>
            <strong>Year</strong>
            <br>
            <?= e($student['student_Year'] ?? '') ?>
        </p>

    </div>

</div>


<?php

page_end();

?>