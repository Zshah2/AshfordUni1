<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);

// Get the logged-in student's ID
$studentID = (int) $_SESSION['user_id'];


// Drop a course
if (isset($_POST['drop'])) {

    $crn = (int) $_POST['drop'];

    try {

        $pdo->beginTransaction();


        // Remove the student from the course
        $sql = "DELETE FROM Enrollment
                WHERE student_ID = ?
                AND CRN = ?";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $studentID,
            $crn
        ]);


        // Only return the seat if a course was actually dropped
        if ($stmt->rowCount() > 0) {

            $sql = "UPDATE Course_Section
                    SET available_Seats = available_Seats + 1
                    WHERE CRN = ?";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$crn]);

            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Course dropped.'
            ];

        } else {

            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Course could not be dropped.'
            ];
        }


        $pdo->commit();

    } catch (Throwable $error) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Course could not be dropped.'
        ];
    }


    header('Location: schedule.php');
    exit;
}


// Get all courses the student is registered for
$sql = "SELECT
            Course.course_ID,
            Course.course_Name,
            Course.course_Credits,
            Course_Section.CRN,
            Semester.semester_Name,
            User.first_Name,
            User.last_Name
        FROM Enrollment
        JOIN Course_Section
            ON Course_Section.CRN = Enrollment.CRN
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        JOIN User
            ON User.user_ID = Course_Section.faculty_ID
        WHERE Enrollment.student_ID = ?
        ORDER BY
            Semester.semester_ID,
            Course.course_ID";

$schedule = all_rows(
    $pdo,
    $sql,
    [$studentID]
);


page_start(
    'My Schedule',
    'Student',
    'schedule.php'
);

?>

<p class="muted">
    View your registered courses or drop a course.
</p>

<?php flash(); ?>


<?php if (count($schedule) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Course</th>
                <th>Course Name</th>
                <th>CRN</th>
                <th>Professor</th>
                <th>Credits</th>
                <th>Semester</th>
                <th>Action</th>
            </tr>


            <?php foreach ($schedule as $course): ?>

                <tr>

                    <td>
                        <?= e($course['course_ID']) ?>
                    </td>

                    <td>
                        <?= e($course['course_Name']) ?>
                    </td>

                    <td>
                        <?= e($course['CRN']) ?>
                    </td>

                    <td>
                        <?= e(
                            $course['first_Name'] . ' ' .
                            $course['last_Name']
                        ) ?>
                    </td>

                    <td>
                        <?= e($course['course_Credits']) ?>
                    </td>

                    <td>
                        <?= e($course['semester_Name']) ?>
                    </td>

                    <td>

                        <form method="post">

                            <button
                                type="submit"
                                class="btn danger small"
                                name="drop"
                                value="<?= e($course['CRN']) ?>"
                                data-confirm="Drop this course?"
                            >
                                Drop
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>

<?php else: ?>

    <div class="card">
        <p>You are not currently registered for any courses.</p>
    </div>

<?php endif; ?>


<?php

page_end();

?>