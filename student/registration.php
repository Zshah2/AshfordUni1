<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Process a course registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $crn = (int) ($_POST['crn'] ?? 0);

    // Check if the student is allowed to register
    [$canRegister, $message] = registration_check(
        $pdo,
        $studentID,
        $crn
    );

    if ($canRegister) {

        // Get the semester and available seats
        $section = one(
            $pdo,
            "SELECT semester_ID, available_Seats
             FROM Course_Section
             WHERE CRN = ?",
            [$crn]
        );

        if (!$section || (int) $section['available_Seats'] <= 0) {

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'This course section is full.'
            ];

        } else {

            try {

                $pdo->beginTransaction();

                // Add the student to the course
                $sql = "INSERT INTO Enrollment
                            (student_ID, CRN, semester_ID)
                        VALUES (?, ?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $studentID,
                    $crn,
                    $section['semester_ID']
                ]);

                // Reduce the number of available seats
                $sql = "UPDATE Course_Section
                        SET available_Seats = available_Seats - 1
                        WHERE CRN = ?
                        AND available_Seats > 0";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([$crn]);

                $pdo->commit();

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Course registration successful.'
                ];

            } catch (Throwable $error) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Registration could not be completed.'
                ];
            }
        }

    } else {

        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => $message
        ];
    }

    header('Location: registration.php');
    exit;
}


// Get Fall 2026 course sections only
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name,
            Course_Section.section_No,
            Course.course_Credits,
            Department.dept_Name,
            User.first_Name,
            User.last_Name,
            Semester.semester_Name,
            Course_Section.available_Seats
        FROM Course_Section
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Department
            ON Department.dept_ID = Course.dept_ID
        JOIN User
            ON User.user_ID = Course_Section.faculty_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        WHERE Semester.semester_Name = 'Fall 2026'
        ORDER BY
            Course.course_ID,
            Course_Section.section_No";

$sections = all_rows(
    $pdo,
    $sql
);


page_start(
    'Course Registration',
    'Student',
    'registration.php'
);

?>

<p class="muted">
    Search Fall 2026 course sections and register for a course.
</p>

<?php flash(); ?>


<div class="card">

    <label for="tableSearch">
        <strong>Search Courses</strong>
    </label>

    <input
        type="text"
        id="tableSearch"
        placeholder="Search by course, instructor, or department"
    >

</div>


<div class="table-wrap">

    <table id="dataTable">

        <thead>

            <tr>
                <th>CRN</th>
                <th>Course</th>
                <th>Course Name</th>
                <th>Section</th>
                <th>Credits</th>
                <th>Department</th>
                <th>Instructor</th>
                <th>Semester</th>
                <th>Available Seats</th>
                <th>Action</th>
            </tr>

        </thead>

        <tbody>

            <?php foreach ($sections as $section): ?>

                <tr>

                    <td><?= e($section['CRN']) ?></td>

                    <td><?= e($section['course_ID']) ?></td>

                    <td><?= e($section['course_Name']) ?></td>

                    <td><?= e($section['section_No']) ?></td>

                    <td><?= e($section['course_Credits']) ?></td>

                    <td><?= e($section['dept_Name']) ?></td>

                    <td>
                        <?= e(
                            $section['first_Name'] . ' ' .
                            $section['last_Name']
                        ) ?>
                    </td>

                    <td><?= e($section['semester_Name']) ?></td>

                    <td><?= e($section['available_Seats']) ?></td>

                    <td>

                        <?php if ((int) $section['available_Seats'] > 0): ?>

                            <form method="post">

                                <input
                                    type="hidden"
                                    name="crn"
                                    value="<?= e($section['CRN']) ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn small"
                                >
                                    Register
                                </button>

                            </form>

                        <?php else: ?>

                            Full

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </tbody>

    </table>

</div>


<script>

const searchBox = document.getElementById('tableSearch');

const courseRows = document.querySelectorAll(
    '#dataTable tbody tr'
);

searchBox.addEventListener('input', function () {

    const searchText = searchBox.value.toLowerCase();

    courseRows.forEach(function (row) {

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