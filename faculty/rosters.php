<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Faculty']);

// Get the logged-in faculty member
$facultyID = (int) $_SESSION['user_id'];


// Get the courses taught by this faculty member
$sql = "SELECT
            Course_Section.CRN,
            Course.course_ID,
            Course.course_Name
        FROM Course_Section
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        WHERE Course_Section.faculty_ID = ?
        ORDER BY Course.course_ID";

$sections = all_rows(
    $pdo,
    $sql,
    [$facultyID]
);


// Use the selected course or the first course
$crn = (int) (
    $_GET['crn'] ??
    ($sections[0]['CRN'] ?? 0)
);


// Save grade and attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $studentID = (int) ($_POST['student_id'] ?? 0);
    $grade = trim($_POST['grade'] ?? '');
    $attendance = $_POST['attendance'] ?? 'Present';
    $today = date('Y-m-d');


    // Make sure the course belongs to this faculty member
    $courseSection = one(
        $pdo,
        "SELECT course_ID
         FROM Course_Section
         WHERE CRN = ?
         AND faculty_ID = ?",
        [$crn, $facultyID]
    );


    if ($courseSection) {

        // Update grade
        if ($grade !== '') {

            $sql = "UPDATE Enrollment
                    SET Grade = ?
                    WHERE student_ID = ?
                    AND CRN = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $grade,
                $studentID,
                $crn
            ]);
        }


        // Save today's attendance
        $sql = "INSERT INTO Attendance
                    (
                        student_ID,
                        CRN,
                        course_ID,
                        attendance_Date,
                        Present_Absent
                    )
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    Present_Absent = VALUES(Present_Absent)";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $studentID,
            $crn,
            $courseSection['course_ID'],
            $today,
            $attendance
        ]);


        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Student record updated.'
        ];
    }


    header("Location: rosters.php?crn=$crn");
    exit;
}


// Get students and today's saved attendance
if ($crn > 0) {

    $today = date('Y-m-d');

    $sql = "SELECT
                Enrollment.student_ID,
                User.first_Name,
                User.last_Name,
                Enrollment.Grade,
                Attendance.Present_Absent
            FROM Enrollment

            JOIN User
                ON User.user_ID = Enrollment.student_ID

            JOIN Course_Section
                ON Course_Section.CRN = Enrollment.CRN

            LEFT JOIN Attendance
                ON Attendance.student_ID = Enrollment.student_ID
                AND Attendance.CRN = Enrollment.CRN
                AND Attendance.attendance_Date = ?

            WHERE Enrollment.CRN = ?
            AND Course_Section.faculty_ID = ?

            ORDER BY
                User.last_Name,
                User.first_Name";

    $roster = all_rows(
        $pdo,
        $sql,
        [$today, $crn, $facultyID]
    );

} else {

    $roster = [];
}


page_start(
    'Course Rosters',
    'Faculty',
    'rosters.php'
);

flash();

?>


<div class="card">

    <form method="get">

        <label for="crn">
            <strong>Select Course</strong>
        </label>

        <select
            name="crn"
            id="crn"
            onchange="this.form.submit()"
        >

            <?php foreach ($sections as $section): ?>

                <option
                    value="<?= e($section['CRN']) ?>"
                    <?= $crn === (int) $section['CRN']
                        ? 'selected'
                        : '' ?>
                >

                    <?= e($section['course_ID']) ?>
                    -
                    <?= e($section['course_Name']) ?>
                    (CRN <?= e($section['CRN']) ?>)

                </option>

            <?php endforeach; ?>

        </select>

    </form>

</div>


<?php if (count($roster) > 0): ?>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Student</th>
                <th>Grade</th>
                <th>Attendance Today</th>
                <th>Action</th>
            </tr>


            <?php foreach ($roster as $student): ?>

                <?php
                $savedAttendance =
                    $student['Present_Absent'] ?? 'Present';
                ?>

                <tr>

                    <td>

                        <?= e($student['student_ID']) ?>
                        -
                        <?= e(
                            $student['first_Name'] . ' ' .
                            $student['last_Name']
                        ) ?>

                    </td>


                    <td>

                        <form
                            method="post"
                            id="student-<?= e($student['student_ID']) ?>"
                        >

                            <input
                                type="hidden"
                                name="student_id"
                                value="<?= e($student['student_ID']) ?>"
                            >

                            <input
                                type="text"
                                name="grade"
                                value="<?= e($student['Grade'] ?? '') ?>"
                                style="width: 90px;"
                            >

                    </td>


                    <td>

                            <select name="attendance">

                                <option
                                    value="Present"
                                    <?= $savedAttendance === 'Present'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Present
                                </option>

                                <option
                                    value="Absent"
                                    <?= $savedAttendance === 'Absent'
                                        ? 'selected'
                                        : '' ?>
                                >
                                    Absent
                                </option>

                            </select>

                    </td>


                    <td>

                            <button
                                type="submit"
                                class="btn small"
                            >
                                Save
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>

    </div>


<?php else: ?>

    <div class="card">

        <p>
            No students are currently enrolled in this course.
        </p>

    </div>

<?php endif; ?>


<?php

page_end();

?>