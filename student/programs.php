<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);


// Get all majors and their departments
$sql = "SELECT
            Major.major_Name,
            Department.dept_Name
        FROM Major
        JOIN Department
            ON Department.dept_ID = Major.dept_ID
        ORDER BY Major.major_Name";

$majors = all_rows(
    $pdo,
    $sql
);


// Get all minors and their departments
$sql = "SELECT
            Minor.minor_Name,
            Department.dept_Name
        FROM Minor
        JOIN Department
            ON Department.dept_ID = Minor.dept_ID
        ORDER BY Minor.minor_Name";

$minors = all_rows(
    $pdo,
    $sql
);


page_start(
    'Majors & Minors',
    'Student',
    'programs.php'
);

?>

<div class="grid2">

    <div class="card">

        <h2>Majors</h2>

        <?php foreach ($majors as $major): ?>

            <p>

                <strong>
                    <?= e($major['major_Name']) ?>
                </strong>

                <br>

                <span class="muted">
                    <?= e($major['dept_Name']) ?>
                </span>

            </p>

        <?php endforeach; ?>

    </div>


    <div class="card">

        <h2>Minors</h2>

        <?php foreach ($minors as $minor): ?>

            <p>

                <strong>
                    <?= e($minor['minor_Name']) ?>
                </strong>

                <br>

                <span class="muted">
                    <?= e($minor['dept_Name']) ?>
                </span>

            </p>

        <?php endforeach; ?>

    </div>

</div>


<?php

page_end();

?>