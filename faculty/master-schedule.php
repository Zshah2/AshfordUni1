<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student', 'Faculty', 'Admin', 'StatStaff']);

$searchTerm = trim($_GET['search'] ?? '');
$departmentId = filter_var($_GET['department'] ?? '', FILTER_VALIDATE_INT);
$semesterId = filter_var($_GET['semester'] ?? '', FILTER_VALIDATE_INT);
$pageSize = 25;
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$courseKeyExpression = "
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
    END";
$filterCondition = '';
$filterParameters = [];

if ($departmentId !== false && $departmentId !== null) {
    $filterCondition .= ' AND Department.dept_ID = ?';
    $filterParameters[] = $departmentId;
}

if ($semesterId !== false && $semesterId !== null) {
    $filterCondition .= ' AND Semester.semester_ID = ?';
    $filterParameters[] = $semesterId;
}

if ($searchTerm !== '') {
    $filterCondition .= "
        AND (
            CONCAT(Course_Section.CRN) LIKE ?
            OR " . $courseKeyExpression . " LIKE ?
            OR Department.dept_Name LIKE ?
            OR Course.course_Name LIKE ?
            OR CONCAT(User.first_Name, ' ', User.last_Name) LIKE ?
            OR Student_Enrollment.enrolled_Students LIKE ?
            OR Semester.semester_Name LIKE ?
        )";
    $searchValue = '%' . $searchTerm . '%';
    $filterParameters = array_merge($filterParameters, [
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    ]);
}

// Count matching sections before selecting the requested page.
$countSql = "
    SELECT COUNT(*)
    FROM Course_Section
    JOIN Course
        ON Course.course_ID = Course_Section.course_ID
    JOIN Department
        ON Department.dept_ID = Course.dept_ID
    JOIN Semester
        ON Semester.semester_ID = Course_Section.semester_ID
    JOIN User
        ON User.user_ID = Course_Section.faculty_ID
    LEFT JOIN (
        SELECT
            Enrollment.CRN,
            Enrollment.semester_ID,
            GROUP_CONCAT(
                CONCAT(StudentUser.first_Name, ' ', StudentUser.last_Name)
                SEPARATOR ', '
            ) AS enrolled_Students
        FROM Enrollment
        JOIN Student
            ON Student.student_ID = Enrollment.student_ID
        JOIN User AS StudentUser
            ON StudentUser.user_ID = Student.student_ID
        GROUP BY
            Enrollment.CRN,
            Enrollment.semester_ID
    ) AS Student_Enrollment
        ON Student_Enrollment.CRN = Course_Section.CRN
        AND Student_Enrollment.semester_ID = Course_Section.semester_ID
    WHERE 1 = 1
    " . $filterCondition;

$totalSections = (int) one($pdo, $countSql, $filterParameters)['COUNT(*)'];
$totalPages = max(1, (int) ceil($totalSections / $pageSize));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $pageSize;

// Get the requested page of course sections with their enrolled students.
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
            Course_Section.semester_ID,
            Semester.semester_Name,
            Course_Section.available_Seats,
            User.first_Name,
            User.last_Name,
            StudentUser.first_Name AS student_First_Name,
            StudentUser.last_Name AS student_Last_Name
        FROM Course_Section
        JOIN Course
            ON Course.course_ID = Course_Section.course_ID
        JOIN Department
            ON Department.dept_ID = Course.dept_ID
        JOIN Semester
            ON Semester.semester_ID = Course_Section.semester_ID
        JOIN User
            ON User.user_ID = Course_Section.faculty_ID
        LEFT JOIN Enrollment
            ON Enrollment.CRN = Course_Section.CRN
            AND Enrollment.semester_ID = Course_Section.semester_ID
        LEFT JOIN Student
            ON Student.student_ID = Enrollment.student_ID
        LEFT JOIN User AS StudentUser
            ON StudentUser.user_ID = Student.student_ID
        WHERE 1 = 1
        " . $filterCondition . "
        ORDER BY
            Semester.semester_ID,
            Course.course_ID,
            StudentUser.last_Name,
            StudentUser.first_Name
        LIMIT ? OFFSET ?";

$sectionRows = all_rows(
    $pdo,
    $sql,
    array_merge($filterParameters, [$pageSize, $offset])
);

