<?php

require_once '../config/database.php';

require_once '../includes/layout.php';

require_once '../includes/functions.php';

require_role(['Student', 'Faculty', 'Admin', 'StatStaff']);

function schedule_h($value): string {

    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');

}

$semesterId = (int)($_GET['semester'] ?? 1);

if (!in_array($semesterId, [1, 2], true)) {

    $semesterId = 1;

}

$departmentId = max(0, (int)($_GET['department'] ?? 0));

$dayId = max(0, (int)($_GET['day'] ?? 0));

$periodId = max(0, (int)($_GET['period'] ?? 0));

$search = trim((string)($_GET['search'] ?? ''));

$search = substr($search, 0, 150);

$availableOnly = ($_GET['available'] ?? '0') === '1';

$page = max(1, (int)($_GET['page'] ?? 1));

$perPage = 25;

$departments = $pdo->query('SELECT dept_ID, dept_Name FROM Department ORDER BY dept_Name')->fetchAll(PDO::FETCH_ASSOC);

$days = $pdo->query('SELECT day_ID, day_Of_Week FROM Day ORDER BY day_ID')->fetchAll(PDO::FETCH_ASSOC);

$periods = $pdo->query('SELECT period_ID, start_Time, end_Time FROM Period ORDER BY start_Time')->fetchAll(PDO::FETCH_ASSOC);

$where = ['cs.semester_ID = :semester'];

$params = ['semester' => $semesterId];

if ($departmentId > 0) {

    $where[] = 'c.dept_ID = :department';

    $params['department'] = $departmentId;

}

if ($search !== '') {

    $where[] = "(c.course_Name LIKE :name OR CAST(c.course_ID AS CHAR) LIKE :course_id OR CAST(cs.CRN AS CHAR) LIKE :crn OR u.first_Name LIKE :first_name OR u.last_Name LIKE :last_name OR CONCAT(u.first_Name, ' ', u.last_Name) LIKE :full_name)";

    foreach (['name', 'course_id', 'crn', 'first_name', 'last_name', 'full_name'] as $key) {

        $params[$key] = '%' . $search . '%';

    }

}

if ($dayId > 0) {

    $where[] = 'EXISTS (SELECT 1 FROM time_slot_day tsd_filter WHERE tsd_filter.time_Slot_ID = cs.time_Slot_ID AND tsd_filter.day_ID = :day_id)';

    $params['day_id'] = $dayId;

}

if ($periodId > 0) {

    $where[] = 'EXISTS (SELECT 1 FROM time_slot_period tsp_filter WHERE tsp_filter.time_Slot_ID = cs.time_Slot_ID AND tsp_filter.period_ID = :period_id)';

    $params['period_id'] = $periodId;

}

if ($availableOnly) {

    $where[] = 'cs.available_Seats > 0';

}

$fromSql = ' FROM Course_Section cs JOIN Course c ON c.course_ID = cs.course_ID JOIN Department d ON d.dept_ID = c.dept_ID JOIN Semester s ON s.semester_ID = cs.semester_ID LEFT JOIN Faculty f ON f.faculty_ID = cs.faculty_ID LEFT JOIN User u ON u.user_ID = f.faculty_ID ';

$whereSql = ' WHERE ' . implode(' AND ', $where);

$countStmt = $pdo->prepare('SELECT COUNT(*)' . $fromSql . $whereSql);

$countStmt->execute($params);

$total = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($total / $perPage));

$page = min($page, $totalPages);

$offset = ($page - 1) * $perPage;

$sql = "SELECT cs.CRN, cs.section_No, cs.available_Seats, c.course_ID, c.course_Name, c.course_Credits, c.course_Type, d.dept_Name, s.semester_Name, CASE cs.semester_ID WHEN 1 THEN 'FA26' WHEN 2 THEN 'SP27' ELSE s.semester_Name END AS semester_Code, u.first_Name, u.last_Name, u.phone_no,

