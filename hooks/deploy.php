<?php
// Public loader for GitHub webhook -> includes secure deploy script outside web root
// Path on server: /home/greagfup/public_html/hooks/deploy.php (after deployment)
// Private target: /home/greagfup/deploy/deploy.php

// For safety, restrict to POST (GitHub webhooks use POST). This also prevents casual GET access.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

// Include the secure script (must exist on the server)
$secureScript = '/home/greagfup/deploy/deploy.php';
if (file_exists($secureScript)) {
    require_once $secureScript;
    // The secure script will echo/exit; we return just in case
    return;
}

// Helpful guidance if the private script hasn't been created yet
http_response_code(500);
header('Content-Type: text/plain');
echo "Deploy script not found at $secureScript\n".
     "Create it on the server (outside public_html) using deploy/deploy.sample.php from this repo as a template.\n".
     "After creating it, set your webhook secret in that file and in GitHub Webhook settings.";
