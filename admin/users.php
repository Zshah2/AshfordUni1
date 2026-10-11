<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/layout.php';
require_role(['Admin']);

if (empty($_SESSION['admin_users_csrf'])) {
    $_SESSION['admin_users_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$success = isset($_GET['saved']) ? 'Roster information updated successfully.' : '';
$editing = null;
$systemAdmin = is_system_admin($pdo);
$fields = ['first_Name', 'middle_Name', 'last_Name', 'street', 'city', 'state', 'zip_Code', 'phone_no'];
$requiredFields = ['first_Name', 'last_Name', 'street', 'city', 'state', 'zip_Code'];
$maxLengths = ['first_Name' => 50, 'middle_Name' => 50, 'last_Name' => 50, 'street' => 100, 'city' => 50, 'state' => 50, 'zip_Code' => 10, 'phone_no' => 20];

$q = trim((string) ($_GET['q'] ?? ''));
$allowedRoles = ['Student', 'Faculty', 'Admin', 'StatStaff'];
$role = (string) ($_GET['role'] ?? '');
if (!in_array($role, $allowedRoles, true)) {
    $role = '';
}
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 25;
$holdId = filter_input(INPUT_GET, 'holds', FILTER_VALIDATE_INT);
$holdStudent = null;
$holdTypes = $pdo->query('SELECT hold_ID, hold_Type FROM hold ORDER BY hold_ID')->fetchAll(PDO::FETCH_ASSOC);
$holdSuggestions = [
    1 => 'Student has an outstanding tuition balance that must be resolved.',
    2 => 'Student must meet with an academic advisor.',
    3 => 'Required immunization records have not been submitted.',
    4 => 'Required admissions documents are incomplete.',
    5 => 'Student must submit required financial aid documentation.',
    6 => 'Student academic standing requires review.',
];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['admin_users_csrf'], (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('403 - Invalid form token.');
    }

    $action = (string) ($_POST['action'] ?? 'edit');
    $id = filter_var($_POST['user_ID'] ?? null, FILTER_VALIDATE_INT);
    if (!$id) {
        $error = 'Invalid user ID.';
    } elseif (in_array($action, ['place_hold', 'remove_hold'], true)) {
        // Holds can only be managed for real student records.
        $studentCheck = $pdo->prepare("SELECT s.student_ID FROM student s JOIN `user` u ON u.user_ID = s.student_ID WHERE s.student_ID = ? AND u.user_Type = 'Student'");
        $studentCheck->execute([$id]);
        if (!$studentCheck->fetchColumn()) {
            $error = 'Cannot manage holds for user ID ' . $id . ': this account is marked Student, but no matching student record exists. Check the student table.';
        } else {
            $selectedHold = filter_var($_POST['hold_ID'] ?? null, FILTER_VALIDATE_INT);
            $validHoldIds = array_map('intval', array_column($holdTypes, 'hold_ID'));
            if (!$selectedHold || !in_array($selectedHold, $validHoldIds, true)) {
                $error = 'Select a valid hold type.';
            } elseif ($action === 'place_hold') {
                $reason = trim((string) ($_POST['hold_Reason'] ?? ''));
                if ($reason === '') {
                    $error = 'Enter a reason for the hold.';
                } elseif (mb_strlen($reason) > 2000) {
                    $error = 'Hold reason must be 2,000 characters or fewer.';
                } else {
                    try {
                        $insert = $pdo->prepare('INSERT INTO student_hold (student_ID, hold_ID, hold_Date, hold_Reason) VALUES (?, ?, CURRENT_DATE(), ?)');
                        $insert->execute([$id, $selectedHold, $reason]);
                        header('Location: ' . roster_url(['holds' => $id, 'edit' => null, 'saved_hold' => 1]), true, 303);
                        exit;
                    } catch (PDOException $exception) {
                        error_log('Roster hold insert failed: ' . $exception->getMessage());
                        $error = 'Unable to place hold. It may already be assigned to this student.';
                    }
                }
            } else {
                try {
                    $delete = $pdo->prepare('DELETE FROM student_hold WHERE student_ID = ? AND hold_ID = ?');
                    $delete->execute([$id, $selectedHold]);
                    header('Location: ' . roster_url(['holds' => $id, 'edit' => null, 'removed_hold' => 1]), true, 303);
                    exit;
                } catch (PDOException $exception) {
                    error_log('Roster hold removal failed: ' . $exception->getMessage());
                    $error = 'Unable to remove this hold.';
                }
            }
        }
    } elseif ($action === 'change_admin_level') {
        if (!$systemAdmin) {
            http_response_code(403);
            exit('403 - System Admin access required.');
        }
        $newLevel = filter_var($_POST['security_Level'] ?? null, FILTER_VALIDATE_INT);
        if (!in_array($newLevel, [1, 2], true)) {
            $error = 'Choose a valid administrator security level.';
        } else {
            try {
                $pdo->beginTransaction();
                $target = $pdo->prepare("SELECT ap.security_Level FROM Admin_Permissions ap JOIN `user` u ON u.user_ID = ap.admin_ID WHERE ap.admin_ID = ? AND u.user_Type = 'Admin' FOR UPDATE");
                $target->execute([$id]);
                $oldLevel = $target->fetchColumn();
                if ($oldLevel === false) {
                    $error = 'Administrator permission record not found.';
                } elseif ((int) $oldLevel === 1 && $newLevel === 2) {
                    // Never allow demotion of a System Admin from this roster page.
                    // This avoids accidental self-demotion or removal of the last Level 1 admin.
                    $error = 'For safety, System Admin demotion is not available in the Roster.';
                } else {
                    $update = $pdo->prepare('UPDATE Admin_Permissions SET security_Level = ? WHERE admin_ID = ?');
                    $update->execute([$newLevel, $id]);
                }
                $pdo->commit();
                if ($error === '') {
                    header('Location: /admin/users.php?' . http_build_query(['edit' => $id, 'level_saved' => 1, 'q' => $q, 'role' => $role, 'page' => $page]), true, 303);
                    exit;
                }
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                error_log('Roster admin level update failed: ' . $exception->getMessage());
                $error = 'Unable to update administrator security level.';
            }
        }
    } elseif ($action !== 'edit') {
        $error = 'Invalid action.';
    } else {
        // Keep existing server-side permission enforcement.
        require_admin_account_edit_permission($pdo, $id);
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = trim((string) ($_POST[$field] ?? ''));
        }
        foreach ($requiredFields as $field) {
            if ($values[$field] === '') {
                $error = 'Please fill in all required fields.';
                break;
            }
        }
        if ($error === '') {
            foreach ($maxLengths as $field => $max) {
                if (mb_strlen($values[$field]) > $max) {
                    $error = 'A field exceeds the allowed length: ' . $field;
                    break;
                }
            }
        }
        if ($error === '') {
            $exists = $pdo->prepare('SELECT user_ID FROM `user` WHERE user_ID = ?');
            $exists->execute([$id]);
            if (!$exists->fetchColumn()) {
                $error = 'This roster record no longer exists.';
            } else {
                $values['middle_Name'] = $values['middle_Name'] === '' ? null : $values['middle_Name'];
                $values['phone_no'] = $values['phone_no'] === '' ? null : $values['phone_no'];
                $values['user_ID'] = $id;
                try {
                    $stmt = $pdo->prepare('UPDATE `user` SET first_Name=:first_Name, middle_Name=:middle_Name, last_Name=:last_Name, street=:street, city=:city, state=:state, zip_Code=:zip_Code, phone_no=:phone_no WHERE user_ID=:user_ID');
                    $stmt->execute($values);
                    $query = http_build_query(['saved' => 1, 'q' => $q, 'role' => $role, 'page' => $page, 'edit' => $id]);
                    header('Location: /admin/users.php?' . $query, true, 303);
                    exit;
                } catch (PDOException $exception) {
                    error_log('Roster update failed: ' . $exception->getMessage());
                    $error = 'Unable to save changes. Please check the information and try again.';
                }
            }
        }
    }
}

