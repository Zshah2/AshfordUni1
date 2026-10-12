
<?php

/**
 * Ashford University
 * Amazon SES — Email OTP Delivery
 *
 * Sender: zshah2@oldwestbury.edu
 * Region: us-east-1
 * AWS Profile: ashford-ses
 *
 * Used by the login OTP authentication system.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Aws\SesV2\SesV2Client;
use Aws\Credentials\CredentialProvider;

/**
 * Send a six-digit login verification code.
 *
 * @param string $recipient User's registered email
 * @param string $otp       Six-digit verification code
 * @return bool             True if Amazon SES accepts the email
 */
function send_ses_otp(string $recipient, string $otp): bool
{
    // Amazon SES configuration
    $senderEmail = 'zshah2@oldwestbury.edu';
    $senderName = 'Ashford University';

    $region = 'us-east-1';
    $awsProfile = 'ashford-ses';

    // Validate input
    if (
        !filter_var($senderEmail, FILTER_VALIDATE_EMAIL) ||
        !filter_var($recipient, FILTER_VALIDATE_EMAIL) ||
        !preg_match('/^[0-9]{6}$/', $otp)
    ) {
        error_log('Amazon SES OTP: Invalid sender, recipient, or OTP.');
        return false;
    }

    // Escape OTP for HTML output
    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

    /*
    |--------------------------------------------------------------------------
    | HTML Email Template
    |--------------------------------------------------------------------------
    */

    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ashford University Verification Code</title>
</head>

<body style="
    margin:0;
    padding:30px 12px;
    background-color:#f3f5f9;
    font-family:Arial,Helvetica,sans-serif;
">

    <div style="
        max-width:560px;
        margin:0 auto;
        background-color:#ffffff;
        border:1px solid #e2e7ee;
        border-radius:10px;
        overflow:hidden;
    ">

        <!-- University Header -->
        <div style="
            background-color:#142d4e;
            padding:30px 20px;
            text-align:center;
        ">

            <h1 style="
                margin:0;
                color:#ffffff;
                font-size:24px;
                font-weight:bold;
                letter-spacing:1px;
            ">
                ASHFORD UNIVERSITY
            </h1>

            <p style="
                margin:10px 0 0;
                color:#e9be67;
                font-size:12px;
                letter-spacing:2px;
            ">
                ACCOUNT SECURITY
            </p>

        </div>

        <!-- Email Content -->
        <div style="padding:32px 28px;">

            <h2 style="
                color:#142d4e;
                font-size:22px;
                margin:0 0 20px;
            ">
                Your Login Verification Code
            </h2>

            <p style="
                color:#44546a;
                font-size:15px;
                line-height:1.7;
            ">
                Hello,
            </p>

            <p style="
                color:#44546a;
                font-size:15px;
                line-height:1.7;
            ">
                We received a request to sign in to your
                Ashford University account.
                Please enter the verification code below
                to complete your login.
            </p>

            <!-- OTP Code -->
            <div style="
                background-color:#f3f5f9;
                border:1px solid #e2e7ee;
                border-radius:8px;
                text-align:center;
                padding:26px 15px;
                margin:28px 0;
            ">

                <p style="
                    color:#65758b;
                    font-size:11px;
                    font-weight:bold;
                    letter-spacing:1.5px;
                    margin:0 0 14px;
                ">
                    ONE-TIME VERIFICATION CODE
                </p>

                <div style="
                    font-size:38px;
                    font-weight:bold;
                    color:#142d4e;
                    letter-spacing:8px;
                ">
                    {$safeOtp}
                </div>

                <p style="
                    color:#65758b;
                    font-size:13px;
                    margin:16px 0 0;
                ">
                    This code expires in 5 minutes.
                </p>

            </div>

            <!-- Security Notice -->
            <p style="
                color:#44546a;
                font-size:14px;
                line-height:1.8;
            ">
                <strong>Security Reminder:</strong>
                Never share your verification code with anyone.
                Ashford University staff will never ask
                you to disclose it.
            </p>

            <p style="
                color:#44546a;
                font-size:14px;
                line-height:1.8;
            ">
                If you did not attempt to sign in,
                you can safely ignore this email.
            </p>

            <p style="
                margin-top:28px;
                color:#142d4e;
                font-size:14px;
                font-weight:bold;
            ">
                Ashford University
                <span style="
                    display:block;
                    font-weight:normal;
                    color:#65758b;
                ">
                    Information Technology Services
                </span>
            </p>

        </div>

        <!-- Footer -->
        <div style="
            background-color:#f3f5f9;
            border-top:1px solid #e2e7ee;
            padding:20px;
            text-align:center;
        ">

            <p style="
                margin:0;
                color:#65758b;
                font-size:12px;
                line-height:1.6;
            ">
                Ashford University
                <br>
                Automated Account Security Notification
            </p>

            <p style="
                margin:10px 0 0;
                color:#8895a7;
                font-size:11px;
            ">
                This is an automated message.
                Please do not reply.
            </p>

        </div>

    </div>

</body>
</html>
HTML;

    /*
    |--------------------------------------------------------------------------
    | Plain-Text Email Alternative
    |--------------------------------------------------------------------------
    */

    $text = 
        "ASHFORD UNIVERSITY\n" .
        "Account Security\n\n" .
        "Your Login Verification Code\n\n" .
        "Your verification code is: {$otp}\n\n" .
        "This code expires in 5 minutes.\n\n" .
        "Never share this code with anyone.\n\n" .
        "If you did not request this login, " .
        "you can safely ignore this email.\n\n" .
        "Ashford University\n" .
        "Information Technology Services";

    /*
    |--------------------------------------------------------------------------
    | Send Through Amazon SES
    |--------------------------------------------------------------------------
    */

    try {

        // Load credentials from the local AWS profile
        $credentials = CredentialProvider::memoize(
            CredentialProvider::ini($awsProfile)
        );

        // Initialize Amazon SES client
        $client = new SesV2Client([
            'version' => 'latest',
            'region' => $region,
            'credentials' => $credentials
        ]);

        // Send OTP email
        $result = $client->sendEmail([
            'FromEmailAddress' =>
                $senderName . ' <' . $senderEmail . '>',

            'Destination' => [
                'ToAddresses' => [
                    $recipient
                ]
            ],

            'Content' => [
                'Simple' => [

                    'Subject' => [
                        'Data' =>
                            'Ashford University | Login Verification Code',
                        'Charset' => 'UTF-8'
                    ],

                    'Body' => [

                        'Html' => [
                            'Data' => $html,
                            'Charset' => 'UTF-8'
                        ],

                        'Text' => [
                            'Data' => $text,
                            'Charset' => 'UTF-8'
                        ]

                    ]
                ]
            ]
        ]);

        // Log only the SES message ID
        error_log(
            'Amazon SES OTP accepted. Message ID: ' .
            $result->get('MessageId')
        );

        return true;

    } catch (Throwable $e) {

        error_log(
            'Amazon SES OTP failed: ' .
            $e->getMessage()
        );

        return false;
    }
}
