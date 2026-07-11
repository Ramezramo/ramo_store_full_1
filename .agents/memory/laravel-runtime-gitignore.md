---
name: Laravel runtime artifacts must be gitignored
description: Committed storage/framework (sessions/cache/views) or public/build files get flagged by completion review as security/noise issues; ensure they're gitignored on Laravel projects.
---

Some imported Laravel projects arrive with `storage/framework/cache/data/*`, `storage/framework/sessions/*`, `storage/framework/views/*`, and `public/build/*` (Vite build output) committed to git instead of gitignored. Running the app regenerates/modifies these files, which then show up as dirty working-tree changes.

**Why:** A completion code review rejected a task specifically because session payload files were being versioned (privacy/security risk) and because runtime cache/view artifacts are ephemeral and shouldn't be tracked.

**How to apply:** When setting up or verifying a Laravel project on Replit, check `git status` after running the app once. If `storage/framework/**` or `public/build/**` show as tracked/modified, add standard Laravel `.gitignore` entries for them (keep `.gitignore` placeholder files in each dir) and `git rm --cached` the previously committed files before considering setup done.
