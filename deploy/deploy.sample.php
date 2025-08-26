<?php
// Template: Secure GitHub webhook deploy script for Namecheap cPanel
// Copy this file to the server as: /home/greagfup/deploy/deploy.php (outside public_html)
// Then replace the secret below with a long random string and set the SAME value in your GitHub Webhook.

// 1) Set a long random secret and use the SAME value in GitHub Webhook settings
$SHARED_SECRET = 'Chubsy Ubsy Rocks the Rascals';

// 2) Paths and tools (adjust if your paths differ)
$REPO_PATH   = '/home/greagfup/gom_repo/GreatOwlSite';  // cPanel Git Version Control repo path
$BRANCH      = 'main';
$DEPLOY_PATH = '/home/greagfup/public_html/';           // live site docroot
$LOG_FILE    = '/home/greagfup/deploy/deploy.log';

// Common binary paths on Namecheap/cPanel
$GIT_BIN   = '/usr/bin/git';
$RSYNC_BIN = '/usr/bin/rsync';

// Directories in public_html to preserve even when using --delete
$PRESERVE_DIRS = ['.well-known','cgi-bin'];

// ---------- Helpers ----------
function log_msg($m){
    global $LOG_FILE;
    @file_put_contents($LOG_FILE, '['.date('Y-m-d H:i:s')."] $m\n", FILE_APPEND);
}
function run_cmd($cmd){
    log_msg("RUN: $cmd");
    $out = [];$ret = 0;
    exec($cmd.' 2>&1', $out, $ret);
    log_msg("RET($ret):\n".implode("\n", $out));
    return $ret === 0;
}

// ---------- Basic request checks ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_msg('Rejected non-POST request');
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$payload = file_get_contents('php://input');
$sig256  = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$event   = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
$deliv   = $_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? '';

if (!$sig256 || strpos($sig256, 'sha256=') !== 0) {
    log_msg("Missing signature header for delivery $deliv");
    echo 'Missing signature';
    exit;
}

$computed = 'sha256=' . hash_hmac('sha256', $payload, $SHARED_SECRET);
if (!hash_equals($computed, $sig256)) {
    log_msg("Signature mismatch for delivery $deliv");
    echo 'Invalid signature';
    exit;
}

$data = json_decode($payload, true);
if (!is_array($data)) {
    log_msg('Invalid JSON payload');
    echo 'Bad payload';
    exit;
}

// Only deploy on push to the configured branch
if (($data['ref'] ?? '') !== ('refs/heads/' . $BRANCH) || $event !== 'push') {
    log_msg('Ignoring event/ref: event=' . $event . ' ref=' . ($data['ref'] ?? ''));
    echo 'Ignored';
    exit;
}

log_msg("Webhook OK: delivery=$deliv branch=$BRANCH");

// Some git operations need HOME set
putenv('HOME=' . dirname($REPO_PATH));

// ---------- Deploy steps ----------
if (!run_cmd("$GIT_BIN -C $REPO_PATH fetch --all --prune")) {
    http_response_code(500); echo 'git fetch failed'; exit;
}
if (!run_cmd("$GIT_BIN -C $REPO_PATH reset --hard origin/$BRANCH")) {
    http_response_code(500); echo 'git reset failed'; exit;
}

// Build rsync excludes
$excludes = ' --exclude ".git" --exclude ".cpanel"';
foreach ($PRESERVE_DIRS as $d) { $excludes .= ' --exclude ' . escapeshellarg($d); }

// Sync working tree to live docroot
if (!run_cmd("$RSYNC_BIN -av --delete$excludes " . escapeshellarg($REPO_PATH . '/') . ' ' . escapeshellarg($DEPLOY_PATH))) {
    http_response_code(500); echo 'rsync failed'; exit;
}

log_msg('Deploy completed successfully');
header('Content-Type: text/plain');
echo "OK\n";
