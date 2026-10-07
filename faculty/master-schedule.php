<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student', 'Faculty', 'Admin', 'StatStaff']);

// Get all course sections for the master schedule
$sql = "SELECT
            Course_Section.CRN,
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
            Course_Section.section_No,
            Semester.semester_Name,
            Course_Section.available_Seats,
            User.first_Name,
            User.last_Name
        FROM Course_Section
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Department
            ON Department.dept_ID = Course.dept_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        JOIN User
            ON User.user_ID = Course_Section.faculty_ID
        ORDER BY
            Semester.semester_ID,
            Course.course_ID";

$sections = all_rows($pdo, $sql);


page_start(
    'Master Schedule',
    $_SESSION['user_type'] ?? 'Faculty',
    'master-schedule.php'
);

?>

<div class="card">

    <label for="scheduleSearch">
        <strong>Search Schedule</strong>
    </label>

    <input
        type="text"
        id="scheduleSearch"
        placeholder="Search by course, faculty, or semester"
    >

</div>


<div class="table-wrap">

    <table id="scheduleTable">

        <thead>
            <tr>
                <th>CRN</th>
                <th>Course</th>
                <th>Title</th>
                <th>Section</th>
                <th>Faculty</th>
                <th>Semester</th>
                <th>Available Seats</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($sections as $section): ?>

                <tr>

                    <td>
                        <?= e($section['CRN']) ?>
                    </td>

                    <td>
                        <?= e($section['course_Key']) ?>
                    </td>

                    <td>
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

        </tbody>

    </table>

</div>


<script>

const searchBox = document.getElementById('scheduleSearch');
const scheduleRows = document.querySelectorAll(
    '#scheduleTable tbody tr'
);

searchBox.addEventListener('input', function () {

    const searchText = searchBox.value.toLowerCase();

    scheduleRows.forEach(function (row) {

        const rowText = row.innerText.toLowerCase();

        if (rowText.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }

    });

});

</script>


<?php

page_end();

?>