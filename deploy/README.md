This directory contains a sample deployment script for GitHub webhooks.

Important:
- Do NOT place the real deploy.php here if this folder is inside public_html.
- Create the real script on the server at: /home/greagfup/deploy/deploy.php (outside public_html)
- Use deploy.sample.php as a template and set a strong $SHARED_SECRET that matches the GitHub webhook secret.
- The public endpoint is /hooks/deploy.php which includes the private script and only accepts POST.
