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

// ---------- Enhanced Helpers ----------
function log_msg($m){
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $m\n";
    @file_put_contents($LOG_FILE, $logEntry, FILE_APPEND);
    
    // Also log to error log for cPanel visibility
    error_log("Webhook Deploy: $m");
}

function run_cmd($cmd){
    log_msg("RUN: $cmd");
    $out = [];$ret = 0;
    exec($cmd.' 2>&1', $out, $ret);
    $output = implode("\n", $out);
    log_msg("RET($ret):\n$output");
    
    if ($ret !== 0) {
        log_msg("ERROR: Command failed with exit code $ret");
    }
    
    return $ret === 0;
}

function validate_paths() {
    global $REPO_PATH, $DEPLOY_PATH, $LOG_FILE, $GIT_BIN, $RSYNC_BIN;
    
    $issues = [];
    
    if (!is_dir($REPO_PATH)) {
        $issues[] = "Repository path does not exist: $REPO_PATH";
    }
    
    if (!is_dir($DEPLOY_PATH)) {
        $issues[] = "Deploy path does not exist: $DEPLOY_PATH";
    }
    
    if (!is_dir(dirname($LOG_FILE))) {
        $issues[] = "Log directory does not exist: " . dirname($LOG_FILE);
    }
    
    if (!file_exists($GIT_BIN)) {
        $issues[] = "Git binary not found at: $GIT_BIN";
    }
    
    if (!file_exists($RSYNC_BIN)) {
        $issues[] = "Rsync binary not found at: $RSYNC_BIN";
    }
    
    return $issues;
}

// Log deployment start
log_msg("=== Webhook deployment started ===");
log_msg("PHP User: " . get_current_user());
log_msg("Working Directory: " . getcwd());
log_msg("Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));

// Validate configuration
$pathIssues = validate_paths();
if (!empty($pathIssues)) {
    log_msg("Configuration errors found:");
    foreach ($pathIssues as $issue) {
        log_msg("- $issue");
    }
    http_response_code(500);
    echo "Configuration errors - check deploy log";
    exit;
}

// ---------- Basic request checks ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_msg('Rejected non-POST request: ' . ($_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN'));
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$payload = file_get_contents('php://input');
$sig256  = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
$event   = $_SERVER['HTTP_X_GITHUB_EVENT'] ?? '';
$deliv   = $_SERVER['HTTP_X_GITHUB_DELIVERY'] ?? '';

log_msg("GitHub Headers - Event: $event, Delivery: $deliv");
log_msg("Payload length: " . strlen($payload));

if (!$sig256 || strpos($sig256, 'sha256=') !== 0) {
    log_msg("Missing or invalid signature header for delivery $deliv. Header: '$sig256'");
    http_response_code(400);
    echo 'Missing or invalid signature';
    exit;
}

$computed = 'sha256=' . hash_hmac('sha256', $payload, $SHARED_SECRET);
if (!hash_equals($computed, $sig256)) {
    log_msg("Signature mismatch for delivery $deliv");
    log_msg("Expected: $computed");
    log_msg("Received: $sig256");
    log_msg("Secret length: " . strlen($SHARED_SECRET));
    http_response_code(401);
    echo 'Invalid signature';
    exit;
}

$data = json_decode($payload, true);
if (!is_array($data)) {
    log_msg('Invalid JSON payload. JSON error: ' . json_last_error_msg());
    log_msg('Payload preview: ' . substr($payload, 0, 200));
    http_response_code(400);
    echo 'Bad payload';
    exit;
}

// Only deploy on push to the configured branch
$receivedRef = $data['ref'] ?? '';
$expectedRef = 'refs/heads/' . $BRANCH;

log_msg("Event type: $event");
log_msg("Received ref: $receivedRef");
log_msg("Expected ref: $expectedRef");

if ($receivedRef !== $expectedRef || $event !== 'push') {
    log_msg("Ignoring event/ref - not a push to $BRANCH branch");
    echo 'Ignored - not a push to main branch';
    exit;
}

log_msg("Webhook validation passed: delivery=$deliv branch=$BRANCH");

// Set environment variables for git operations
$homeDir = dirname($REPO_PATH);
putenv('HOME=' . $homeDir);
putenv('USER=' . get_current_user());
log_msg("Set HOME environment to: $homeDir");

// Check if repository exists and is a git repository
if (!is_dir($REPO_PATH . '/.git')) {
    log_msg("ERROR: Repository not found or not a git repository at: $REPO_PATH");
    http_response_code(500);
    echo 'Repository not found';
    exit;
}

// ---------- Deploy steps ----------
log_msg("Starting git fetch operation...");
if (!run_cmd("$GIT_BIN -C $REPO_PATH fetch --all --prune")) {
    log_msg("FATAL: git fetch failed");
    http_response_code(500); 
    echo 'git fetch failed - check deploy log'; 
    exit;
}

log_msg("Starting git reset operation...");
if (!run_cmd("$GIT_BIN -C $REPO_PATH reset --hard origin/$BRANCH")) {
    log_msg("FATAL: git reset failed");
    http_response_code(500); 
    echo 'git reset failed - check deploy log'; 
    exit;
}

// Verify the current commit after reset
if (run_cmd("$GIT_BIN -C $REPO_PATH rev-parse HEAD")) {
    // The output is already logged by run_cmd
}

// Build rsync excludes
$excludes = ' --exclude ".git" --exclude ".cpanel"';
foreach ($PRESERVE_DIRS as $d) { 
    $excludes .= ' --exclude ' . escapeshellarg($d); 
}

log_msg("Rsync excludes: $excludes");

// Create deploy path if it doesn't exist
if (!is_dir($DEPLOY_PATH)) {
    log_msg("Creating deploy directory: $DEPLOY_PATH");
    if (!mkdir($DEPLOY_PATH, 0755, true)) {
        log_msg("FATAL: Failed to create deploy directory: $DEPLOY_PATH");
        http_response_code(500);
        echo 'Failed to create deploy directory';
        exit;
    }
}

// Sync working tree to live docroot
log_msg("Starting rsync operation...");
$rsyncCmd = "$RSYNC_BIN -av --delete$excludes " . escapeshellarg($REPO_PATH . '/') . ' ' . escapeshellarg($DEPLOY_PATH);
if (!run_cmd($rsyncCmd)) {
    log_msg("FATAL: rsync failed");
    http_response_code(500); 
    echo 'rsync failed - check deploy log'; 
    exit;
}

log_msg('Deploy completed successfully');
log_msg("=== Webhook deployment finished ===");

header('Content-Type: text/plain');
echo "OK - Deploy completed successfully\n";
echo "Check deploy log for details: $LOG_FILE\n";
