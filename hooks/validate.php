<?php
// Webhook validation endpoint - helps debug webhook issues
// URL: https://greatowlmarketing.com/hooks/validate.php

header('Content-Type: text/plain');

// Enhanced logging for debugging
function log_validation($message) {
    $logFile = '/home/greagfup/deploy/webhook_validation.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
$githubEvent = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? 'NONE';
$githubDelivery = $_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? 'NONE';
$signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? 'NONE';

log_validation("Validation request: method=$method, event=$githubEvent, delivery=$githubDelivery");

echo "=== GitHub Webhook Validation ===\n\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "Method: $method\n";
echo "User Agent: $userAgent\n";
echo "GitHub Event: $githubEvent\n";
echo "GitHub Delivery: $githubDelivery\n";
echo "Signature: $signature\n\n";

if ($method !== 'POST') {
    echo "❌ ERROR: Expected POST request, got $method\n";
    log_validation("ERROR: Wrong method - $method");
    http_response_code(405);
    exit;
}

echo "✅ Method: POST (correct)\n";

if (strpos($userAgent, 'GitHub-Hookshot') === false) {
    echo "⚠️  WARNING: User agent doesn't contain 'GitHub-Hookshot'\n";
    log_validation("WARNING: Unexpected user agent - $userAgent");
} else {
    echo "✅ User Agent: GitHub webhook (correct)\n";
}

if ($githubEvent === 'NONE') {
    echo "❌ ERROR: No X-GitHub-Event header found\n";
    log_validation("ERROR: No GitHub event header");
} else {
    echo "✅ GitHub Event: $githubEvent\n";
    if ($githubEvent === 'push') {
        echo "✅ Event Type: push (correct for deployment)\n";
    } else {
        echo "ℹ️  Event Type: $githubEvent (not push - will be ignored by deploy script)\n";
    }
}

if ($githubDelivery === 'NONE') {
    echo "❌ ERROR: No X-GitHub-Delivery header found\n";
    log_validation("ERROR: No GitHub delivery header");
} else {
    echo "✅ GitHub Delivery ID: $githubDelivery\n";
}

$payload = file_get_contents('php://input');
echo "\nPayload Length: " . strlen($payload) . " bytes\n";

if (strlen($payload) === 0) {
    echo "❌ ERROR: No payload received\n";
    log_validation("ERROR: Empty payload");
} else {
    echo "✅ Payload: Received\n";
    
    $data = json_decode($payload, true);
    if (is_array($data)) {
        echo "✅ JSON: Valid\n";
        
        $ref = $data['ref'] ?? 'UNKNOWN';
        echo "Repository Ref: $ref\n";
        
        if ($ref === 'refs/heads/main') {
            echo "✅ Branch: main (correct for deployment)\n";
        } else {
            echo "ℹ️  Branch: not main - deployment will be skipped\n";
        }
        
        $repo = $data['repository']['full_name'] ?? 'UNKNOWN';
        echo "Repository: $repo\n";
        
        if ($repo === 'detect2173/GreatOwlSite') {
            echo "✅ Repository: correct\n";
        } else {
            echo "⚠️  Repository: unexpected\n";
        }
        
    } else {
        echo "❌ ERROR: Invalid JSON payload\n";
        echo "JSON Error: " . json_last_error_msg() . "\n";
        log_validation("ERROR: Invalid JSON - " . json_last_error_msg());
    }
}

if ($signature === 'NONE') {
    echo "\n❌ ERROR: No X-Hub-Signature-256 header found\n";
    echo "This indicates the webhook was not configured with a secret\n";
    log_validation("ERROR: No signature header");
} else {
    echo "\n✅ Signature: Present\n";
    if (strpos($signature, 'sha256=') === 0) {
        echo "✅ Signature Format: Correct (sha256=...)\n";
    } else {
        echo "❌ ERROR: Signature format incorrect (should start with 'sha256=')\n";
        log_validation("ERROR: Invalid signature format - $signature");
    }
}

echo "\n=== Recommendations ===\n";

if ($method === 'POST' && $githubEvent === 'push' && strlen($payload) > 0 && $signature !== 'NONE') {
    echo "✅ This webhook request looks valid for deployment!\n";
    echo "If deployment still fails, check:\n";
    echo "1. Deploy script exists at /home/greagfup/deploy/deploy.php\n";
    echo "2. Webhook secret matches deploy script exactly\n";
    echo "3. File permissions allow script execution\n";
    echo "4. Check deploy logs: /home/greagfup/deploy/deploy.log\n";
    log_validation("SUCCESS: Valid webhook request received");
} else {
    echo "❌ This webhook request has issues that will prevent deployment\n";
    echo "Fix the errors above and test again\n";
    log_validation("FAILURE: Invalid webhook request");
}

echo "\n=== Next Steps ===\n";
echo "1. Check validation log: /home/greagfup/deploy/webhook_validation.log\n";
echo "2. Test actual deployment: https://greatowlmarketing.com/hooks/deploy.php\n";
echo "3. Debug deployment: https://greatowlmarketing.com/hooks/test.php\n";