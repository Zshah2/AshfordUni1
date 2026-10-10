
<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Admin']);

// Get all departments
$departments = all_rows(
    $pdo,
    "SELECT dept_ID, dept_Name, Email, Phone_No
     FROM Department
     ORDER BY dept_Name"
);

// Get all courses
$courses = all_rows(
    $pdo,
    "SELECT course_ID, course_Name, course_Credits, course_Type
     FROM Course
     ORDER BY course_ID"
);

page_start(
    'Academic Management',
    'Admin',
    'academic.php'
);

?>

<style>
.academic-page {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.academic-summary {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.academic-summary .card {
    padding: 20px;
}

.academic-summary span {
    display: block;
    margin-bottom: 10px;
    font-size: 14px;
    opacity: 0.75;
}

.academic-summary strong {
    font-size: 30px;
}

.academic-panel {
    padding: 20px;
    min-width: 0;
}

.academic-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.academic-tab {
    padding: 11px 20px;
    border: 1px solid #d7dfeb;
    border-radius: 8px;
    background: #f1f4f9;
    color: #26344a;
    font-weight: 600;
    cursor: pointer;
}

.academic-tab.active {
    background: #173b70;
    color: white;
    border-color: #173b70;
}

.academic-section[hidden],
.academic-empty[hidden] {
    display: none;
}

.academic-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.academic-toolbar h2 {
    margin: 0;
}

.academic-search {
    width: 100%;
    max-width: 300px;
    padding: 11px 14px;
    border: 1px solid #d7dfeb;
    border-radius: 8px;
    background: white;
    color: #26344a;
    font-size: 14px;
}

.academic-table-container {
    overflow-x: auto;
}

.academic-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

.academic-table th,
.academic-table td {
    padding: 14px;
    border-bottom: 1px solid #e4e9f0;
    font-size: 14px;
}

.academic-table th {
    background: #f4f6fa;
    font-weight: 700;
}

.academic-table tbody tr:hover {
    background: #f8fafd;
}

.academic-table td:first-child {
    font-weight: 600;
}

.academic-empty {
    text-align: center;
    padding: 20px;
    opacity: 0.7;
}

@media (max-width: 650px) {
    .academic-summary {
        grid-template-columns: 1fr;
    }

    .academic-panel {
        padding: 14px;
    }
}
</style>

<div class="academic-page">

    <div class="academic-summary">

        <div class="card">
            <span>Total Departments</span>
            <strong>
                <?= e(count($departments)) ?>
            </strong>
        </div>

        <div class="card">
            <span>Total Courses</span>
            <strong>
                <?= e(count($courses)) ?>
            </strong>
        </div>

    </div>

    <div class="card academic-panel">

        <div class="academic-tabs">

            <button
                type="button"
                class="academic-tab active"
                data-target="departments"
            >
                Departments
            </button>

            <button
                type="button"
                class="academic-tab"
                data-target="courses"
            >
                Courses
            </button>

        </div>

        <!-- DEPARTMENTS -->

        <section
            id="departments"
            class="academic-section"
        >

            <div class="academic-toolbar">

                <h2>Departments</h2>

                <input
                    type="search"
                    id="departmentSearch"
                    class="academic-search"
                    placeholder="Search departments..."
                >

            </div>

            <div class="academic-table-container">

                <table
                    class="academic-table"
                    id="departmentTable"
                >

                    <thead>
                        <tr>
                            <th>Department ID</th>
                            <th>Department Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($departments as $department): ?>

                            <tr>
                                <td>
                                    <?= e($department['dept_ID']) ?>
                                </td>

                                <td>
                                    <?= e($department['dept_Name']) ?>
                                </td>

                                <td>
                                    <?= e($department['Email']) ?>
                                </td>

                                <td>
                                    <?= e($department['Phone_No']) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <p class="academic-empty" id="departmentEmpty" hidden>
                No matching departments found.
            </p>

        </section>

        <!-- COURSES -->

        <section
            id="courses"
            class="academic-section"
            hidden
        >

            <div class="academic-toolbar">

                <h2>Courses</h2>

                <input
                    type="search"
                    id="courseSearch"
                    class="academic-search"
                    placeholder="Search courses..."
                >

            </div>

            <div class="academic-table-container">

                <table
                    class="academic-table"
                    id="courseTable"
                >

                    <thead>
                        <tr>
                            <th>Course ID</th>
                            <th>Course Name</th>
                            <th>Credits</th>
                            <th>Course Type</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($courses as $course): ?>

                            <tr>
                                <td>
                                    <?= e($course['course_ID']) ?>
                                </td>

                                <td>
                                    <?= e($course['course_Name']) ?>
                                </td>

                                <td>
                                    <?= e($course['course_Credits']) ?>
                                </td>

                                <td>
                                    <?= e($course['course_Type']) ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

            <p class="academic-empty" id="courseEmpty" hidden>
                No matching courses found.
            </p>

        </section>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Switch between Departments and Courses
    const tabs = document.querySelectorAll('.academic-tab');
    const sections = document.querySelectorAll('.academic-section');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {

            const target = tab.dataset.target;

            tabs.forEach(function (button) {
                button.classList.toggle(
                    'active',
                    button === tab
                );
            });

            sections.forEach(function (section) {
                section.hidden = section.id !== target;
            });

        });
    });

    // Search table rows
    function enableSearch(inputId, tableId, emptyId) {

        const input = document.getElementById(inputId);
        const table = document.getElementById(tableId);
        const empty = document.getElementById(emptyId);

        input.addEventListener('input', function () {

            const search = input.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            let visible = 0;

            rows.forEach(function (row) {

                const matches = row.textContent
                    .toLowerCase()
                    .includes(search);

                row.hidden = !matches;
                row.style.display = matches ? '' : 'none';

                if (matches) {
                    visible++;
                }

            });

            empty.hidden = visible > 0;
        });
    }

    enableSearch(
        'departmentSearch',
        'departmentTable',
        'departmentEmpty'
    );

    enableSearch(
        'courseSearch',
        'courseTable',
        'courseEmpty'
    );

});
</script>

<?php

page_end();

?>
