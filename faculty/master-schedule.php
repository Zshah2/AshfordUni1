<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student', 'Faculty', 'Admin', 'StatStaff']);

$searchTerm = trim($_GET['search'] ?? '');
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
$searchCondition = '';
$searchParameters = [];

if ($searchTerm !== '') {
    $searchCondition = "
        AND (
            CONCAT(Course_Section.CRN) LIKE ?
            OR " . $courseKeyExpression . " LIKE ?
            OR Department.dept_Name LIKE ?
            OR Course.course_Name LIKE ?
            OR CONCAT(User.first_Name, ' ', User.last_Name) LIKE ?
            OR Semester.semester_Name LIKE ?
        )
    ";
    $searchValue = '%' . $searchTerm . '%';
    $searchParameters = [
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    ];
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
    WHERE 1 = 1
    " . $searchCondition;

$totalSections = (int) one($pdo, $countSql, $searchParameters)['COUNT(*)'];
$totalPages = max(1, (int) ceil($totalSections / $pageSize));
$currentPage = min($currentPage, $totalPages);
$offset = ($currentPage - 1) * $pageSize;

// Get the requested page of course sections for the master schedule.
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
        WHERE 1 = 1
        " . $searchCondition . "
        ORDER BY
            Semester.semester_ID,
            Course.course_ID
        LIMIT ? OFFSET ?";

$sections = all_rows(
    $pdo,
    $sql,
    array_merge($searchParameters, [$pageSize, $offset])
);


page_start(
    'Master Schedule',
    $_SESSION['user_type'] ?? 'Faculty',
    'master-schedule.php'
);

?>

<form class="card" method="get" action="master-schedule.php">

    <label for="scheduleSearch">
        <strong>Search Schedule</strong>
    </label>

    <input
        type="text"
        id="scheduleSearch"
        name="search"
        value="<?= e($searchTerm) ?>"
        placeholder="Search by CRN, course, faculty, or semester"
    >

    <button class="btn" type="submit">Search</button>

</form>


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

<?php if ($totalPages > 1): ?>

    <nav class="pagination" aria-label="Master schedule pages">

        <?php if ($currentPage > 1): ?>

            <a
                class="btn small"
                href="?page=<?= $currentPage - 1 ?><?= $searchTerm !== '' ? '&search=' . urlencode($searchTerm) : '' ?>"
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
                href="?page=<?= $currentPage + 1 ?><?= $searchTerm !== '' ? '&search=' . urlencode($searchTerm) : '' ?>"
            >
                Next
            </a>

        <?php endif; ?>

    </nav>

<?php endif; ?>


<?php

page_end();

?>