$sections = [];
foreach ($sectionRows as $row) {
    $key = $row['CRN'] . '|' . $row['semester_ID'];
    if (!isset($sections[$key])) {
        $sections[$key] = [
            'CRN' => $row['CRN'],
            'course_Key' => $row['course_Key'],
            'course_Name' => $row['course_Name'],
            'section_No' => $row['section_No'],
            'semester_Name' => $row['semester_Name'],
            'available_Seats' => $row['available_Seats'],
            'first_Name' => $row['first_Name'],
            'last_Name' => $row['last_Name'],
            'students' => []
        ];
    }

    if ($row['student_First_Name'] !== null && $row['student_Last_Name'] !== null) {
        $sections[$key]['students'][] = [
            'first_Name' => $row['student_First_Name'],
            'last_Name' => $row['student_Last_Name']
        ];
    }
}

$sections = array_values($sections);
$maxStudentColumns = max(0, ...array_map(fn ($section) => count($section['students']), $sections));


page_start(
    'Master Schedule',
    $_SESSION['user_type'] ?? 'Faculty',
    'master-schedule.php'
);

?>

<form class="card schedule-filter" method="get" action="master-schedule.php">

    <div class="schedule-filter-search">
        <label for="scheduleSearch">
            <strong>Search Schedule</strong>
        </label>

        <input
            type="search"
            id="scheduleSearch"
            name="search"
            value="<?= e($searchTerm) ?>"
            placeholder="Search by CRN, course, faculty, student, or semester"
        >
    </div>

    <div class="schedule-filter-controls">
        <label>
            <strong>Department</strong>
            <select name="department">
                <option value="">All departments</option>
                <?php foreach (all_rows($pdo, 'SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name') as $department): ?>
                    <option value="<?= (int) $department['dept_ID'] ?>" <?= (int) $departmentId === (int) $department['dept_ID'] ? 'selected' : '' ?>>
                        <?= e($department['dept_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <label>
            <strong>Semester</strong>
            <select name="semester">
                <option value="">All semesters</option>
                <?php foreach (all_rows($pdo, 'SELECT semester_ID, semester_Name FROM Semester ORDER BY semester_ID DESC') as $semester): ?>
                    <option value="<?= (int) $semester['semester_ID'] ?>" <?= (int) $semesterId === (int) $semester['semester_ID'] ? 'selected' : '' ?>>
                        <?= e($semester['semester_Name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>

        <button class="btn" type="submit">Apply filters</button>
    </div>

</form>


<div class="table-wrap">

    <table id="scheduleTable" style="min-width: <?= 900 + ($maxStudentColumns * 150) ?>px;">

        <thead>
            <tr>
                <th>CRN</th>
                <th>Course</th>
                <th>Title</th>
                <th>Section</th>
                <th>Faculty</th>
                <?php for ($studentIndex = 1; $studentIndex <= $maxStudentColumns; $studentIndex++): ?>
                    <th>Student <?= $studentIndex ?></th>
                <?php endfor; ?>
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

                    <?php for ($studentIndex = 0; $studentIndex < $maxStudentColumns; $studentIndex++): ?>
                        <td>
                            <?php if (isset($section['students'][$studentIndex])): ?>
                                <?= e($section['students'][$studentIndex]['first_Name'] . ' ' . $section['students'][$studentIndex]['last_Name']) ?>
                            <?php else: ?>
                                <span class="student-empty">—</span>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>

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

<?php if ($totalPages > 1): ?>

    <nav class="pagination" aria-label="Master schedule pages">

        <?php if ($currentPage > 1): ?>

            <a
                class="btn small"
                href="?page=<?= $currentPage - 1 ?><?= $searchTerm !== '' ? '&search=' . urlencode($searchTerm) : '' ?><?= $departmentId !== null && $departmentId !== false ? '&department=' . $departmentId : '' ?><?= $semesterId !== null && $semesterId !== false ? '&semester=' . $semesterId : '' ?>"
            >
                Previous
            </a>

        <?php endif; ?>

        <span>
            Page <?= $currentPage ?> of <?= $totalPages ?>
        </span>

        <?php if ($currentPage < $totalPages): ?>

            <a
                class="btn small"
                href="?page=<?= $currentPage + 1 ?><?= $searchTerm !== '' ? '&search=' . urlencode($searchTerm) : '' ?><?= $departmentId !== null && $departmentId !== false ? '&department=' . $departmentId : '' ?><?= $semesterId !== null && $semesterId !== false ? '&semester=' . $semesterId : '' ?>"
            >
                Next
            </a>

        <?php endif; ?>

    </nav>

<?php endif; ?>


<?php

page_end();

?>