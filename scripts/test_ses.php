
<?php

/**
 * Ashford University — Amazon SES Email Test
 *
 * Run:
 * php scripts/test_ses.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Terminal access only.');
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Aws\SesV2\SesV2Client;
use Aws\Credentials\CredentialProvider;

$credentials = CredentialProvider::memoize(
    CredentialProvider::ini('ashford-ses')
);

$client = new SesV2Client([
    'version' => 'latest',
    'region' => 'us-east-1',
    'credentials' => $credentials
]);

$sender = 'zshah2@oldwestbury.edu';
$recipient = 'zshah2@oldwestbury.edu';

$otp = (string) random_int(100000, 999999);

try {
    $result = $client->sendEmail([
        'FromEmailAddress' => $sender,
        'Destination' => [
            'ToAddresses' => [$recipient]
        ],
        'Content' => [
            'Simple' => [
                'Subject' => [
                    'Data' => 'Ashford University | SES OTP Test'
                ],
                'Body' => [
                    'Text' => [
                        'Data' =>
                            "Ashford University\n\n" .
                            "Your test verification code is: {$otp}\n\n" .
                            "This is a delivery test. The code is not " .
                            "connected to the login system.\n\n" .
                            "Never share your verification codes."
                    ]
                ]
            ]
        ]
    ]);

    echo "SUCCESS: Amazon SES accepted the email.\n";
    echo "Recipient: {$recipient}\n";
    echo "Message ID: " . $result->get('MessageId') . "\n";
    echo "Check your Outlook inbox.\n";

} catch (Throwable $e) {
    error_log('SES test failed: ' . $e->getMessage());
    exit("ERROR: Amazon SES could not send the test email. Check the PHP error log.\n");
}
