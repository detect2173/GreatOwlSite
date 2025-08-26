<?php
// Fallback: only redirect the root URL to index.html. Do not affect assets or subpaths.
$uri = $_SERVER['REQUEST_URI'] ?? '/';
if ($uri === '/' || $uri === '') {
    header('Location: /index.html', true, 302);
    exit;
}
// For non-root requests, do nothing so the server can serve files directly.
