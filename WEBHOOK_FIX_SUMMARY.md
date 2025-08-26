# Webhook Deployment Fix Summary

## Problem
The webhook deployment system was failing without clear error reporting, making it difficult to diagnose issues.

## Solutions Implemented

### 1. Enhanced Error Reporting
- **hooks/deploy.php**: Added comprehensive logging to `/home/greagfup/deploy/webhook.log`
- **deploy.sample.php**: Improved error messages with specific failure reasons
- All scripts now provide detailed debug information

### 2. New Debugging Tools
- **hooks/test.php**: System diagnostics endpoint
  - Checks file paths, permissions, environment variables
  - Validates GitHub webhook headers
  - Reports file system status
  
- **hooks/validate.php**: Webhook validation endpoint  
  - Step-by-step webhook validation
  - Identifies specific issues with webhook requests
  - Provides recommendations for fixes

### 3. Improved Deployment Scripts
- **Path validation**: Checks all required paths before deployment
- **Better git operations**: Enhanced error handling for fetch/reset
- **Rsync improvements**: Better exclude handling and error reporting
- **Environment setup**: Proper HOME/USER variables for git operations

### 4. Enhanced cPanel Configuration
- **.cpanel.yml**: Added logging and preserved important directories
- Better error feedback during cPanel deployments

## How to Debug Webhook Issues

### Step 1: System Check
Visit: `https://greatowlmarketing.com/hooks/test.php`
- Verifies file paths exist
- Checks permissions
- Shows environment variables
- Reports git/rsync binary locations

### Step 2: Webhook Validation  
Visit: `https://greatowlmarketing.com/hooks/validate.php`
- Validates GitHub webhook format
- Checks headers and payload
- Identifies signature issues
- Provides specific recommendations

### Step 3: Check Logs
- Webhook requests: `/home/greagfup/deploy/webhook.log`
- Deploy operations: `/home/greagfup/deploy/deploy.log`  
- Validation tests: `/home/greagfup/deploy/webhook_validation.log`

### Step 4: Verify Setup
1. Ensure `/home/greagfup/deploy/deploy.php` exists (copy from `deploy.sample.php`)
2. Set correct `$SHARED_SECRET` matching GitHub webhook
3. Verify all paths in deploy script match server environment
4. Test GitHub webhook configuration

## Common Issues Fixed

1. **"Deploy script not found"**
   - Now provides exact path and setup instructions
   - Logs detailed diagnostic information

2. **"Invalid signature"**  
   - Shows expected vs received signatures
   - Validates secret configuration
   - Provides troubleshooting steps

3. **Git/rsync failures**
   - Validates paths before operations
   - Better error reporting with specific failure reasons
   - Proper environment variable setup

4. **Permission issues**
   - Reports file system permissions
   - Shows current PHP user and working directory
   - Validates binary locations

## Next Steps

1. **Test the system**: Push this commit and check if webhook deploys correctly
2. **Check diagnostics**: Use the new test endpoints if issues persist  
3. **Review logs**: Use the enhanced logging to identify specific problems
4. **Update deploy script**: Copy `deploy.sample.php` to server if needed

## Deployment Options

The repository supports two deployment methods:

### Option A: cPanel Git Deploy (Recommended)
- Uses `.cpanel.yml` for automatic deployment
- Simpler setup, uses cPanel's built-in webhook
- Enhanced with better logging and error handling

### Option B: Custom Webhook Script
- Uses `hooks/deploy.php` → `/home/greagfup/deploy/deploy.php`
- More control, custom error handling
- Now includes comprehensive debugging tools

Choose the option that works best with your hosting setup.