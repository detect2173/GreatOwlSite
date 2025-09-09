# Project Guidelines

## 0. Modes & Scope Control:

- When approving the **first Git command** for a given change request, assume **implicit approval for all file writes/updates** related to that request.
- Junie should not re-prompt for every file touched within the same change context/branch.
- Auto-approval ends once the change set is either:
    - merged into `main`, or
    - explicitly cancelled by the user.

## 1. Repository & Branching

Main branch: main → always deployable/stable.

Feature branches: create a branch per feature/fix, e.g.

- feat/navigation-menu
- fix/mobile-footer
- style/hero-section
- docs/readme-update
- chore/gitignore-cleanup

Merge into main via Pull Requests (PRs). Always review before merging.

## 2. File & Folder Structure
```
Chatbot-Website/
│
├── index.html                # Landing page
├── privacy-policy.html
├── terms.html
├── smartbotpro-agreement.html
├── thank-you.html
├── gom-onboarding.html
│
├── styles/                   # CSS styles
│   └── main.css
│
├── assets/                   # images, logos, icons
│   └── Great-Owl-LogoTransparent.png
│
├── .idea/                    # WebStorm config (ignored in Git)
├── .vscode/                  # VSCode config (ignored in Git)
├── guidelines.md             # Project standards
└── README.md                 # Project overview
```

## 3. HTML Standards

- Use semantic HTML5 (header, section, footer, etc.).
- All `<img>` tags must include `alt` attributes.
- Indentation: 2 spaces (no tabs).
- External links must include `rel="noopener noreferrer"`.

## 4. CSS Standards

- Single source of truth → `styles/main.css`.
- Use CSS custom properties (`:root { --primary-color: … }`) for theme consistency.  
  Note: selector is `:root` (single colon), not `::root`.
- Prefer classes over IDs for styling.
- Mobile-first responsive design.
- Run code through **W3C CSS Validator** before merging.

Common section classes:

`.hero`, `.services`, `.features`, `.pricing`, `.contact`, `.footer`.

## 5. Commit Messages

Follow Conventional Commits:

- feat: add hero section animations
- fix: correct navbar logo link
- style: improve mobile spacing on pricing cards
- docs: update privacy policy wording
- chore: update .gitignore for WebStorm

## 6. Deployment

- Local testing: run via XAMPP or WebStorm’s built-in server.
- Remote deployment: auto-upload enabled in WebStorm (SFTP to server).
- All commits pushed to main should reflect a working, deployable version.
- External scripts should use explicit `https://` URLs (avoid protocol-relative `//` URLs).

## 7. Code Quality & Validation

- Validate HTML: W3C Validator.
- Validate CSS: W3C CSS Validator.
- Test in Chrome, Firefox, Edge, and on mobile viewports.

### Accessibility:
- Provide accessible names/labels for interactive controls (e.g., `aria-label` on icon-only buttons).
- Use semantic elements for buttons/links; prefer `<button type="button">` for actions.
- Maintain sufficient color contrast (aim for WCAG AA).

### Performance:
- Use `rel="preconnect"`/`rel="dns-prefetch"` for major third-party origins when helpful.
- Consider `rel="preload"` for critical fonts and above-the-fold assets.
- Optimize images (compression, modern formats where possible) and set width/height to reduce CLS.

### Caching/Versioning:
- Apply simple cache-busting for CSS/JS on deploy when needed (e.g., `styles/main.css?v=YYYYMMDD`).

## 8. Git Ignore Rules

In `.gitignore`:
```
.idea/
.vscode/
node_modules/
*.log
```

## 9. Pull Request Checklist

- [ ] HTML validated (W3C)
- [ ] CSS validated (W3C)
- [ ] Cross-browser tested (Chrome, Firefox, Edge) and mobile viewport
- [ ] External anchors with `target="_blank"` include `rel="noopener noreferrer"`
- [ ] External scripts use `https://` explicitly
- [ ] Assets stored under `assets/` and referenced accordingly
- [ ] Conventional Commit message used
- [ ] Cache-busting query applied if styles/scripts changed (optional)

## 10. Quick Compliance Checklist

### Structure
- [ ] `guidelines.md` maintained and discoverable in this project context
- [ ] `assets/` holds images and icons; HTML updated accordingly

### Git hygiene
- [ ] `.gitignore` contains IDE folders and common noise
- [ ] Accidental `.idea/` files removed from repo if committed previously

### HTML/CSS
- [ ] `:root` variable block present and valid (not `::root`)
- [ ] External anchors use `rel="noopener noreferrer"`
- [ ] HTML and CSS pass W3C validators

### Deployment
- [ ] External scripts use `https://` URLs
- [ ] Optional: versioning/caching applied to CSS/JS

### Process
- [ ] Feature/fix branches used and merged via PRs
- [ ] Conventional Commits enforced
- [ ] PR checklist completed before merge
