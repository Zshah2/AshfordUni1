<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['StatStaff']);


// Get enrollment totals for each course section
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Semester.semester_Name,
            COUNT(Enrollment.student_ID) AS enrolled
        FROM Course_Section

        JOIN Course
            ON Course.course_ID = Course_Section.course_ID

        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID

        LEFT JOIN Enrollment
            ON Enrollment.CRN = Course_Section.CRN

        GROUP BY
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Semester.semester_Name

        ORDER BY
            Course.course_ID,
            Semester.semester_ID";

$courseReports = all_rows(
    $pdo,
    $sql
);


// Get anonymous grade totals by department and course
$sql = "SELECT
            Department.dept_Name,
            Course.course_ID,
            Course.course_Name,
            Enrollment.Grade,
            COUNT(*) AS grade_Total
        FROM Enrollment

        JOIN Course_Section
            ON Course_Section.CRN = Enrollment.CRN

        JOIN Course
            ON Course.course_ID = Course_Section.course_ID

        JOIN Department
            ON Department.dept_ID = Course.dept_ID

        WHERE Enrollment.Grade IS NOT NULL
        AND Enrollment.Grade <> ''

        GROUP BY
            Department.dept_Name,
            Course.course_ID,
            Course.course_Name,
            Enrollment.Grade

        ORDER BY
            Department.dept_Name,
            Course.course_ID,
            Enrollment.Grade";

$gradeReports = all_rows(
    $pdo,
    $sql
);


page_start(
    'Anonymous Reports',
    'StatStaff',
    'reports.php'
);

?>


<div class="card">

    <p>
        These reports contain summary academic information
        without displaying student names or student IDs.
    </p>

</div>


<h2>Course Enrollment Totals</h2>

<div class="table-wrap">

    <table>

        <tr>
            <th>CRN</th>
            <th>Course</th>
            <th>Title</th>
            <th>Semester</th>
            <th>Registered Students</th>
        </tr>


        <?php foreach ($courseReports as $course): ?>

            <tr>

                <td>
                    <?= e($course['CRN']) ?>
                </td>

                <td>
                    <?= e($course['course_ID']) ?>
                </td>

                <td>
                    <?= e($course['course_Name']) ?>
                </td>

                <td>
                    <?= e($course['semester_Name']) ?>
                </td>

                <td>
                    <?= e($course['enrolled']) ?>
                </td>

            </tr>

        <?php endforeach; ?>

    </table>

</div>


<h2>Grade Summary by Department and Course</h2>

<?php if (count($gradeReports) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Department</th>
                <th>Course</th>
                <th>Title</th>
                <th>Grade</th>
                <th>Number of Students</th>
            </tr>


            <?php foreach ($gradeReports as $grade): ?>

                <tr>

                    <td>
                        <?= e($grade['dept_Name']) ?>
                    </td>

                    <td>
                        <?= e($grade['course_ID']) ?>
                    </td>

                    <td>
                        <?= e($grade['course_Name']) ?>
                    </td>

                    <td>
                        <?= e($grade['Grade']) ?>
                    </td>

                    <td>
                        <?= e($grade['grade_Total']) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php else: ?>

    <div class="card">

        <p>
            No grade information is currently available.
        </p>

    </div>

<?php endif; ?>


<?php

page_end();

?>