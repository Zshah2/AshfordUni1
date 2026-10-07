<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);


// Get all courses and their departments with a mnemonic course key.
$sql = "SELECT
            Course.course_ID,
            CASE Department.dept_Name
                WHEN 'Computer Science' THEN CONCAT('CS', Course.course_ID)
                WHEN 'Business' THEN CONCAT('BUS', Course.course_ID)
                WHEN 'Mathematics' THEN CONCAT('MA', Course.course_ID)
                WHEN 'Natural Sciences' THEN CONCAT('NS', Course.course_ID)
                WHEN 'Engineering' THEN CONCAT('ENG', Course.course_ID)
                WHEN 'English' THEN CONCAT('ENGL', Course.course_ID)
                WHEN 'Social Sciences' THEN CONCAT('SS', Course.course_ID)
                WHEN 'Education' THEN CONCAT('ED', Course.course_ID)
                WHEN 'Health Sciences' THEN CONCAT('HS', Course.course_ID)
                WHEN 'Arts and Media' THEN CONCAT('ART', Course.course_ID)
                ELSE CONCAT(Department.dept_Name, Course.course_ID)
            END AS course_Key,
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
                    <?= e($course['course_Key']) ?>
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