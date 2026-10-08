<?php

// Get one row from the database
function one(
    PDO $pdo,
    string $sql,
    array $params = []
): ?array {

    $stmt = $pdo->prepare($sql);
    bind_params($stmt, $params);
    $stmt->execute();

    $row = $stmt->fetch();

    if ($row) {
        return $row;
    }

    return null;
}


// Get multiple rows from the database
function all_rows(
    PDO $pdo,
    string $sql,
    array $params = []
): array {

    $stmt = $pdo->prepare($sql);
    bind_params($stmt, $params);
    $stmt->execute();

    return $stmt->fetchAll();
}


function bind_params(
    PDOStatement $stmt,
    array $params
): void {

    foreach ($params as $index => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($index + 1, $value, $type);
    }

}


// Get the ID for a semester
function semester_id(
    PDO $pdo,
    string $name = 'Fall 2026'
): ?int {

    $sql = "SELECT semester_ID
            FROM Semester
            WHERE semester_Name = ?
            LIMIT 1";

    $semester = one(
        $pdo,
        $sql,
        [$name]
    );

    if ($semester) {
        return (int) $semester['semester_ID'];
    }

    return null;
}


// Find the maximum number of credits a student can take
function student_credit_limit(
    PDO $pdo,
    int $studentID
): int {

    $fullTime = one(
        $pdo,
        "SELECT 1
         FROM Full_Time_Undergraduate
         WHERE student_ID = ?",
        [$studentID]
    );

    if ($fullTime) {
        return 16;
    }


    $partTime = one(
        $pdo,
        "SELECT 1
         FROM Part_Time_Undergraduate
         WHERE student_ID = ?",
        [$studentID]
    );

    if ($partTime) {
        return 8;
    }


    return 16;
}


// Check if a student is allowed to register for a course
function registration_check(
    PDO $pdo,
    int $studentID,
    int $crn
): array {

    // Get information about the course section
    $sql = "SELECT
                Course_Section.*,
                Course.course_Credits,
                Course.course_Type,
                Course.course_ID
            FROM Course_Section
            JOIN Course
                ON Course.course_ID = Course_Section.course_ID
            WHERE Course_Section.CRN = ?";

    $section = one(
        $pdo,
        $sql,
        [$crn]
    );


    if (!$section) {
        return [
            false,
            'Course section not found.'
        ];
    }


    // Check for student holds
    $hold = one(
        $pdo,
        "SELECT 1
         FROM Student_Hold
         WHERE student_ID = ?
         LIMIT 1",
        [$studentID]
    );

    if ($hold) {
        return [
            false,
            'Registration blocked: you have a hold.'
        ];
    }


    // Check if seats are available
    if ((int) $section['available_Seats'] <= 0) {
        return [
            false,
            'Registration blocked: no seats are available.'
        ];
    }


    // Check if the student is already registered
    $alreadyRegistered = one(
        $pdo,
        "SELECT 1
         FROM Enrollment
         WHERE student_ID = ?
         AND CRN = ?",
        [$studentID, $crn]
    );

    if ($alreadyRegistered) {
        return [
            false,
            'You are already registered for this section.'
        ];
    }


    // Get the student's academic level
    $student = one(
        $pdo,
        "SELECT student_Type
         FROM Student
         WHERE student_ID = ?",
        [$studentID]
    );


    // Undergraduate students cannot take graduate courses
    if (
        $student &&
        $student['student_Type'] === 'Undergraduate' &&
        $section['course_Type'] === 'Graduate'
    ) {
        return [
            false,
            'Undergraduate students cannot register for graduate courses.'
        ];
    }


    // Graduate students cannot take undergraduate courses
    if (
        $student &&
        $student['student_Type'] === 'Graduate' &&
        $section['course_Type'] === 'Undergraduate'
    ) {
        return [
            false,
            'Graduate students cannot register for undergraduate courses.'
        ];
    }


    // Check if the student already passed this course
    $sql = "SELECT 1
            FROM Student_History
            WHERE student_ID = ?
            AND course_ID = ?
            AND Grade IN (
                'A', 'A-', 'B+', 'B', 'B-',
                'C+', 'C', 'C-', 'D+', 'D'
            )
            LIMIT 1";

    $passedCourse = one(
        $pdo,
        $sql,
        [
            $studentID,
            $section['course_ID']
        ]
    );

    if ($passedCourse) {
        return [
            false,
            'Registration blocked: you already passed this course.'
        ];
    }


    // Get prerequisites for the course
    $sql = "SELECT
                prerequisite_course_ID,
                min_Grade_Req
            FROM Course_Prerequisite
            WHERE course_ID = ?";

    $prerequisites = all_rows(
        $pdo,
        $sql,
        [$section['course_ID']]
    );


    // Check each prerequisite
    foreach ($prerequisites as $prerequisite) {

        $completedPrerequisite = one(
            $pdo,
            "SELECT 1
             FROM Student_History
             WHERE student_ID = ?
             AND course_ID = ?
             AND Grade IS NOT NULL
             LIMIT 1",
            [
                $studentID,
                $prerequisite['prerequisite_course_ID']
            ]
        );

        if (!$completedPrerequisite) {
            return [
                false,
                'Registration blocked: prerequisite requirements are not satisfied.'
            ];
        }
    }


    // Calculate the student's current credits
    $sql = "SELECT
                COALESCE(SUM(Course.course_Credits), 0) AS total
            FROM Enrollment
            JOIN Course_Section
                ON Course_Section.CRN = Enrollment.CRN
            JOIN Course
                ON Course.course_ID = Course_Section.course_ID
            WHERE Enrollment.student_ID = ?
            AND Enrollment.semester_ID = ?";

    $credits = one(
        $pdo,
        $sql,
        [
            $studentID,
            $section['semester_ID']
        ]
    );


    $creditLimit = student_credit_limit(
        $pdo,
        $studentID
    );

    $currentCredits = (int) ($credits['total'] ?? 0);
    $courseCredits = (int) $section['course_Credits'];

    if (
        $currentCredits + $courseCredits >
        $creditLimit
    ) {
        return [
            false,
            "Registration blocked: this would exceed your {$creditLimit}-credit limit."
        ];
    }


    // Check for another course at the same time
    $sql = "SELECT 1
            FROM Enrollment
            JOIN Course_Section
                ON Course_Section.CRN = Enrollment.CRN
            WHERE Enrollment.student_ID = ?
            AND Enrollment.semester_ID = ?
            AND Course_Section.time_Slot_ID = ?
            LIMIT 1";

    $timeConflict = one(
        $pdo,
        $sql,
        [
            $studentID,
            $section['semester_ID'],
            $section['time_Slot_ID']
        ]
    );

    if ($timeConflict) {
        return [
            false,
            'Registration blocked: this course has a time conflict.'
        ];
    }


    // Student passed all registration checks
    return [
        true,
        'Eligible'
    ];
}

?>
