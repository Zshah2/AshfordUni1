<?php

// Load database settings from the .env file
function load_env(string $path): void
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file(
        $path,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    foreach ($lines as $line) {

        $line = trim($line);

        // Ignore blank lines and comments
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Ignore lines that do not contain =
        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);

        $key = trim($key);
        $value = trim($value);
        $value = trim($value, "\"'");

        $_ENV[$key] = $value;
    }
}


// Load the .env file
load_env(dirname(__DIR__) . '/.env');


// Database information
$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$database = $_ENV['DB_NAME'] ?? 'ashford_university';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';


// Connect to the MySQL database
try {

    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    http_response_code(500);

    exit(
        'Database connection failed. Please check your MySQL settings.'
    );
}

?>