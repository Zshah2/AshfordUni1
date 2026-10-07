<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get all course sections for the master schedule
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Course_Section.section_No,
            Semester.semester_Name,
            Course_Section.available_Seats,
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
    'Master Schedule',
    'Faculty',
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
                        <?= e($section['course_ID']) ?>
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