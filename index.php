<?php
// Fallback to ensure homepage loads when DirectoryIndex isn't applied
// Some shared hosts deny reading .htaccess; this redirect forces index.html.
header('Location: /index.html', true, 302);
exit;
