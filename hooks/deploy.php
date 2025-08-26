<?php
// Public loader for GitHub webhook -> includes secure deploy script outside web root
// Path on server: /home/greagfup/public_html/hooks/deploy.php (after deployment)
// Private target: /home/greagfup/deploy/deploy.php

// Enhanced logging for debugging
function log_webhook_event($message) {
    $logFile = '/home/greagfup/deploy/webhook.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Log the incoming request for debugging
$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$githubEvent = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'NONE';
$githubDelivery = $_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? 'NONE';

log_webhook_event("Webhook request: method=$method, event=$githubEvent, delivery=$githubDelivery, userAgent=$userAgent");

// For safety, restrict to POST (GitHub webhooks use POST). This also prevents casual GET access.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_webhook_event("Rejected non-POST request: $method");
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Include the secure script (must exist on the server)
$secureScript = '/home/greagfup/deploy/deploy.php';
log_webhook_event("Looking for deploy script at: $secureScript");

if (file_exists($secureScript)) {
    log_webhook_event("Deploy script found, executing...");
    require_once $secureScript;
    // The secure script will echo/exit; we return just in case
    return;
}

// Helpful guidance if the private script hasn't been created yet
log_webhook_event("Deploy script NOT found at: $secureScript");
http_response_code(500);
header('Content-Type: text/plain');
echo "Deploy script not found at $secureScript\n".
     "Create it on the server (outside public_html) using deploy/deploy.sample.php from this repo as a template.\n".
     "After creating it, set your webhook secret in that file and in GitHub Webhook settings.\n\n".
     "Debug info:\n".
     "- Request method: $method\n".
     "- GitHub event: $githubEvent\n".
     "- GitHub delivery: $githubDelivery\n".
     "- User agent: $userAgent\n".
     "- Expected script location: $secureScript\n".
     "- Current working directory: " . getcwd() . "\n".
     "- PHP user: " . get_current_user() . "\n";
