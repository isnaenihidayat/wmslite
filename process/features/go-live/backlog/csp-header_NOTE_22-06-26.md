---
name: note:csp-header
description: "Content-Security-Policy header deferred — needs dedicated testing against rendered pages, risk of breaking Next.js inline hydration"
date: 22-06-26
metadata:
  node_type: memory
  type: note
  feature: go-live
---

# Backlog: Content-Security-Policy (CSP) Header

**Found during:** go-live Phase 3 (Security & Validation Audit), Section 8
**Source:** `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md`

**Target phase:** unscheduled — needs dedicated testing time, not a quick add-on to Phase 3

---

## Problem

Phase 3 Section 8 added 3 basic security response headers (`X-Frame-Options`,
`X-Content-Type-Options`, `Referrer-Policy`) to both the Laravel backend (`SecurityHeaders`
middleware) and the Next.js frontend (`next.config.ts` `headers()`). A `Content-Security-Policy`
(CSP) header was explicitly NOT added in this pass.

## Why this was deferred (not fixed in Phase 3)

CSP is materially riskier to add than the 3 basic headers:

- A misconfigured CSP can silently break Next.js's inline script hydration, App Router streaming,
  or any inline `<style>`/`<script>` tags the framework or its dependencies (e.g. Tailwind v4's
  runtime, shadcn/ui, recharts) inject at runtime — this would surface as a broken or blank page in
  the browser, not a test failure, since PHPUnit/Vitest do not render real DOM/CSS/JS execution.
- Getting CSP right requires testing against actual rendered pages in a real browser (or at minimum
  a browser-automation tool), not just asserting a header string is present — the header being
  present with an overly strict policy is worse than no header, since it would block legitimate
  app functionality.
- `apps/frontend/AGENTS.md` already flags that Next.js 16 / React 19 diverge from training data in
  ways that may not be obvious — CSP directives that worked for older Next.js versions (e.g. around
  `nonce` handling for inline scripts) may not apply identically here without verification.

Rushing CSP into Phase 3 risked introducing a production-breaking regression with no fast way to
catch it pre-deploy, for a Medium-severity finding — not worth the risk under this phase's time
budget.

## Recommendation

When this is picked up:

1. Add a permissive starter CSP (e.g. `default-src 'self'`) behind a `Content-Security-Policy-Report-Only`
   header first, not the enforcing header — this surfaces violations in the browser console without
   breaking anything.
2. Manually exercise every page/module in a real browser with the report-only header active and
   confirm no console CSP violations.
3. Only after a clean report-only pass, switch to the enforcing `Content-Security-Policy` header.
4. Add an automated check via `vc-chrome-devtools` or `vc-agent-browser` (browser automation) that
   loads each major route and asserts no CSP violation appears in the console — a `grep`-style
   header-presence test alone (the pattern used for the 3 basic headers in Phase 3) is not sufficient
   proof for CSP, precisely because the risk is behavioral (broken hydration), not header-presence.

## Related

- `apps/backend/app/Http/Middleware/SecurityHeaders.php` (Phase 3) — the 3 basic headers that WERE
  added; CSP would likely live in the same middleware once implemented
- `apps/frontend/next.config.ts` (Phase 3) — the frontend-side `headers()` config for the same 3
  basic headers
- `apps/frontend/AGENTS.md` — Next.js 16 / React 19 divergence-from-training-data caution, relevant
  to any future CSP nonce/inline-script handling
- `process/features/go-live/active/go-live_19-06-26/phase-03-security-audit_PLAN_19-06-26.md` —
  Section 8 (Basic security headers), item 8e
