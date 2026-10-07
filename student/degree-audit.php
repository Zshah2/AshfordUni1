<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Get the student's major and department
$sql = "SELECT
            Major.major_Name,
            Department.dept_Name
        FROM Student
        JOIN Major
            ON Major.major_ID = Student.major_ID
        JOIN Department
            ON Department.dept_ID = Major.dept_ID
        WHERE Student.student_ID = ?";

$major = one(
    $pdo,
    $sql,
    [$studentID]
);


// Get the student's completed course history
$sql = "SELECT
            Course.course_ID,
            Course.course_Name,
            Student_History.Grade
        FROM Student_History
        JOIN Course
            ON Course.course_ID = Student_History.course_ID
        WHERE Student_History.student_ID = ?
        ORDER BY Course.course_ID";

$courseHistory = all_rows(
    $pdo,
    $sql,
    [$studentID]
);


page_start(
    'Degree Audit',
    'Student',
    'degree-audit.php'
);

?>

<p class="muted">
    View your major and completed coursework.
</p>


<div class="card">

    <h2>
        <?= e($major['major_Name'] ?? 'Major Not Assigned') ?>
    </h2>

    <?php if (!empty($major['dept_Name'])): ?>

        <p class="muted">
            Department:
            <?= e($major['dept_Name']) ?>
        </p>

    <?php endif; ?>

</div>


<h2 class="section-title">
    Course Progress
</h2>


<?php if (count($courseHistory) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Course</th>
                <th>Title</th>
                <th>Status</th>
            </tr>


            <?php foreach ($courseHistory as $course): ?>

                <tr>

                    <td>
                        <?= e($course['course_ID']) ?>
                    </td>

                    <td>
                        <?= e($course['course_Name']) ?>
                    </td>

                    <td>

                        <?php if (!empty($course['Grade'])): ?>

                            <span class="badge">
                                Complete
                            </span>

                        <?php else: ?>

                            In Progress

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php else: ?>

    <div class="card">
        <p>No completed courses are currently available.</p>
    </div>

<?php endif; ?>


<?php

page_end();

?>