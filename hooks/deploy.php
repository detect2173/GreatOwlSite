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

http_response_code(500);
echo 'Deploy script not found';
