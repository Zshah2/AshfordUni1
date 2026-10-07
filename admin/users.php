<?php

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Admin']);

$adminID = (int) $_SESSION['user_id'];


// Get the admin's access level
$admin = one(
    $pdo,
    "SELECT security_Level
     FROM Admin
     WHERE user_ID = ?",
    [$adminID]
);

$securityLevel = $admin['security_Level'] ?? 'Read-Only';


// Search
$search = trim($_GET['search'] ?? '');


// Update a user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($securityLevel !== 'Full-Access') {

        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'You have read-only access and cannot update users.'
        ];

        header('Location: users.php');
        exit;
    }


    $userID = (int) ($_POST['user_id'] ?? 0);

    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');


    $oldUser = one(
        $pdo,
        "SELECT
            first_Name,
            last_Name,
            city,
            state
         FROM User
         WHERE user_ID = ?",
        [$userID]
    );


    if ($oldUser) {

        try {

            $pdo->beginTransaction();


            $sql = "UPDATE User
                    SET
                        first_Name = ?,
                        last_Name = ?,
                        city = ?,
                        state = ?
                    WHERE user_ID = ?";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                $firstName,
                $lastName,
                $city,
                $state,
                $userID
            ]);


            $changes = [
                'first_Name' => [$oldUser['first_Name'], $firstName],
                'last_Name' => [$oldUser['last_Name'], $lastName],
                'city' => [$oldUser['city'], $city],
                'state' => [$oldUser['state'], $state]
            ];


            foreach ($changes as $field => $values) {

                $oldValue = (string) $values[0];
                $newValue = (string) $values[1];

                if ($oldValue !== $newValue) {

                    $sql = "INSERT INTO User_Update_Log
                                (
                                    user_ID,
                                    changed_By_User_ID,
                                    changed_At,
                                    changed_Field,
                                    old_Value,
                                    new_Value,
                                    user_Request_Reference
                                )
                            VALUES (?, ?, NOW(), ?, ?, ?, ?)";

                    $stmt = $pdo->prepare($sql);

                    $stmt->execute([
                        $userID,
                        $adminID,
                        $field,
                        $oldValue,
                        $newValue,
                        'Admin portal update'
                    ]);
                }
            }


            $pdo->commit();


            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'User information updated.'
            ];

        } catch (Exception $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }


            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'The user could not be updated.'
            ];
        }
    }


    header('Location: users.php');
    exit;
}


// Get one user for editing
$editUser = null;

if (isset($_GET['edit'])) {

    $editID = (int) $_GET['edit'];

    $editUser = one(
        $pdo,
        "SELECT
            user_ID,
            first_Name,
            last_Name,
            user_Type,
            city,
            state
         FROM User
         WHERE user_ID = ?",
        [$editID]
    );
}


// Get users
if ($search !== '') {

    $searchValue = '%' . $search . '%';

    $sql = "SELECT
                user_ID,
                first_Name,
                last_Name,
                user_Type,
                city,
                state
            FROM User
            WHERE
                CAST(user_ID AS CHAR) LIKE ?
                OR first_Name LIKE ?
                OR last_Name LIKE ?
            ORDER BY user_ID
            LIMIT 25";

    $users = all_rows(
        $pdo,
        $sql,
        [
            $searchValue,
            $searchValue,
            $searchValue
        ]
    );

} else {

    $sql = "SELECT
                user_ID,
                first_Name,
                last_Name,
                user_Type,
                city,
                state
            FROM User
            ORDER BY user_ID
            LIMIT 25";

    $users = all_rows(
        $pdo,
        $sql
    );
}


page_start(
    'Users',
    'Admin',
    'users.php'
);

flash();

?>


<section class="card" aria-label="User management">

    <div class="dashboard-section-heading">
        <div>
            <p class="dashboard-eyebrow">Directory</p>
            <h2>User access</h2>
        </div>
        <span class="badge">
            <?= e($securityLevel) ?>
        </span>
    </div>

    <form method="get" class="filters" style="margin-top: 18px;">

        <div>
            <label for="search">Search users</label>
            <input
                type="search"
                name="search"
                id="search"
                value="<?= e($search) ?>"
                placeholder="ID, first name, or last name"
            >
        </div>

        <div style="align-self: end;">
            <button type="submit" class="btn">
                Search users
            </button>
        </div>

        <?php if ($search !== ''): ?>

            <div style="align-self: end;">
                <a href="users.php" class="btn light">
                    Clear search
                </a>
            </div>

        <?php endif; ?>

    </form>

</section>


<?php if (
    $editUser &&
    $securityLevel === 'Full-Access'
): ?>

    <section class="card">

        <div class="dashboard-section-heading">
            <div>
                <p class="dashboard-eyebrow">Profile</p>
                <h2>Edit user <?= e($editUser['user_ID']) ?></h2>
            </div>
        </div>

        <form method="post" class="form-grid" style="margin-top: 18px;">

            <input
                type="hidden"
                name="user_id"
                value="<?= e($editUser['user_ID']) ?>"
            >

            <div>
                <label for="first_name">First name</label>
                <input
                    id="first_name"
                    type="text"
                    name="first_name"
                    value="<?= e($editUser['first_Name']) ?>"
                    required
                >
            </div>

            <div>
                <label for="last_name">Last name</label>
                <input
                    id="last_name"
                    type="text"
                    name="last_name"
                    value="<?= e($editUser['last_Name']) ?>"
                    required
                >
            </div>

            <div>
                <label for="city">City</label>
                <input
                    id="city"
                    type="text"
                    name="city"
                    value="<?= e($editUser['city']) ?>"
                >
            </div>

            <div>
                <label for="state">State</label>
                <input
                    id="state"
                    type="text"
                    name="state"
                    value="<?= e($editUser['state']) ?>"
                >
            </div>

            <div class="form-actions" style="display:flex; gap:10px; align-items:center; grid-column: 1 / -1;">
                <button type="submit" class="btn">
                    Save changes
                </button>
                <a href="users.php" class="btn light">
                    Cancel
                </a>
            </div>

        </form>

    </section>

<?php endif; ?>


<div class="table-wrap">

    <table>

        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Role</th>
            <th>Location</th>

            <?php if ($securityLevel === 'Full-Access'): ?>
                <th>Action</th>
            <?php endif; ?>

        </tr>


        <?php foreach ($users as $user): ?>

            <tr>

                <td>
                    <?= e($user['user_ID']) ?>
                </td>

                <td>
                    <?= e(
                        $user['first_Name'] . ' ' .
                        $user['last_Name']
                    ) ?>
                </td>

                <td>
                    <?= e($user['user_Type']) ?>
                </td>

                <td>
                    <?= e(
                        $user['city'] . ', ' .
                        $user['state']
                    ) ?>
                </td>


                <?php if ($securityLevel === 'Full-Access'): ?>

                    <td>

                        <a
                            href="users.php?edit=<?= e($user['user_ID']) ?>"
                            class="btn small"
                        >
                            Edit
                        </a>

                    </td>

                <?php endif; ?>

            </tr>

        <?php endforeach; ?>

    </table>

</div>


<section class="card">
    <p class="muted">
        Showing up to 25 users. Use the search box to find another user.
    </p>
</section>


<?php

page_end();

?>