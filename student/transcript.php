<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get the student's course history
$sql = "SELECT
            Semester.semester_Name,
            Course.course_ID,
            Course.course_Name,
            Course.course_Credits,
            Student_History.Grade
        FROM Student_History
        JOIN Course
            ON Course.course_ID = Student_History.course_ID
        JOIN Semester
            ON Semester.semester_ID = Student_History.semester_ID
        WHERE Student_History.student_ID = ?
        ORDER BY Student_History.semester_ID DESC";

$courseHistory = all_rows(
    $pdo,
    $sql,
    [$studentID]
);


page_start(
    'Unofficial Transcript',
    'Student',
    'transcript.php'
);

?>

<p class="muted">
    View your completed courses and grades.
</p>


<?php if (count($courseHistory) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Semester</th>
                <th>Course</th>
                <th>Title</th>
                <th>Credits</th>
                <th>Grade</th>
            </tr>


            <?php foreach ($courseHistory as $course): ?>

                <tr>

                    <td>
                        <?= e($course['semester_Name']) ?>
                    </td>

                    <td>
                        <?= e($course['course_ID']) ?>
                    </td>

                    <td>
                        <?= e($course['course_Name']) ?>
                    </td>

                    <td>
                        <?= e($course['course_Credits']) ?>
                    </td>

                    <td>
                        <?= e($course['Grade'] ?? '-') ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php else: ?>

    <div class="card">
        <p>No course history is currently available.</p>
    </div>

<?php endif; ?>


<?php

page_end();

?>