<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Admin']);

// Get all course sections
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Course_Section.section_No,
            Course_Section.available_Seats,
            Semester.semester_Name,
            User.first_Name,
            User.last_Name
        FROM Course_Section
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        JOIN User
            ON User.user_ID = Course_Section.faculty_ID
        ORDER BY
            Semester.semester_ID,
            Course.course_ID";

$sections = all_rows($pdo, $sql);

page_start(
    'Course Sections',
    'Admin',
    'sections.php'
);

?>

<div class="table-wrap">

    <table>

        <tr>
            <th>CRN</th>
            <th>Course</th>
            <th>Section</th>
            <th>Faculty</th>
            <th>Semester</th>
            <th>Available Seats</th>
        </tr>

        <?php foreach ($sections as $section): ?>

            <tr>

                <td>
                    <?= e($section['CRN']) ?>
                </td>

                <td>
                    <?= e($section['course_ID']) ?>
                    -
                    <?= e($section['course_Name']) ?>
                </td>

                <td>
                    <?= e($section['section_No']) ?>
                </td>

                <td>
                    <?= e(
                        $section['first_Name'] . ' ' .
                        $section['last_Name']
                    ) ?>
                </td>

                <td>
                    <?= e($section['semester_Name']) ?>
                </td>

                <td>
                    <?= e($section['available_Seats']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>

<?php

page_end();

?>