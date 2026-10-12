
<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

function sendEmail(string $to, string $subject, string $message): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Username = $_ENV['SMTP_USER'];
        $mail->Password = $_ENV['SMTP_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) ($_ENV['SMTP_PORT'] ?? 587);

        $mail->setFrom(
            $_ENV['SMTP_FROM_EMAIL'],
            $_ENV['SMTP_FROM_NAME'] ?? 'Ashford University Project'
        );

        $mail->addAddress($to);
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body = $message;

        return $mail->send();

    } catch (\Throwable $e) {
        error_log('Email sending failed: ' . $e->getMessage());
        return false;
    }
}


