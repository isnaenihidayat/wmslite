# Legacy `main.php` Plaintext Credentials — Backlog

**Origin:** go-live Phase 5 (Legacy Yii Cutover Plan), RESEARCH pass, 24-06-26.

## Finding

`protected/config/main.php` (committed to git, not gitignored) contains plaintext credentials:
- Line 39: MySQL `root` connection password.
- Lines 54-55: SMTP mail credentials.

No actual values are reproduced in this note — see the file/line references above.

## Why this is out of scope for go-live Phase 5

Phase 5's scope is proving the Yii→Laravel cutover mechanism locally for one module
(Monitoring). Rotating/relocating these credentials is a separate, pre-existing legacy
hardening task unrelated to the cutover mechanism itself.

## Recommendation

- Rotate the MySQL root password and SMTP credentials.
- Move both into environment variables (`protected/config/main-local.php`, which is already
  `.gitignore`d per Yii 1.x convention) instead of the committed `main.php`.

## Trade-off to consider before prioritizing

The Yii app this file belongs to is the legacy stack `go-live` is actively retiring module by
module (see `yii-production-cutover-elogid_NOTE_24-06-26.md`). Fixing this is a real security
improvement, but the underlying app has a planned end-of-life — weigh remediation effort against
how long the Yii app (and this file) will still be running before full retirement.

**Status:** Not started. Logged for awareness; not blocking any go-live phase.
