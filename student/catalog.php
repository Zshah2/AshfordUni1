<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);


// Get all courses and their departments
$sql = "SELECT
            Course.course_ID,
            Course.course_Name,
            Course.course_Credits,
            Course.course_Type,
            Department.dept_Name
        FROM Course
        JOIN Department
            ON Department.dept_ID = Course.dept_ID
        ORDER BY
            Department.dept_Name,
            Course.course_ID";

$courses = all_rows(
    $pdo,
    $sql
);


page_start(
    'University Catalog',
    'Student',
    'catalog.php'
);

?>

<p class="muted">
    View university courses, departments, credits, and academic levels.
</p>


<div class="table-wrap">

    <table>

        <tr>
            <th>Course</th>
            <th>Title</th>
            <th>Department</th>
            <th>Credits</th>
            <th>Level</th>
        </tr>


        <?php foreach ($courses as $course): ?>

            <tr>

                <td>
                    <?= e($course['course_ID']) ?>
                </td>

                <td>
                    <?= e($course['course_Name']) ?>
                </td>

                <td>
                    <?= e($course['dept_Name']) ?>
                </td>

                <td>
                    <?= e($course['course_Credits']) ?>
                </td>

                <td>
                    <?= e($course['course_Type']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>


<?php

page_end();

?>