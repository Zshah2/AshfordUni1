<?php

session_start();

require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';


// Get majors for the dropdown
$majors = $pdo->query(
    "SELECT major_ID, major_Name
     FROM Major
     ORDER BY major_Name"
)->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName = trim($_POST['first_name'] ?? '');
    $middleName = trim($_POST['middle_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $gender = $_POST['gender'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');
    $majorID = (int) ($_POST['major_id'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';


    if (
        $firstName === '' ||
        $lastName === '' ||
        $gender === '' ||
        $dob === '' ||
        $street === '' ||
        $city === '' ||
        $state === '' ||
        $zipCode === '' ||
        $majorID === 0 ||
        $email === '' ||
        $password === ''
    ) {
        $error = 'Please fill in all required fields.';
    }

    elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    }

    elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    }

    else {

        // Make sure the email is not already being used
        $stmt = $pdo->prepare(
            "SELECT user_ID
             FROM Login
             WHERE user_Email = ?"
        );

        $stmt->execute([$email]);


        if ($stmt->fetch()) {

            $error = 'An account with this email already exists.';

        } else {

            try {

                $pdo->beginTransaction();


                // Create the next user ID
                $stmt = $pdo->query(
                    "SELECT MAX(user_ID) AS max_id
                     FROM User"
                );

                $row = $stmt->fetch();

                $userID = ((int) $row['max_id']) + 1;


                // Add user
                $sql = "INSERT INTO User
                        (user_ID, first_Name, middle_Name, last_Name,
                         gender, DOB, street, city, state,
                         zip_Code, user_Type)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $userID,
                    $firstName,
                    $middleName !== '' ? $middleName : null,
                    $lastName,
                    $gender,
                    $dob,
                    $street,
                    $city,
                    $state,
                    $zipCode,
                    'Student'
                ]);


                // Create password
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                // Add login
                $sql = "INSERT INTO Login
                        (user_ID, user_Email, user_Password,
                         no_Of_Tries, lock_var, user_Type)
                        VALUES (?, ?, ?, 0, 0, 'Student')";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $userID,
                    $email,
                    $hashedPassword
                ]);


                // Add student information
                $sql = "INSERT INTO Student
                        (student_ID, major_ID, student_Year, student_Type)
                        VALUES (?, ?, ?, ?)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    $userID,
                    $majorID,
                    1,
                    'Undergraduate'
                ]);


                $pdo->commit();

                $success =
                    'Account created successfully. You can now log in.';


            } catch (PDOException $e) {

                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                $error = 'Unable to create the account.';
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Create Account - Ashford University</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

</head>


<body>


<header class="login-header">

    <h1>Ashford University</h1>

    <p>Student & Academic Information System</p>

</header>



<div class="login-container">

    <h2>Create Student Account</h2>

    <p class="login-description">
        Enter your information below to create an account.
    </p>


    <?php if ($error): ?>

        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($success): ?>

        <div class="success-message">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>



    <form method="post" action="register.php">


        <div class="form-group">

            <label for="first_name">
                First Name
            </label>

            <input
                type="text"
                id="first_name"
                name="first_name"
                required
            >

        </div>



        <div class="form-group">

            <label for="middle_name">
                Middle Name
            </label>

            <input
                type="text"
                id="middle_name"
                name="middle_name"
            >

        </div>



        <div class="form-group">

            <label for="last_name">
                Last Name
            </label>

            <input
                type="text"
                id="last_name"
                name="last_name"
                required
            >

        </div>



        <div class="form-group">

            <label for="gender">
                Gender
            </label>

            <select
                id="gender"
                name="gender"
                required
            >

                <option value="">
                    Select
                </option>

                <option value="Male">
                    Male
                </option>

                <option value="Female">
                    Female
                </option>

                <option value="Other">
                    Other
                </option>

            </select>

        </div>



        <div class="form-group">

            <label for="dob">
                Date of Birth
            </label>

            <input
                type="date"
                id="dob"
                name="dob"
                required
            >

        </div>



        <div class="form-group">

            <label for="street">
                Street Address
            </label>

            <input
                type="text"
                id="street"
                name="street"
                required
            >

        </div>



        <div class="form-group">

            <label for="city">
                City
            </label>

            <input
                type="text"
                id="city"
                name="city"
                required
            >

        </div>



        <div class="form-group">

            <label for="state">
                State
            </label>

            <input
                type="text"
                id="state"
                name="state"
                required
            >

        </div>



        <div class="form-group">

            <label for="zip_code">
                ZIP Code
            </label>

            <input
                type="text"
                id="zip_code"
                name="zip_code"
                required
            >

        </div>



        <div class="form-group">

            <label for="major_id">
                Major
            </label>

            <select
                id="major_id"
                name="major_id"
                required
            >

                <option value="">
                    Select Major
                </option>

                <?php foreach ($majors as $major): ?>

                    <option
                        value="<?php echo htmlspecialchars($major['major_ID']); ?>"
                    >
                        <?php echo htmlspecialchars($major['major_Name']); ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </div>



        <div class="form-group">

            <label for="email">
                University Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="first.last@ashford.edu"
                required
            >

        </div>



        <div class="form-group">

            <label for="password">
                Password
            </label>

            <input
                type="password"
                id="password"
                name="password"
                required
            >

        </div>



        <div class="form-group">

            <label for="confirm_password">
                Confirm Password
            </label>

            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                required
            >

        </div>



        <button
            type="submit"
            class="login-button"
        >
            Create Account
        </button>


    </form>



    <p class="login-note">

        Already have an account?

        <a href="index.php">
            Back to Login
        </a>

    </p>

</div>



<footer class="login-footer">

    <p>
        &copy; 2026 Ashford University
    </p>

</footer>


</body>

</html>