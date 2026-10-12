
<?php

/**
 * Ashford University
 * Login OTP Authentication
 *
 * Email Provider: Amazon SES
 *
 * Features:
 * - Secure six-digit OTP generation
 * - HMAC-SHA256 OTP hashing
 * - Five-minute expiration
 * - 60-second resend cooldown
 * - Maximum five verification attempts
 * - One-time-use verification codes
 */

require_once __DIR__ . '/ses_mail.php';

/**
 * Generate a secure hash for a user's OTP.
 */
function otp_hash_for_user(int $userId, string $code): string
{
    $secret = $_ENV['OTP_SECRET'] ?? '';

    if (strlen($secret) < 32) {
        throw new RuntimeException(
            'OTP_SECRET is not configured.'
        );
    }

    return hash_hmac(
        'sha256',
        $userId . ':' . $code,
        $secret
    );
}

/**
 * Generate, save, and email a login OTP.
 *
 * Returns true when Amazon SES accepts the email.
 */
function issue_login_otp(
    PDO $pdo,
    int $userId,
    string $email
): bool {

    /*
    |--------------------------------------------------------------------------
    | Check resend cooldown
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "SELECT TIMESTAMPDIFF(
                    SECOND,
                    last_sent_at,
                    UTC_TIMESTAMP()
                )
         FROM login_otp
         WHERE user_ID = ?"
    );

    $stmt->execute([$userId]);

    $seconds = $stmt->fetchColumn();

    // Prevent another OTP request within 60 seconds.
    if (
        $seconds !== false &&
        (int) $seconds < 60
    ) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Generate secure six-digit OTP
    |--------------------------------------------------------------------------
    */

    $code = (string) random_int(
        100000,
        999999
    );

    $hash = otp_hash_for_user(
        $userId,
        $code
    );

    /*
    |--------------------------------------------------------------------------
    | Store OTP in MySQL
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "INSERT INTO login_otp
            (
                user_ID,
                otp_hash,
                expires_at,
                attempts,
                last_sent_at
            )
         VALUES
            (
                ?,
                ?,
                DATE_ADD(
                    UTC_TIMESTAMP(),
                    INTERVAL 5 MINUTE
                ),
                0,
                UTC_TIMESTAMP()
            )
         ON DUPLICATE KEY UPDATE
            otp_hash = VALUES(otp_hash),
            expires_at = VALUES(expires_at),
            attempts = 0,
            last_sent_at = UTC_TIMESTAMP()"
    );

    $stmt->execute([
        $userId,
        $hash
    ]);

    /*
    |--------------------------------------------------------------------------
    | Send OTP through Amazon SES
    |--------------------------------------------------------------------------
    */

    $sent = send_ses_otp(
        $email,
        $code
    );

    if (!$sent) {
        error_log(
            'Amazon SES OTP delivery request failed for user ID: ' .
            $userId
        );

        return false;
    }

    return true;
}

/**
 * Verify the six-digit login OTP.
 *
 * Returns true only if:
 * - The OTP exists
 * - The OTP has not expired
 * - Fewer than five attempts have been used
 * - The submitted code matches
 *
 * Successful verification deletes the OTP.
 */
function verify_login_otp(
    PDO $pdo,
    int $userId,
    string $code
): bool {

    /*
    |--------------------------------------------------------------------------
    | Validate OTP format
    |--------------------------------------------------------------------------
    */

    if (!preg_match('/^[0-9]{6}$/', $code)) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieve OTP information
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "SELECT
            otp_hash,
            attempts,
            (
                expires_at > UTC_TIMESTAMP()
            ) AS is_valid
         FROM login_otp
         WHERE user_ID = ?"
    );

    $stmt->execute([$userId]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | Check expiration and attempt limit
    |--------------------------------------------------------------------------
    */

    if (
        !$row ||
        (int) $row['is_valid'] !== 1 ||
        (int) $row['attempts'] >= 5
    ) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Consume one verification attempt
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "UPDATE login_otp
         SET attempts = attempts + 1
         WHERE user_ID = ?
           AND attempts < 5
           AND expires_at > UTC_TIMESTAMP()"
    );

    $stmt->execute([$userId]);

    if ($stmt->rowCount() !== 1) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Verify OTP hash
    |--------------------------------------------------------------------------
    */

    $expected = otp_hash_for_user(
        $userId,
        $code
    );

    if (!hash_equals(
        $row['otp_hash'],
        $expected
    )) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete OTP after successful verification
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare(
        "DELETE FROM login_otp
         WHERE user_ID = ?"
    );

    $stmt->execute([$userId]);

    return true;
}
