---
name: Replit client IP detection
description: Trust configuration for visitor IP detection behind Replit's web ingress.
---

On this Replit-hosted Laravel app, the ingress proxy forwards the visitor's original IP in `X-Forwarded-For`. Laravel must trust the immediate connection peer to resolve that header as the client IP.

**Why:** Replit's official guidance says to trust the proxy hop. Leaving trusted proxies unset made Laravel resolve the proxy address, so country lookup missed the visitor's actual country. Trusting all proxies would broaden trust unnecessarily.

**How to apply:** Use `REMOTE_ADDR` as the single immediate proxy in this Replit deployment. For a different deployment topology, set `TRUSTED_PROXIES` to that deployment's explicit ingress addresses or CIDRs.
