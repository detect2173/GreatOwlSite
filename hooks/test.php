<?php
// Simple webhook test endpoint for debugging
// URL: https://greatowlmarketing.com/hooks/test.php

header('Content-Type: text/plain');

echo "=== Webhook Test Endpoint ===\n\n";

echo "Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN') . "\n";
echo "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'NONE') . "\n";
echo "Request Time: " . date('Y-m-d H:i:s') . "\n";
echo "PHP User: " . get_current_user() . "\n";
echo "Working Directory: " . getcwd() . "\n";

echo "\n=== GitHub Headers ===\n";
$githubHeaders = [
    'HTTP_X_GITHUB_EVENT',
    'HTTP_X_GITHUB_DELIVERY', 
    'HTTP_X_HUB_SIGNATURE_256',
    'HTTP_X_GITHUB_HOOK_ID',
    'HTTP_X_GITHUB_HOOK_INSTALLATION_TARGET_ID'
];

foreach ($githubHeaders as $header) {
    $value = $_SERVER[$header] ?? 'NOT SET';
    echo "$header: $value\n";
}

echo "\n=== Payload Info ===\n";
$payload = file_get_contents('php://input');
echo "Payload Length: " . strlen($payload) . "\n";

if (strlen($payload) > 0) {
    $data = json_decode($payload, true);
    if (is_array($data)) {
        echo "Valid JSON: YES\n";
        echo "Repository: " . ($data['repository']['full_name'] ?? 'UNKNOWN') . "\n";
        echo "Ref: " . ($data['ref'] ?? 'UNKNOWN') . "\n";
        echo "Event Type: " . ($_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'UNKNOWN') . "\n";
        echo "Pusher: " . ($data['pusher']['name'] ?? 'UNKNOWN') . "\n";
        echo "Head Commit: " . ($data['head_commit']['id'] ?? 'UNKNOWN') . "\n";
        echo "Head Commit Message: " . ($data['head_commit']['message'] ?? 'UNKNOWN') . "\n";
    } else {
        echo "Valid JSON: NO\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
        echo "Payload Preview: " . substr($payload, 0, 200) . "\n";
    }
} else {
    echo "No payload received\n";
}

echo "\n=== File System Checks ===\n";
$checkPaths = [
    '/home/greagfup/deploy/deploy.php' => 'Deploy Script',
    '/home/greagfup/gom_repo/GreatOwlSite' => 'Repository Path',
    '/home/greagfup/public_html/' => 'Deploy Path',
    '/home/greagfup/deploy/deploy.log' => 'Deploy Log',
    '/usr/bin/git' => 'Git Binary',
    '/usr/bin/rsync' => 'Rsync Binary'
];

foreach ($checkPaths as $path => $description) {
    $exists = file_exists($path) ? 'EXISTS' : 'NOT FOUND';
    $readable = is_readable($path) ? 'READABLE' : 'NOT READABLE';
    echo "$description ($path): $exists, $readable\n";
}

echo "\n=== Environment Variables ===\n";
$envVars = ['HOME', 'USER', 'PATH', 'PWD'];
foreach ($envVars as $var) {
    $value = getenv($var) ?: 'NOT SET';
    echo "$var: $value\n";
}

echo "\n=== Test Complete ===\n";
echo "If this is a webhook test, check that GitHub receives a 200 response.\n";
echo "For deploy script issues, check /home/greagfup/deploy/deploy.log\n";