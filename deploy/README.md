This directory contains a sample deployment script for GitHub webhooks.

## Important Setup Notes

- Do NOT place the real deploy.php here if this folder is inside public_html.
- Create the real script on the server at: `/home/greagfup/deploy/deploy.php` (outside public_html)
- Use deploy.sample.php as a template and set a strong $SHARED_SECRET that matches the GitHub webhook secret.
- The public endpoint is `/hooks/deploy.php` which includes the private script and only accepts POST.

## Troubleshooting Webhook Failures

### Step 1: Test Basic Connectivity
Visit: `https://greatowlmarketing.com/hooks/test.php` to check:
- File system paths and permissions
- Environment variables
- GitHub headers (when called from webhook)

### Step 2: Check Deploy Script Exists
Ensure `/home/greagfup/deploy/deploy.php` exists and is based on `deploy.sample.php`

### Step 3: Verify GitHub Webhook Configuration
- Payload URL: `https://greatowlmarketing.com/hooks/deploy.php`
- Content type: `application/json`
- Secret: Must match `$SHARED_SECRET` in deploy script
- Events: Just the `push` event
- SSL verification: Enabled

### Step 4: Check Logs
- Webhook requests: `/home/greagfup/deploy/webhook.log`
- Deploy operations: `/home/greagfup/deploy/deploy.log`
- cPanel error logs (if using cPanel webhooks)

### Common Issues and Solutions

1. **"Deploy script not found"**
   - Copy `deploy.sample.php` to `/home/greagfup/deploy/deploy.php`
   - Ensure the deploy directory exists and is writable

2. **"Invalid signature"**
   - Verify the secret in GitHub webhook matches `$SHARED_SECRET` exactly
   - Check that the webhook content type is `application/json`

3. **"git fetch/reset failed"**
   - Verify repository path in deploy script
   - Check git binary path (`/usr/bin/git`)
   - Ensure HOME environment variable is set correctly

4. **"rsync failed"**
   - Verify deploy path exists and is writable
   - Check rsync binary path (`/usr/bin/rsync`)
   - Review excluded directories configuration

5. **Webhook receives no response**
   - Check if the webhook URL is accessible
   - Verify server logs for PHP errors
   - Ensure the script has proper permissions

### Manual Testing

To test the deploy script manually:
```bash
# SSH to server
cd /home/greagfup/deploy
php -f deploy.php
```

### Alternative: cPanel Git Deploy (Recommended)

Consider using cPanel's built-in Git deployment instead:
1. Enable "Deploy on Update" in cPanel Git Version Control
2. Use the cPanel-provided webhook URL in GitHub
3. Let `.cpanel.yml` handle the deployment automatically