(SELECT GROUP_CONCAT(DISTINCT dy.day_Of_Week ORDER BY dy.day_ID SEPARATOR ', ') FROM time_slot_day tsd JOIN Day dy ON dy.day_ID = tsd.day_ID WHERE tsd.time_Slot_ID = cs.time_Slot_ID) AS meeting_days,

(SELECT GROUP_CONCAT(DISTINCT CONCAT(TIME_FORMAT(p.start_Time, '%h:%i %p'), ' – ', TIME_FORMAT(p.end_Time, '%h:%i %p')) ORDER BY p.start_Time SEPARATOR ', ') FROM time_slot_period tsp JOIN Period p ON p.period_ID = tsp.period_ID WHERE tsp.time_Slot_ID = cs.time_Slot_ID) AS meeting_times"

. $fromSql . $whereSql . ' ORDER BY d.dept_Name, c.course_ID, cs.section_No, cs.CRN LIMIT :limit OFFSET :offset';

$stmt = $pdo->prepare($sql);

foreach ($params as $key => $value) {

    $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);

}

$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$stmt->execute();

$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

function schedule_page_url(int $targetPage): string {

    $query = $_GET;

    $query['page'] = $targetPage;

    return 'master-schedule.php?' . http_build_query($query);

}

$hasAdvancedFilters = $departmentId > 0 || $dayId > 0 || $periodId > 0 || $availableOnly;

page_start('Master Schedule', $_SESSION['user_type'] ?? 'Faculty', 'master-schedule.php');

?>

<style>

