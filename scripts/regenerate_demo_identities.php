<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$dryRun = getenv('IDENTITY_MIGRATION_DRY_RUN') !== '0';
$users = $pdo->query('SELECT user_ID, user_Type FROM User ORDER BY user_ID')->fetchAll(PDO::FETCH_ASSOC);
$studentMap = [];
$facultyMap = [];
$nextStudentId = 1000001;
$nextFacultyId = 2000001;

foreach ($users as $user) {
    $oldId = (int) $user['user_ID'];
    $role = $user['user_Type'];

    if ($role === 'Student') {
        $studentMap[$oldId] = $nextStudentId++;
    } elseif ($role === 'Faculty') {
        $facultyMap[$oldId] = $nextFacultyId++;
    }
}

$studentCount = count($studentMap);
$facultyCount = count($facultyMap);
if ($studentCount !== 1601 || $facultyCount !== 350) {
    fwrite(STDERR, "Unexpected identity counts; migration was not applied.\n");
    exit(1);
}

$allNewIds = array_merge(array_values($studentMap), array_values($facultyMap));
$allOldIds = array_merge(array_keys($studentMap), array_keys($facultyMap));
if (count(array_intersect($allOldIds, $allNewIds)) !== 0 || count($allNewIds) !== count(array_unique($allNewIds))) {
    fwrite(STDERR, "New IDs collide with existing IDs; migration was not applied.\n");
    exit(1);
}

$references = $pdo->query(
    "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = DATABASE()
       AND REFERENCED_TABLE_NAME IN ('Student', 'Faculty')
       AND TABLE_NAME NOT IN ('Student', 'Faculty')
     ORDER BY TABLE_NAME, COLUMN_NAME"
)->fetchAll(PDO::FETCH_ASSOC);

if ($dryRun) {
    echo "Planned student IDs: 1000001-" . ($nextStudentId - 1) . "\n";
    echo "Planned faculty IDs: 2000001-" . ($nextFacultyId - 1) . "\n";
    echo "Dependent references: " . count($references) . "\n";
    exit(0);
}

$pdo->query('SET FOREIGN_KEY_CHECKS = 0');
$pdo->beginTransaction();

foreach ($references as $reference) {
    $table = $reference['TABLE_NAME'];
    $column = $reference['COLUMN_NAME'];
    $referencedTable = $reference['REFERENCED_TABLE_NAME'];
    $mapping = $referencedTable === 'Student' ? $studentMap : $facultyMap;
    $statement = $pdo->prepare("UPDATE `$table` SET `$column` = ? WHERE `$column` = ?");

    foreach ($mapping as $oldId => $newId) {
        $statement->execute([$newId, $oldId]);
    }
}

foreach ($studentMap as $oldId => $newId) {
    $pdo->prepare('UPDATE Student SET student_ID = ? WHERE student_ID = ?')->execute([$newId, $oldId]);
}

foreach ($facultyMap as $oldId => $newId) {
    $pdo->prepare('UPDATE Faculty SET faculty_ID = ? WHERE faculty_ID = ?')->execute([$newId, $oldId]);
}

foreach ($studentMap as $oldId => $newId) {
    $pdo->prepare('UPDATE Login SET user_ID = ? WHERE user_ID = ?')->execute([$newId, $oldId]);
}

foreach ($facultyMap as $oldId => $newId) {
    $pdo->prepare('UPDATE Login SET user_ID = ? WHERE user_ID = ?')->execute([$newId, $oldId]);
}

foreach ($studentMap as $oldId => $newId) {
    $pdo->prepare('UPDATE User SET user_ID = ? WHERE user_ID = ?')->execute([$newId, $oldId]);
    $pdo->prepare('UPDATE user_update_log SET user_ID = ?, changed_By_User_ID = ? WHERE user_ID = ? OR changed_By_User_ID = ?')->execute([$newId, $newId, $oldId, $oldId]);
}

foreach ($facultyMap as $oldId => $newId) {
    $pdo->prepare('UPDATE User SET user_ID = ? WHERE user_ID = ?')->execute([$newId, $oldId]);
    $pdo->prepare('UPDATE user_update_log SET user_ID = ?, changed_By_User_ID = ? WHERE user_ID = ? OR changed_By_User_ID = ?')->execute([$newId, $newId, $oldId, $oldId]);
}

$pdo->commit();
$pdo->query('SET FOREIGN_KEY_CHECKS = 1');

$brokenStudents = (int) $pdo->query('SELECT COUNT(*) FROM Student s LEFT JOIN User u ON u.user_ID = s.student_ID WHERE u.user_ID IS NULL')->fetchColumn();
$brokenFaculty = (int) $pdo->query('SELECT COUNT(*) FROM Faculty f LEFT JOIN User u ON u.user_ID = f.faculty_ID WHERE u.user_ID IS NULL')->fetchColumn();
$oldStudentReferences = (int) $pdo->query('SELECT COUNT(*) FROM Student WHERE student_ID BETWEEN 1 AND 1600')->fetchColumn();
$oldFacultyReferences = (int) $pdo->query('SELECT COUNT(*) FROM Faculty WHERE faculty_ID BETWEEN 1 AND 350')->fetchColumn();

if ($brokenStudents !== 0 || $brokenFaculty !== 0 || $oldStudentReferences !== 0 || $oldFacultyReferences !== 0) {
    fwrite(STDERR, "Post-migration validation failed.\n");
    exit(1);
}

echo "Identity IDs changed successfully.\n";
echo "Students: $studentCount\nFaculty: $facultyCount\nBroken student links: $brokenStudents\nBroken faculty links: $brokenFaculty\n";
