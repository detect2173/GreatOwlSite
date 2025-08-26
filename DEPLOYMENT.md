Deployment guide for Great Owl Marketing (Namecheap cPanel)

Goal
- Ensure every push to main updates the live site at /home/greagfup/public_html.
- The Git repository itself lives OUTSIDE public_html (recommended), so files are copied into public_html during deployment.

There are two supported deployment paths. Use only ONE to avoid confusion.

Option A — cPanel Git “Deploy on Update” (recommended, simplest)
1) Prerequisites
   - Repo URL: https://github.com/detect2173/GreatOwlSite.git
   - Branch: main
   - cPanel user: greagfup
   - Live docroot: /home/greagfup/public_html/
   - This repo contains .cpanel.yml which tells cPanel how to deploy (rsync to public_html).

2) cPanel setup steps
   - Log in to cPanel → Git Version Control → Create → Clone a Repository.
   - Repository Clone URL: https://github.com/detect2173/GreatOwlSite.git
   - Repository Path: /home/greagfup/gom_repo/GreatOwlSite (outside public_html)
   - Branch: main → Create/Clone.
   - Click Manage for this repo.
   - Ensure Checked-out Branch is main.
   - Enable “Deploy on Update” (or similar toggle). This makes cPanel auto-deploy when it receives an update.
   - Copy the cPanel-provided Webhook URL (shown in the Manage view). This is NOT the same as our public hooks endpoint.

3) Connect GitHub to cPanel’s Webhook
   - Go to GitHub → Repo → Settings → Webhooks → Add webhook.
   - Payload URL: paste the cPanel Webhook URL from above.
   - Content type: application/json
   - Secret: leave empty (unless cPanel provided a secret value to use).
   - Events: Just the push event → Add webhook.

4) First deployment
   - In cPanel → Git Version Control → Manage → click “Deploy HEAD” once to seed the first deploy.
   - After that, every push to main will trigger cPanel to pull and run .cpanel.yml:
     .cpanel.yml (already in repo):
       deployment:
         tasks:
           - export DEPLOYPATH=/home/greagfup/public_html/
           - /bin/rsync -avz --delete --exclude ".git" --exclude ".cpanel" ./ $DEPLOYPATH
   - Verify by pushing a small change (e.g., edit a comment in index.html) and checking the live site.

Option B — Custom GitHub Webhook → Server-side deploy script (advanced)
Use this only if Option A isn’t available in your cPanel.

1) Public endpoint (in this repo)
   - URL: https://greatowlmarketing.com/hooks/deploy.php
   - This file requires POST and then includes a private script outside public_html.

2) Private server script (NOT in repo)
   - Create /home/greagfup/deploy/deploy.php on the server by copying from this repo’s deploy/deploy.sample.php.
   - Set $SHARED_SECRET in that file to a long random string.
   - Ensure paths match your environment:
       $REPO_PATH   = '/home/greagfup/gom_repo/GreatOwlSite';
       $BRANCH      = 'main';
       $DEPLOY_PATH = '/home/greagfup/public_html/';
       $LOG_FILE    = '/home/greagfup/deploy/deploy.log';

3) GitHub Webhook (for your repo)
   - GitHub → Settings → Webhooks → Add webhook
   - Payload URL: https://greatowlmarketing.com/hooks/deploy.php
   - Content type: application/json
   - Secret: the SAME value you put in $SHARED_SECRET
   - Events: Just the push event

4) How it works
   - On push to main, GitHub POSTs to /hooks/deploy.php with a signed payload.
   - The private script verifies the signature and runs:
       git -C /home/greagfup/gom_repo/GreatOwlSite fetch --all --prune
       git -C /home/greagfup/gom_repo/GreatOwlSite reset --hard origin/main
       rsync -av --delete (with excludes) from the repo path to /home/greagfup/public_html/
   - Check /home/greagfup/deploy/deploy.log for details if anything fails.

Which one should I use?
- Prefer Option A (cPanel Deploy on Update). It’s simpler and uses the built-in cPanel webhook.
- Use Option B only if the cPanel webhook isn’t available or reliable in your plan.

Common pitfalls
- “Nothing updates on the live site”: Either Deploy on Update is OFF, your GitHub webhook isn’t hitting cPanel’s URL, or .cpanel.yml is missing/invalid.
- Webhook 404/405: Make sure you used the correct URL. /hooks/deploy.php only responds to POST (405 on GET).
- Permission errors: Rare on Namecheap. If rsync errors, open the log file noted above.
- Repo inside public_html: Avoid that; keep it outside and deploy via rsync.

Quick test checklist
- Enable Option A fully (toggle + webhook). Click Deploy HEAD once.
- Make a tiny edit to index.html (e.g., comment) → git add/commit/push to main.
- Watch cPanel Manage → Deployment log for rsync output.
- Hard refresh the live site; your change should appear.

Support
If you want to switch entirely to Option A, you can ignore the hooks/ and deploy/ sample files — they are safe to keep but are not used by cPanel’s own webhook.
