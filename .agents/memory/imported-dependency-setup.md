---
name: Imported dependency setup
description: Avoid coupling Laravel setup to ancillary generated workspace packages.
---

Keep imported Laravel frontend dependency installation independent of unused workspace scaffolding.

**Why:** A whole-workspace pnpm installation failed on a firewall-blocked dependency in an ancillary API code-generation package. The package-management callback successfully installed the Laravel frontend's existing npm dependencies without requiring that unrelated package.

**How to apply:** For Laravel-only setup, use the package-management skill for dependency changes and retain the native npm lockfile and build scripts. Only install ancillary artifact dependencies when actually working on those artifacts. Do not bypass the package firewall.