if (isset($_GET['level_saved'])) $success = 'Administrator security level updated.';
if (isset($_GET['saved_hold'])) $success = 'Student hold placed successfully.';
if (isset($_GET['removed_hold'])) $success = 'Student hold removed successfully.';

$assignedHolds = [];
if ($holdId) {
    $studentStmt = $pdo->prepare("SELECT u.user_ID, u.first_Name, u.last_Name FROM student s JOIN `user` u ON u.user_ID = s.student_ID WHERE s.student_ID = ? AND u.user_Type = 'Student'");
    $studentStmt->execute([$holdId]);
    $holdStudent = $studentStmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($holdStudent) {
        $holdsStmt = $pdo->prepare('SELECT sh.hold_ID, h.hold_Type, sh.hold_Date, sh.hold_Reason FROM student_hold sh JOIN hold h ON h.hold_ID = sh.hold_ID WHERE sh.student_ID = ? ORDER BY sh.hold_Date DESC, sh.hold_ID');
        $holdsStmt->execute([$holdId]);
        $assignedHolds = $holdsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
if ($editId) {
    $stmt = $pdo->prepare('SELECT user_ID, first_Name, middle_Name, last_Name, user_Type, street, city, state, zip_Code, phone_no FROM `user` WHERE user_ID = ?');
    $stmt->execute([$editId]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    if ($editing && $editing['user_Type'] === 'Admin' && $systemAdmin) {
        $levelStmt = $pdo->prepare('SELECT security_Level FROM Admin_Permissions WHERE admin_ID = ?');
        $levelStmt->execute([$editId]);
        $editing['admin_security_level'] = $levelStmt->fetchColumn();
    }
    if ($editing && $_SERVER['REQUEST_METHOD'] === 'POST' && $error !== '') {
        foreach ($fields as $field) {
            if (array_key_exists($field, $_POST)) {
                $editing[$field] = trim((string) $_POST[$field]);
            }
        }
    }
}

$where = [];
$params = [];
if ($q !== '') {
    // Escape SQL LIKE wildcards in literal search input.
    $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $q);
    $params['search'] = '%' . $escaped . '%';
    $where[] = "(CAST(u.user_ID AS CHAR) LIKE :search1 ESCAPE '!' OR u.first_Name LIKE :search2 ESCAPE '!' OR u.last_Name LIKE :search3 ESCAPE '!' OR CONCAT_WS(' ', u.first_Name, NULLIF(u.middle_Name, ''), u.last_Name) LIKE :search4 ESCAPE '!' OR l.user_Email LIKE :search5 ESCAPE '!')";
}
if ($role !== '') {
    $where[] = 'u.user_Type = :role';
    $params['role'] = $role;
}
$whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// login.user_ID is unique for a user in the project's login model.
$fromSql = ' FROM `user` u LEFT JOIN `login` l ON l.user_ID = u.user_ID';
$countStmt = $pdo->prepare('SELECT COUNT(DISTINCT u.user_ID)' . $fromSql . $whereSql);
foreach ($params as $key => $value) {
    if ($key === 'search') {
        for ($i = 1; $i <= 5; $i++) {
            $countStmt->bindValue(':search' . $i, $value, PDO::PARAM_STR);
        }
    } else {
        $countStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
}
$countStmt->execute();
$total = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$listSql = 'SELECT u.user_ID, u.first_Name, u.middle_Name, u.last_Name, u.user_Type, MAX(l.user_Email) AS user_Email, (SELECT COUNT(*) FROM student_hold sh WHERE sh.student_ID = u.user_ID) AS hold_count, EXISTS(SELECT 1 FROM student s WHERE s.student_ID = u.user_ID AND s.user_Type = \'Student\') AS has_student_record'
    . $fromSql . $whereSql
    . ' GROUP BY u.user_ID, u.first_Name, u.middle_Name, u.last_Name, u.user_Type'
    . ' ORDER BY u.user_ID LIMIT :limit OFFSET :offset';
$listStmt = $pdo->prepare($listSql);
foreach ($params as $key => $value) {
    if ($key === 'search') {
        for ($i = 1; $i <= 5; $i++) {
            $listStmt->bindValue(':search' . $i, $value, PDO::PARAM_STR);
        }
    } else {
        $listStmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
    }
}
$listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$listStmt->execute();
$users = $listStmt->fetchAll(PDO::FETCH_ASSOC);

function roster_url(array $overrides = []): string {
    $current = [
        'q' => (string) ($_GET['q'] ?? ''),
        'role' => (string) ($_GET['role'] ?? ''),
        'page' => max(1, (int) ($_GET['page'] ?? 1)),
    ];
    return '/admin/users.php?' . http_build_query(array_merge($current, $overrides));
}

page_start('University Roster', 'Admin', '/admin/users.php');
?>
<style>
/* Compact, page-scoped roster styling; main stylesheet is unchanged. */
.roster-wrap{max-width:1150px;width:100%;min-width:0}
.roster-card{padding:18px 20px;min-width:0}
.roster-top{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px;flex-wrap:wrap}
.roster-top h2{font-size:20px;margin:0;color:#172b4d}
.roster-toolbar{display:flex;gap:14px;align-items:end;flex-wrap:wrap;margin-bottom:20px;width:100%}
.roster-toolbar label{display:flex;flex-direction:column;gap:7px;font-size:14px;font-weight:600;color:#334155}
.roster-toolbar .roster-search-label{flex:2 1 420px;max-width:none;min-width:240px}
.roster-input{width:100%;height:46px;padding:10px 14px;border:1px solid #d7dfeb;border-radius:7px;background:white;color:#26344a;font:inherit;box-sizing:border-box}
.roster-role{min-width:205px}
.roster-toolbar .btn{height:46px;padding:10px 22px}
.roster-clear{padding:12px 5px;font-size:14px}
.roster-table-container{overflow-x:auto}
.roster-table{width:100%;border-collapse:collapse;text-align:left}
.roster-table th,.roster-table td{padding:12px 11px;border-bottom:1px solid #e4e9f0;font-size:13px;vertical-align:middle}
.roster-table th{background:#f4f6fa;color:#334155;font-weight:700;white-space:nowrap}
.roster-table tbody tr:hover{background:#f8fafd}
.roster-table td:first-child{font-weight:600;white-space:nowrap}
.roster-table td:last-child{white-space:nowrap}
.roster-meta{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:15px;font-size:13px;color:#64748b}
.roster-pagination{display:flex;align-items:center;gap:5px;flex-wrap:wrap}
.roster-pagination a,.roster-pagination span.page-current{display:inline-flex;justify-content:center;align-items:center;min-width:34px;height:34px;padding:0 9px;border:1px solid #d7dfeb;border-radius:6px;text-decoration:none;font-size:13px;box-sizing:border-box}
.roster-pagination a{background:white;color:#173b70}
.roster-pagination a:hover{background:#eef3fa}
.roster-pagination .page-current{background:#173b70;color:white;border-color:#173b70;font-weight:700}
.roster-pagination .page-dots{padding:0 4px}
.roster-edit{max-width:850px}
.roster-edit h2{margin-top:0}
.roster-wrap .roster-holds{max-width:850px;margin:0}
.roster-wrap .roster-holds__grid{grid-template-columns:1fr;gap:12px}
.roster-wrap .roster-holds__panel{padding:14px}
.roster-wrap .roster-holds__item{padding:10px}
.roster-wrap .roster-holds__control{max-width:540px}
.roster-wrap .roster-holds__header{margin-bottom:14px}
@media(max-width:650px){.roster-toolbar .roster-search-label{flex-basis:100%;min-width:0}.roster-role{min-width:160px}.roster-card{padding:13px}.roster-table th,.roster-table td{padding:10px 8px}.roster-toolbar .roster-search-label{max-width:none}.roster-meta{align-items:flex-start}}
</style>
<div class="roster-wrap">
<div class="card roster-card">
<?php if ($error): ?><div class="alert error" role="alert"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert success" role="status"><?= e($success) ?></div><?php endif; ?>

<?php if ($holdId && !$holdStudent): ?>
<div class="alert error" role="alert" style="background:#fee2e2;color:#991b1b;border:1px solid #dc2626;padding:14px;border-radius:8px;margin:16px 0;">
    <strong>Cannot manage holds for user ID <?= (int) $holdId ?>.</strong>
    This user is not linked to a valid student record in the <code>student</code> table.
    Verify the student record before placing or removing holds.
    <a href="<?= e(roster_url(['holds' => null])) ?>">Return to Roster</a>
</div>
<?php endif; ?>
<?php if ($holdStudent): ?>
<style>
/* Scoped to Manage Holds only; existing site styles remain unchanged. */
.roster-holds {max-width:860px;margin:12px 0 18px}
.roster-holds__header{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:12px}
.roster-holds__header h2{margin:0 0 5px;font-size:1.2rem}
.roster-holds__muted{color:#667085;font-size:.84rem;margin:0}
.roster-holds__grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:12px;align-items:start}
.roster-holds__panel{border:1px solid #dce2eb;border-radius:10px;padding:14px;background:#fff;min-width:0}
.roster-holds__panel h3{margin:0 0 5px;font-size:1rem}
.roster-holds__label{display:block;font-weight:650;margin:11px 0 5px}
.roster-holds__control{display:block;width:100%;box-sizing:border-box;padding:8px 10px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#172238;font:inherit}
.roster-holds__control:focus{outline:2px solid #d6a800;outline-offset:1px}
.roster-holds__hint{font-size:.82rem;color:#667085;margin:6px 0 0}
.roster-holds__actions{display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-top:12px}
.roster-holds__empty{padding:14px 12px;text-align:center;background:#f4f7fb;border:1px dashed #cbd5e1;border-radius:10px;color:#667085;margin-top:18px}
.roster-holds__item{border:1px solid #dce2eb;border-radius:8px;padding:10px;margin-top:8px;overflow-wrap:anywhere}
.roster-holds__item-head{display:flex;align-items:start;justify-content:space-between;gap:10px;flex-wrap:wrap}
.roster-holds__item h4{margin:0;font-size:.96rem}
.roster-holds__item p{margin:5px 0;color:#475467;font-size:.9rem;white-space:pre-wrap}
.roster-holds__date{color:#667085;font-size:.78rem}
.roster-holds__remove{background:#fff;color:#a32121;border:1px solid #d9a3a3;border-radius:7px;padding:5px 9px;cursor:pointer;font:inherit;font-size:.85rem}
.roster-holds__remove:hover{background:#fff1f1}
@media(max-width:820px){.roster-holds__grid{grid-template-columns:1fr}.roster-holds__panel{padding:12px}}
</style>
<section class="roster-holds" aria-label="Student hold management">
    <div class="roster-holds__header">
        <div>
            <h2>Manage Student Holds</h2>
            <p class="roster-holds__muted"><?= e($holdStudent['first_Name'] . ' ' . $holdStudent['last_Name']) ?> · Student ID <?= (int) $holdStudent['user_ID'] ?></p>
        </div>
        <a href="<?= e(roster_url(['holds' => null])) ?>">← Back to Roster</a>
    </div>
    <div class="roster-holds__grid">
        <section class="roster-holds__panel" aria-labelledby="roster-active-title">
            <h3 id="roster-active-title">Active Holds (<?= count($assignedHolds) ?>)</h3>
            <?php if (!$assignedHolds): ?>
                <div class="roster-holds__empty">No active holds for this student.</div>
            <?php else: ?>
                <?php foreach ($assignedHolds as $assigned): ?>
                    <div class="roster-holds__item">
                        <div class="roster-holds__item-head">
                            <h4><?= e($assigned['hold_Type']) ?></h4>
                            <span class="roster-holds__date"><?= e($assigned['hold_Date']) ?></span>
                        </div>
                        <p><?= e($assigned['hold_Reason'] ?: 'No reason recorded.') ?></p>
                        <form method="post" action="<?= e(roster_url(['holds' => $holdId, 'edit' => null])) ?>" onsubmit="return confirm('Remove this hold from the student?');">
                            <input type="hidden" name="csrf" value="<?= e($_SESSION['admin_users_csrf']) ?>">
                            <input type="hidden" name="action" value="remove_hold">
                            <input type="hidden" name="user_ID" value="<?= (int) $holdId ?>">
                            <input type="hidden" name="hold_ID" value="<?= (int) $assigned['hold_ID'] ?>">
                            <button class="roster-holds__remove" type="submit">Remove hold</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <section class="roster-holds__panel" aria-labelledby="roster-place-title">
            <h3 id="roster-place-title">Place New Hold</h3>
            <form method="post" action="<?= e(roster_url(['holds' => $holdId, 'edit' => null])) ?>">
                <input type="hidden" name="csrf" value="<?= e($_SESSION['admin_users_csrf']) ?>">
                <input type="hidden" name="action" value="place_hold">
                <input type="hidden" name="user_ID" value="<?= (int) $holdId ?>">
                <label class="roster-holds__label" for="roster-hold-type">Hold type</label>
                <select class="roster-holds__control" name="hold_ID" id="roster-hold-type" required>
                    <option value="">Select a hold type</option>
                    <?php foreach ($holdTypes as $type): ?>
                        <option value="<?= (int) $type['hold_ID'] ?>" <?= in_array((int) $type['hold_ID'], array_map('intval', array_column($assignedHolds, 'hold_ID')), true) ? 'disabled' : '' ?>><?= e($type['hold_Type']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="roster-holds__label" for="roster-hold-reason">Reason</label>
                <textarea class="roster-holds__control" id="roster-hold-reason" style="min-height:76px" name="hold_Reason" rows="3" maxlength="2000" placeholder="Choose a hold type to fill a suggested reason, or write your own." required><?= e($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'place_hold' ? (string) ($_POST['hold_Reason'] ?? '') : '') ?></textarea>
                <p class="roster-holds__hint">Suggested reason is editable; date is automatic.</p>
                <div class="roster-holds__actions">
                    <button class="btn" type="submit">Place Hold</button>
                    <a href="<?= e(roster_url(['holds' => null])) ?>">Cancel</a>
                </div>
            </form>
        </section>
    </div>
</section>
<script>
(() => {
    const suggestions = <?= json_encode($holdSuggestions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const select = document.getElementById('roster-hold-type');
    const reason = document.getElementById('roster-hold-reason');
    let lastSuggested = '';
    select.addEventListener('change', () => {
        const next = suggestions[select.value] || '';
        if (!reason.value.trim() || reason.value === lastSuggested) {
            reason.value = next;
        }
        lastSuggested = next;
    });
})();
</script>
<?php endif; ?>

<?php if ($editing): ?>
<div class="roster-edit">
    <h2>Edit <?= e(trim($editing['first_Name'] . ' ' . $editing['last_Name'])) ?> (<?= e($editing['user_ID']) ?>)</h2>
    <?php if ($editing['user_Type'] === 'Admin' && !$systemAdmin): ?>
        <p>Only a System Admin can edit administrator accounts.</p>
    <?php else: ?>
        <form method="post" action="<?= e(roster_url(['edit' => (int) $editing['user_ID']])) ?>">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['admin_users_csrf']) ?>">
            <input type="hidden" name="user_ID" value="<?= (int) $editing['user_ID'] ?>">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                <?php foreach (['first_Name'=>'First name','middle_Name'=>'Middle name','last_Name'=>'Last name','street'=>'Street','city'=>'City','state'=>'State','zip_Code'=>'ZIP code','phone_no'=>'Phone number'] as $field => $label): ?>
                    <label><?= e($label) ?>
                        <input style="display:block;width:100%;padding:8px;box-sizing:border-box;" type="text" name="<?= e($field) ?>" maxlength="<?= $maxLengths[$field] ?>" value="<?= e($editing[$field] ?? '') ?>" <?= in_array($field, $requiredFields, true) ? 'required' : '' ?>>
                    </label>
                <?php endforeach; ?>
            </div>
            <p><button class="btn" type="submit">Save changes</button> <a href="<?= e(roster_url(['edit' => null])) ?>">Cancel</a></p>
        </form>
        <?php if ($editing['user_Type'] === 'Admin'): ?>
            <hr>
            <h3>Administrator Security Level</h3>
            <p>Level 1 = System Admin; Level 2 = Admin. Only System Admins can manage these permissions.</p>
            <?php if ($editing['admin_security_level'] === false): ?>
                <p>No Admin_Permissions record exists for this administrator.</p>
            <?php else: ?>
                <form method="post" action="<?= e(roster_url(['edit' => (int) $editing['user_ID']])) ?>">
                    <input type="hidden" name="csrf" value="<?= e($_SESSION['admin_users_csrf']) ?>">
                    <input type="hidden" name="action" value="change_admin_level">
                    <input type="hidden" name="user_ID" value="<?= (int) $editing['user_ID'] ?>">
                    <label>Security level
                        <select name="security_Level">
                            <option value="1" <?= (int) $editing['admin_security_level'] === 1 ? 'selected' : '' ?>>Level 1 — System Admin</option>
                            <option value="2" <?= (int) $editing['admin_security_level'] === 2 ? 'selected' : '' ?>>Level 2 — Admin</option>
                        </select>
                    </label>
                    <button class="btn" type="submit">Update Security Level</button>
                    <p><small>For safety, this page does not allow demoting a Level 1 System Admin.</small></p>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
<?php elseif ($editId): ?>
<div class="alert error" role="alert">Roster record not found.</div>
<?php endif; ?>

<?php if (!$editing && !$editId && !$holdId): ?>
<div class="roster-top"><h2>Roster Records</h2></div>
<form class="roster-toolbar" method="get" action="/admin/users.php">
    <label class="roster-search-label">Search
        <input class="roster-input" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, university ID, or email">
    </label>
    <label>Role
        <select class="roster-input roster-role" name="role">
            <option value="">All roles</option>
            <?php foreach ($allowedRoles as $option): ?>
                <option value="<?= e($option) ?>" <?= $role === $option ? 'selected' : '' ?>><?= e($option) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="btn" type="submit">Search</button>
    <a class="roster-clear" href="/admin/users.php">Clear</a>
</form>

<div class="roster-table-container">
<table class="roster-table">
    <thead><tr><th>User ID</th><th>Full Name</th><th>Role</th><th>Email</th><th>Hold Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= e($user['user_ID']) ?></td>
            <td><?= e(trim(implode(' ', array_filter([$user['first_Name'], $user['middle_Name'], $user['last_Name']], static fn($part) => $part !== null && $part !== ''))) ) ?></td>
            <td><?= e($user['user_Type']) ?></td>
            <td><?= e($user['user_Email'] ?? '') ?></td>
            <td><?= $user['user_Type'] === 'Student' ? (!(bool) $user['has_student_record'] ? 'Student record missing' : ((int) $user['hold_count'] > 0 ? (int) $user['hold_count'] . ' active' : 'No holds')) : '—' ?></td>
            <td>
                <?php if ($user['user_Type'] === 'Admin' && !$systemAdmin): ?>
                    Restricted
                <?php else: ?>
                    <a href="<?= e(roster_url(['edit' => (int) $user['user_ID']])) ?>">Edit</a>
                <?php endif; ?>
                <?php if ($user['user_Type'] === 'Student'): ?>
                    <?php if ((bool) $user['has_student_record']): ?>
                        · <a href="<?= e(roster_url(['holds' => (int) $user['user_ID'], 'edit' => null])) ?>">Manage Holds</a>
                    <?php else: ?>
                        <span style="display:inline-block;background:#fee2e2;color:#991b1b;border:1px solid #dc2626;border-radius:6px;padding:3px 7px;margin-left:6px;font-weight:600;" role="alert">Cannot manage holds: student record missing</span>
                    <?php endif; ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?><tr><td colspan="6">No roster records match your search.</td></tr><?php endif; ?>
    </tbody>
</table>
</div>
<div class="roster-meta">
    <span>Showing <?= $total ? $offset + 1 : 0 ?>–<?= min($offset + $perPage, $total) ?> of <?= $total ?> records</span>
    <?php if ($totalPages > 1): ?>
    <nav class="roster-pagination" aria-label="Roster pagination">
        <?php if ($page > 1): ?>
            <a href="<?= e(roster_url(['page' => 1, 'edit' => null, 'holds' => null])) ?>" aria-label="First page">«</a>
            <a href="<?= e(roster_url(['page' => $page - 1, 'edit' => null, 'holds' => null])) ?>">Previous</a>
        <?php endif; ?>
        <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);
            if ($startPage === 2) $startPage = 1;
            if ($endPage === $totalPages - 1) $endPage = $totalPages;
        ?>
        <?php if ($startPage > 1): ?><span class="page-dots" aria-hidden="true">…</span><?php endif; ?>
        <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="page-current" aria-current="page"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= e(roster_url(['page' => $p, 'edit' => null, 'holds' => null])) ?>"><?= $p ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($endPage < $totalPages): ?><span class="page-dots" aria-hidden="true">…</span><?php endif; ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?= e(roster_url(['page' => $page + 1, 'edit' => null, 'holds' => null])) ?>">Next</a>
            <a href="<?= e(roster_url(['page' => $totalPages, 'edit' => null, 'holds' => null])) ?>" aria-label="Last page">»</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>
</div>
<?php endif; ?>
</div>
</div>
<?php page_end(); ?>
