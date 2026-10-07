<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$dryRun = getenv('CRN_MIGRATION_DRY_RUN') !== '0';
$sections = $pdo->query('SELECT CRN FROM Course_Section ORDER BY CRN')->fetchAll(PDO::FETCH_COLUMN);

if (count($sections) !== 2533) {
    fwrite(STDERR, "Expected 2533 course sections; migration was not applied.\n");
    exit(1);
}

$crnMap = [];
$nextCrn = 1000001;
foreach ($sections as $oldCrn) {
    $crnMap[(int) $oldCrn] = $nextCrn++;
}

$existingCrns = $pdo->query('SELECT CRN FROM Course_Section')->fetchAll(PDO::FETCH_COLUMN);
$collision = array_intersect($existingCrns, array_values($crnMap));
if ($collision !== []) {
    fwrite(STDERR, "New CRNs collide with existing CRNs; migration was not applied.\n");
    exit(1);
}

if ($dryRun) {
    echo "Planned CRN range: 1000001-" . ($nextCrn - 1) . "\n";
    echo "Course sections to update: " . count($crnMap) . "\n";
    exit(0);
}

$pdo->query('SET FOREIGN_KEY_CHECKS = 0');
$pdo->beginTransaction();

$dependentTables = [
    'Attendance' => 'CRN',
    'Enrollment' => 'CRN',
    'Faculty_History' => 'CRN',
    'Student_History' => 'CRN',
];

foreach ($dependentTables as $table => $column) {
    $statement = $pdo->prepare("UPDATE `$table` SET `$column` = ? WHERE `$column` = ?");
    foreach ($crnMap as $oldCrn => $newCrn) {
        $statement->execute([$newCrn, $oldCrn]);
    }
}

$statement = $pdo->prepare('UPDATE Course_Section SET CRN = ? WHERE CRN = ?');
foreach ($crnMap as $oldCrn => $newCrn) {
    $statement->execute([$newCrn, $oldCrn]);
}

$pdo->commit();
$pdo->query('SET FOREIGN_KEY_CHECKS = 1');

$oldCrnCount = (int) $pdo->query('SELECT COUNT(*) FROM Course_Section WHERE CRN BETWEEN 10001 AND 140024')->fetchColumn();
$newCrnCount = (int) $pdo->query('SELECT COUNT(*) FROM Course_Section WHERE CRN BETWEEN 1000001 AND 1002533')->fetchColumn();
$orphanedAttendances = (int) $pdo->query('SELECT COUNT(*) FROM Attendance a LEFT JOIN Course_Section c ON c.CRN = a.CRN WHERE c.CRN IS NULL')->fetchColumn();
$orphanedEnrollments = (int) $pdo->query('SELECT COUNT(*) FROM Enrollment e LEFT JOIN Course_Section c ON c.CRN = e.CRN WHERE c.CRN IS NULL')->fetchColumn();
$orphanedFacultyHistory = (int) $pdo->query('SELECT COUNT(*) FROM Faculty_History f LEFT JOIN Course_Section c ON c.CRN = f.CRN WHERE c.CRN IS NULL')->fetchColumn();
$orphanedStudentHistory = (int) $pdo->query('SELECT COUNT(*) FROM Student_History s LEFT JOIN Course_Section c ON c.CRN = s.CRN WHERE c.CRN IS NULL')->fetchColumn();

if ($oldCrnCount !== 0 || $newCrnCount !== 2533 || $orphanedAttendances !== 0 || $orphanedEnrollments !== 0 || $orphanedFacultyHistory !== 0 || $orphanedStudentHistory !== 0) {
    fwrite(STDERR, "CRN migration validation failed.\n");
    exit(1);
}

echo "CRN regeneration completed successfully.\n";
echo "Course sections: $newCrnCount\n";
echo "Old CRNs remaining: $oldCrnCount\n";