.ms-page{padding:1.4rem 0 3rem;max-width:1450px;margin:0 auto;color:#0f172a}

.ms-header{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}

.ms-kicker{font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:#2563eb;margin:0 0 6px}

.ms-header h2{font-size:1.85rem;letter-spacing:-.035em;margin:0 0 6px}

.ms-subtitle{color:#64748b;margin:0;font-size:.94rem}

.ms-header-icon{width:52px;height:52px;border-radius:16px;background:#eff6ff;color:#1d4ed8;display:grid;place-items:center;font-size:1.6rem}

.ms-filter-panel{background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:24px;box-shadow:0 8px 30px rgba(15,23,42,.04);margin-bottom:22px}

.ms-primary{display:grid;grid-template-columns:minmax(0,2fr) minmax(190px,1fr);gap:16px}

.ms-filter-panel label{display:block;font-size:.82rem;font-weight:700;color:#334155;margin-bottom:8px}

.ms-filter-panel input,.ms-filter-panel select{display:block;width:100%;min-width:0;height:46px;padding:0 13px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#0f172a;font-size:.91rem;box-sizing:border-box}

.ms-filter-panel input:focus,.ms-filter-panel select:focus{outline:none;border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12)}

.ms-advanced{border-top:1px solid #e2e8f0;margin-top:20px;padding-top:16px}

.ms-advanced summary{cursor:pointer;color:#1d4ed8;font-size:.9rem;font-weight:700;user-select:none}

.ms-advanced-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-top:18px}

.ms-filter-footer{display:flex;align-items:center;justify-content:space-between;gap:16px;border-top:1px solid #e2e8f0;margin-top:20px;padding-top:18px}

.ms-count{font-size:.9rem;color:#64748b}.ms-count strong{color:#0f172a}

.ms-actions{display:flex;align-items:center;gap:12px}

.ms-reset{padding:12px 14px;color:#475569;font-weight:700;text-decoration:none;font-size:.88rem}

.ms-reset:hover{color:#0f172a}

.ms-search-button{border:0;background:#1d4ed8;color:#fff;border-radius:10px;padding:12px 20px;font-size:.9rem;font-weight:700;cursor:pointer}

.ms-search-button:hover{background:#1e40af}

.ms-results-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 12px}

.ms-results-head h3{margin:0;font-size:1.12rem}

.ms-results-head span{font-size:.86rem;color:#64748b}

.ms-table-wrap{overflow-x:auto;background:#fff;border:1px solid #e2e8f0;border-radius:16px;box-shadow:0 5px 22px rgba(15,23,42,.035)}

.ms-table{width:100%;border-collapse:collapse;font-size:.87rem}

.ms-table th{background:#f8fafc;color:#475569;font-size:.73rem;letter-spacing:.045em;text-transform:uppercase;text-align:left;white-space:nowrap;padding:15px 13px;border-bottom:1px solid #e2e8f0}

.ms-table td{padding:16px 13px;border-bottom:1px solid #f1f5f9;vertical-align:top}

.ms-table tbody tr:last-child td{border-bottom:0}

.ms-table tbody tr:hover{background:#f8fafc}

.ms-course-name{font-weight:700;color:#0f172a;min-width:160px}.ms-course-id{font-size:.8rem;color:#2563eb;font-weight:800;margin-bottom:4px}

.ms-muted{color:#64748b}.ms-nowrap{white-space:nowrap}

.ms-seat{display:inline-block;padding:5px 9px;border-radius:999px;font-size:.78rem;font-weight:800;white-space:nowrap}

.ms-seat.open{background:#dcfce7;color:#166534}.ms-seat.full{background:#fee2e2;color:#991b1b}

.ms-empty{text-align:center;padding:48px 20px!important;color:#64748b}

.ms-pagination{display:flex;align-items:center;justify-content:center;gap:18px;margin-top:22px;font-size:.9rem}

.ms-pagination a{padding:9px 13px;background:#fff;border:1px solid #cbd5e1;border-radius:9px;text-decoration:none;color:#1d4ed8;font-weight:700}

.ms-pagination a:hover{background:#eff6ff}

.ms-pagination span{color:#475569}

@media(max-width:980px){.ms-advanced-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

@media(max-width:640px){.ms-filter-panel{padding:16px}.ms-primary,.ms-advanced-grid{grid-template-columns:1fr}.ms-filter-footer{align-items:stretch;flex-direction:column}.ms-actions{justify-content:flex-end}.ms-header-icon{display:none}.ms-header h2{font-size:1.5rem}}

</style>

<div class="dashboard-page ms-page">

    <div class="ms-header">

        <div>

            <p class="ms-kicker">Academics</p>

            <h2>University Master Schedule</h2>

            <p class="ms-subtitle">Explore course offerings, instructors, meeting times, and open seats.</p>

        </div>

        <div class="ms-header-icon" aria-hidden="true">🎓</div>

    </div>

    <form method="GET" action="master-schedule.php" class="ms-filter-panel">

        <div class="ms-primary">

            <div>

                <label for="search">Search courses</label>

                <input type="search" name="search" id="search" value="<?= schedule_h($search) ?>" placeholder="Course name, number, CRN, or instructor...">

            </div>

            <div>

                <label for="semester">Semester</label>

                <select name="semester" id="semester">

                    <option value="1" <?= $semesterId === 1 ? 'selected' : '' ?>>FA26</option>

                    <option value="2" <?= $semesterId === 2 ? 'selected' : '' ?>>SP27</option>

                </select>

            </div>

        </div>

        <details class="ms-advanced" <?= $hasAdvancedFilters ? 'open' : '' ?>>

            <summary>Advanced filters — department, day, time & seats</summary>

            <div class="ms-advanced-grid">

                <div>

                    <label for="department">Department</label>

                    <select name="department" id="department">

                        <option value="0">All departments</option>

                        <?php foreach ($departments as $dept): ?>

                            <option value="<?= (int)$dept['dept_ID'] ?>" <?= $departmentId === (int)$dept['dept_ID'] ? 'selected' : '' ?>><?= schedule_h($dept['dept_Name']) ?></option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label for="day">Meeting day</label>

                    <select name="day" id="day">

                        <option value="0">Any day</option>

                        <?php foreach ($days as $day): ?>

                            <option value="<?= (int)$day['day_ID'] ?>" <?= $dayId === (int)$day['day_ID'] ? 'selected' : '' ?>><?= schedule_h($day['day_Of_Week']) ?></option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label for="period">Meeting time</label>

                    <select name="period" id="period">

                        <option value="0">Any time</option>

                        <?php foreach ($periods as $period): ?>

                            <option value="<?= (int)$period['period_ID'] ?>" <?= $periodId === (int)$period['period_ID'] ? 'selected' : '' ?>><?= schedule_h(date('g:i A', strtotime($period['start_Time']))) ?> – <?= schedule_h(date('g:i A', strtotime($period['end_Time']))) ?></option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div>

                    <label for="available">Seat availability</label>

                    <select name="available" id="available">

                        <option value="0">All sections</option>

                        <option value="1" <?= $availableOnly ? 'selected' : '' ?>>Open seats only</option>

                    </select>

                </div>

            </div>

        </details>

        <div class="ms-filter-footer">

            <div class="ms-count"><strong><?= number_format($total) ?></strong> matching sections</div>

            <div class="ms-actions">

                <a class="ms-reset" href="master-schedule.php">Reset</a>

                <button class="ms-search-button" type="submit">Search courses</button>

            </div>

        </div>

    </form>

    <div class="ms-results-head">

        <h3>Course sections</h3>

        <span>Showing <?= $total ? number_format($offset + 1) : 0 ?>–<?= number_format(min($offset + $perPage, $total)) ?> of <?= number_format($total) ?></span>

    </div>

    <div class="ms-table-wrap">

        <table class="ms-table">

            <thead>

                <tr>

                    <th>CRN</th><th>Course</th><th>Section</th><th>Semester</th><th>Department</th><th>Instructor</th><th>Faculty Phone</th><th>Days</th><th>Time</th><th>Credits</th><th>Level</th><th>Seats</th>

                </tr>

            </thead>

            <tbody>

                <?php if (!$sections): ?>

                    <tr><td class="ms-empty" colspan="12">No course sections match your search. Try adjusting the filters.</td></tr>

                <?php else: ?>

                    <?php foreach ($sections as $section): ?>

                        <?php

                            $instructor = trim(($section['first_Name'] ?? '') . ' ' . ($section['last_Name'] ?? ''));

                            $seats = (int)$section['available_Seats'];

                        ?>

                        <tr>

                            <td class="ms-nowrap"><?= schedule_h($section['CRN']) ?></td>

                            <td><div class="ms-course-id"><?= schedule_h($section['course_ID']) ?></div><div class="ms-course-name"><?= schedule_h($section['course_Name']) ?></div></td>

                            <td><?= schedule_h($section['section_No']) ?></td>
                            <td class="ms-nowrap"><?= schedule_h($section['semester_Code']) ?></td>

                            <td><?= schedule_h($section['dept_Name']) ?></td>

                            <td><?= schedule_h($instructor !== '' ? $instructor : 'Not assigned') ?></td>
                            <td class="ms-nowrap"><?= schedule_h($section['phone_no'] ?: '—') ?></td>

                            <td><?= schedule_h($section['meeting_days'] ?: 'TBA') ?></td>

                            <td><?= schedule_h($section['meeting_times'] ?: 'TBA') ?></td>

                            <td><?= schedule_h($section['course_Credits']) ?></td>

                            <td class="ms-muted"><?= schedule_h($section['course_Type']) ?></td>

                            <td><span class="ms-seat <?= $seats > 0 ? 'open' : 'full' ?>"><?= $seats > 0 ? $seats . ' open' : 'Full' ?></span></td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <?php if ($totalPages > 1): ?>

        <nav class="ms-pagination" aria-label="Course results pages">

            <?php if ($page > 1): ?><a href="<?= schedule_h(schedule_page_url($page - 1)) ?>">← Previous</a><?php endif; ?>

            <span>Page <?= $page ?> of <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?><a href="<?= schedule_h(schedule_page_url($page + 1)) ?>">Next →</a><?php endif; ?>

        </nav>

    <?php endif; ?>

</div>

<?php page_end(); ?>