
<?php

require_once __DIR__ . '/includes/mailer.php';

$email = 'zshah2@oldwestbury.edu';

if (sendEmail(
    $email,
    'Ashford University - Email Test',
    'Success! Your PHP application can send emails using Brevo.'
)) {
    echo "Email sent successfully!\n";
} else {
    echo "Email failed. Check your SMTP settings.\n";
}

