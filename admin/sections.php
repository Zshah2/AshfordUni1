
<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Admin']);

/*
|--------------------------------------------------------------------------
| COURSE SECTIONS
|--------------------------------------------------------------------------
| Retrieve course sections, faculty, semesters, and seat availability.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        cs.CRN,
        c.course_ID,
        c.course_Name,
        cs.section_No,
        cs.available_Seats,
        s.semester_Name,
        u.first_Name,
        u.last_Name
    FROM Course_Section AS cs
    JOIN Course AS c
        ON c.course_ID = cs.course_ID
    JOIN Semester AS s
        ON s.semester_ID = cs.semester_ID
    LEFT JOIN User AS u
        ON u.user_ID = cs.faculty_ID
    ORDER BY
        s.semester_ID,
        c.course_ID,
        cs.section_No
";

$sections = all_rows($pdo, $sql);

$totalSections = count($sections);

$totalAvailableSeats = array_sum(
    array_map(
        static fn($section) => (int) $section['available_Seats'],
        $sections
    )
);

page_start(
    'Course Sections',
    'Admin',
    'sections.php'
);

?>

<style>
.course-sections-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.course-sections-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.course-sections-summary .card {
    padding: 22px;
}

.course-sections-summary span {
    display: block;
    font-size: 14px;
    opacity: 0.75;
    margin-bottom: 10px;
}

.course-sections-summary strong {
    font-size: 30px;
}

.course-sections-panel {
    padding: 20px;
    min-width: 0;
}

.course-sections-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.course-sections-toolbar h2 {
    margin: 0;
}

.course-sections-search {
    width: 100%;
    max-width: 320px;
    padding: 11px 14px;
    border: 1px solid #d7dfeb;
    border-radius: 8px;
    background: #fff;
    color: #26344a;
    font-size: 14px;
}

.course-sections-table-wrap {
    overflow-x: auto;
}

.course-sections-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.course-sections-table th,
.course-sections-table td {
    padding: 14px 12px;
    border-bottom: 1px solid #e4e9f0;
    font-size: 14px;
}

.course-sections-table th {
    background: #f4f6fa;
    font-weight: 700;
    white-space: nowrap;
}

.course-sections-table tbody tr:hover {
    background: #f8fafd;
}

.course-sections-code {
    font-weight: 700;
    white-space: nowrap;
}

.course-sections-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    background: #e9f2ec;
    color: #276343;
    font-size: 12px;
    font-weight: 600;
}

.course-sections-badge.full {
    background: #fce8e8;
    color: #9d2929;
}

.course-sections-empty {
    padding: 24px;
    text-align: center;
    opacity: 0.7;
}

.course-sections-empty[hidden] {
    display: none;
}

@media (max-width: 650px) {
    .course-sections-summary {
        grid-template-columns: 1fr;
    }

    .course-sections-panel {
        padding: 14px;
    }
}
</style>

<div class="course-sections-page">

    <section class="course-sections-summary">

        <div class="card">
            <span>Total Course Sections</span>
            <strong><?= e($totalSections) ?></strong>
        </div>

        <div class="card">
            <span>Total Available Seats</span>
            <strong><?= e($totalAvailableSeats) ?></strong>
        </div>

    </section>

    <section class="card course-sections-panel">

        <div class="course-sections-toolbar">

            <h2>Course Sections</h2>

            <input
                type="search"
                id="sectionSearch"
                class="course-sections-search"
                placeholder="Search CRN, course, faculty, semester..."
                aria-label="Search course sections"
            >

        </div>

        <div class="course-sections-table-wrap">

            <table
                class="course-sections-table"
                id="sectionsTable"
            >

                <thead>
                    <tr>
                        <th>CRN</th>
                        <th>Course</th>
                        <th>Section</th>
                        <th>Faculty</th>
                        <th>Semester</th>
                        <th>Available Seats</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($sections as $section): ?>

                        <?php
                        $facultyName = trim(
                            ($section['first_Name'] ?? '') . ' ' .
                            ($section['last_Name'] ?? '')
                        );

                        if ($facultyName === '') {
                            $facultyName = 'Not Assigned';
                        }

                        $availableSeats = (int) $section['available_Seats'];
                        ?>

                        <tr>

                            <td class="course-sections-code">
                                <?= e($section['CRN']) ?>
                            </td>

                            <td>
                                <strong>
                                    <?= e($section['course_ID']) ?>
                                </strong>
                                -
                                <?= e($section['course_Name']) ?>
                            </td>

                            <td>
                                <?= e($section['section_No']) ?>
                            </td>

                            <td>
                                <?= e($facultyName) ?>
                            </td>

                            <td>
                                <?= e($section['semester_Name']) ?>
                            </td>

                            <td>
                                <span class="course-sections-badge <?= $availableSeats <= 0 ? 'full' : '' ?>">
                                    <?= e($availableSeats) ?>
                                    <?= $availableSeats <= 0 ? '(Full)' : 'Available' ?>
                                </span>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <p
            class="course-sections-empty"
            id="sectionsEmpty"
            <?= $totalSections > 0 ? 'hidden' : '' ?>
        >
            No matching course sections found.
        </p>

    </section>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const search = document.getElementById('sectionSearch');
    const table = document.getElementById('sectionsTable');
    const empty = document.getElementById('sectionsEmpty');

    search.addEventListener('input', function () {

        const query = search.value.toLowerCase().trim();
        const rows = table.querySelectorAll('tbody tr');

        let visibleCount = 0;

        rows.forEach(function (row) {

            const matches = row.textContent
                .toLowerCase()
                .includes(query);

            row.style.display = matches ? '' : 'none';

            if (matches) {
                visibleCount++;
            }

        });

        empty.hidden = visibleCount > 0;

    });

});
</script>

<?php

page_end();

?>
