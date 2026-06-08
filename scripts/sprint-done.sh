#!/usr/bin/env bash
# =============================================================================
# sprint-done.sh — WMS Lite: Sprint Selesai → Generate Changelog + Push GitHub
# =============================================================================
#
# USAGE:
#   bash scripts/sprint-done.sh                      → interaktif (pilih sprint)
#   bash scripts/sprint-done.sh "Sprint 1.1"         → langsung set sprint label
#   bash scripts/sprint-done.sh --push-only           → skip changelog, push saja
#   bash scripts/sprint-done.sh --changelog-only      → generate changelog tanpa push
#   bash scripts/sprint-done.sh --help                → tampilkan bantuan
#
# =============================================================================

set -euo pipefail

# ── Konfigurasi ──────────────────────────────────────────────────────────────
REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BRANCH="main"
REMOTE="origin"
REMOTE_URL="https://github.com/isnaenihidayat/wmslite.git"
CHANGELOG_FILE="$REPO_DIR/CHANGELOG.md"
SCRIPTS_DIR="$REPO_DIR/scripts"

# ── Warna terminal ────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; MAGENTA='\033[0;35m'
BOLD='\033[1m'; DIM='\033[2m'; RESET='\033[0m'

log_info()    { echo -e "${BLUE}ℹ${RESET}  $*"; }
log_success() { echo -e "${GREEN}✓${RESET}  $*"; }
log_warn()    { echo -e "${YELLOW}⚠${RESET}  $*"; }
log_error()   { echo -e "${RED}✗${RESET}  $*" >&2; }
log_step()    { echo -e "\n${BOLD}${CYAN}▶ $*${RESET}"; }
log_done()    { echo -e "${GREEN}${BOLD}✅ $*${RESET}"; }

# ── Banner ────────────────────────────────────────────────────────────────────
print_banner() {
  echo -e "${BOLD}${CYAN}"
  echo "╔══════════════════════════════════════════════════╗"
  echo "║   WMS Lite — Sprint Done Script                  ║"
  echo "║   → github.com/isnaenihidayat/wmslite            ║"
  echo "╚══════════════════════════════════════════════════╝"
  echo -e "${RESET}"
}

# ── Verifikasi git repo ────────────────────────────────────────────────────────
verify_git() {
  cd "$REPO_DIR"
  if ! git rev-parse --git-dir &>/dev/null; then
    log_error "Bukan direktori git: $REPO_DIR"
    exit 1
  fi

  # Pastikan remote ada
  if ! git remote get-url "$REMOTE" &>/dev/null; then
    log_warn "Remote '$REMOTE' tidak ditemukan. Menambahkan..."
    git remote add "$REMOTE" "$REMOTE_URL"
    log_success "Remote ditambahkan: $REMOTE_URL"
  fi
}

# ── Ensure .gitignore entries ─────────────────────────────────────────────────
ensure_gitignore() {
  local gitignore="$REPO_DIR/.gitignore"
  local entries=(
    "apps/frontend/.next/"
    "apps/frontend/node_modules/"
    "apps/frontend/.env.local"
    "apps/backend/vendor/"
    "apps/backend/.env"
    ".DS_Store"
    "protected/runtime/"
  )
  local changed=false
  for pattern in "${entries[@]}"; do
    if ! grep -qxF "$pattern" "$gitignore" 2>/dev/null; then
      echo "$pattern" >> "$gitignore"
      changed=true
    fi
  done
  $changed && log_info ".gitignore diperbarui"
}

# ── Generate Changelog (panggil script Node.js) ───────────────────────────────
run_changelog_generator() {
  local sprint_label="${1:-}"
  log_step "Menjalankan changelog generator..."

  if [ ! -f "$SCRIPTS_DIR/generate-changelog.mjs" ]; then
    log_error "Script generate-changelog.mjs tidak ditemukan di $SCRIPTS_DIR"
    exit 1
  fi

  if [ -n "$sprint_label" ]; then
    SPRINT_LABEL="$sprint_label" node "$SCRIPTS_DIR/generate-changelog.mjs"
  else
    node "$SCRIPTS_DIR/generate-changelog.mjs"
  fi
}

# ── Commit & Push ─────────────────────────────────────────────────────────────
do_commit_push() {
  local sprint_label="${1:-}"
  local timestamp
  timestamp=$(date '+%Y-%m-%d %H:%M WIB')

  cd "$REPO_DIR"

  # Cek ada perubahan
  if git diff --quiet && git diff --cached --quiet && [ -z "$(git ls-files --others --exclude-standard)" ]; then
    log_warn "Tidak ada perubahan untuk di-commit."
    return 0
  fi

  log_step "Status perubahan:"
  git status --short

  # Stage semua
  git add -A

  # Hitung stats
  local file_count changed_files
  file_count=$(git diff --cached --name-only | wc -l | tr -d ' ')
  changed_files=$(git diff --cached --name-only | head -5 | tr '\n' ', ' | sed 's/,$//')

  # Buat commit message
  local commit_msg
  if [ -n "$sprint_label" ]; then
    commit_msg="chore(sprint): selesai ${sprint_label} — ${file_count} file diubah [${timestamp}]"
  elif [ "$file_count" -le 5 ]; then
    commit_msg="chore: update ${file_count} file (${changed_files}) — ${timestamp}"
  else
    commit_msg="chore: update ${file_count} file — ${timestamp}"
  fi

  log_step "Membuat commit..."
  git commit -m "$commit_msg"
  log_success "Commit: \"$commit_msg\""

  # Set branch tracking jika belum
  if ! git rev-parse --abbrev-ref --symbolic-full-name "@{u}" &>/dev/null 2>&1; then
    log_step "Setup upstream branch..."
    git push -u "$REMOTE" "$BRANCH" 2>&1 || {
      log_warn "Push pertama gagal. Coba pull dulu..."
      git pull "$REMOTE" "$BRANCH" --allow-unrelated-histories --no-edit 2>&1 || true
      git push -u "$REMOTE" "$BRANCH"
    }
  else
    log_step "Push ke GitHub (branch: $BRANCH)..."
    git push "$REMOTE" "$BRANCH"
  fi

  log_done "Push berhasil → $REMOTE_URL"
}

# ── Mode: push only ───────────────────────────────────────────────────────────
push_only() {
  verify_git
  ensure_gitignore
  do_commit_push ""
}

# ── Mode: changelog only ──────────────────────────────────────────────────────
changelog_only() {
  verify_git
  run_changelog_generator "${1:-}"
  log_done "Changelog selesai. Jalankan 'bash scripts/sprint-done.sh --push-only' untuk push."
}

# ── Mode: full sprint done ────────────────────────────────────────────────────
sprint_done() {
  local sprint_label="${1:-}"
  verify_git
  ensure_gitignore

  # 1. Generate changelog
  run_changelog_generator "$sprint_label"

  # 2. Commit + push (termasuk changelog yang baru digenerate)
  do_commit_push "$sprint_label"

  echo ""
  echo -e "${GREEN}${BOLD}"
  echo "╔══════════════════════════════════════════════════╗"
  if [ -n "$sprint_label" ]; then
    printf "║  ✅  %-44s ║\n" "${sprint_label} selesai & dipush!"
  else
    echo "║  ✅  Sprint selesai & berhasil dipush!           ║"
  fi
  echo "║  🔗  github.com/isnaenihidayat/wmslite           ║"
  echo "╚══════════════════════════════════════════════════╝"
  echo -e "${RESET}"
}

# ── Auto-push (no changelog, untuk commit biasa) ─────────────────────────────
auto_push() {
  local custom_msg="${1:-}"
  verify_git
  ensure_gitignore

  cd "$REPO_DIR"
  if git diff --quiet && git diff --cached --quiet && [ -z "$(git ls-files --others --exclude-standard)" ]; then
    log_warn "Tidak ada perubahan untuk di-commit."
    exit 0
  fi

  log_step "Status perubahan:"
  git status --short

  git add -A

  local file_count changed_files timestamp
  timestamp=$(date '+%Y-%m-%d %H:%M WIB')
  file_count=$(git diff --cached --name-only | wc -l | tr -d ' ')
  changed_files=$(git diff --cached --name-only | head -5 | tr '\n' ', ' | sed 's/,$//')

  if [ -z "$custom_msg" ]; then
    if [ "$file_count" -le 5 ]; then
      custom_msg="chore: update ${file_count} file (${changed_files}) — ${timestamp}"
    else
      custom_msg="chore: update ${file_count} file — ${timestamp}"
    fi
  fi

  log_step "Commit..."
  git commit -m "$custom_msg"
  log_success "\"$custom_msg\""

  log_step "Push ke GitHub..."
  if ! git rev-parse --abbrev-ref --symbolic-full-name "@{u}" &>/dev/null 2>&1; then
    git push -u "$REMOTE" "$BRANCH" 2>&1 || {
      git pull "$REMOTE" "$BRANCH" --allow-unrelated-histories --no-edit 2>&1 || true
      git push -u "$REMOTE" "$BRANCH"
    }
  else
    git push "$REMOTE" "$BRANCH"
  fi

  log_done "Push berhasil → $REMOTE_URL"
}

# ── Help ──────────────────────────────────────────────────────────────────────
show_help() {
  echo -e "${BOLD}WMS Lite Sprint Done Script${RESET}"
  echo ""
  echo -e "${CYAN}USAGE:${RESET}"
  echo "  bash scripts/sprint-done.sh                    → sprint selesai (interaktif)"
  echo "  bash scripts/sprint-done.sh \"Sprint 1.2\"       → sprint dengan label"
  echo "  bash scripts/sprint-done.sh --push-only        → push perubahan tanpa changelog"
  echo "  bash scripts/sprint-done.sh --push \"msg\"       → push dengan pesan custom"
  echo "  bash scripts/sprint-done.sh --changelog-only   → generate changelog tanpa push"
  echo "  bash scripts/sprint-done.sh --help             → tampilkan bantuan ini"
  echo ""
  echo -e "${CYAN}CONTOH WORKFLOW SPRINT:${RESET}"
  echo "  1. Selesaikan pekerjaan sprint"
  echo "  2. Jalankan: bash scripts/sprint-done.sh \"Sprint 1.2\""
  echo "  3. Ikuti prompt changelog interaktif"
  echo "  4. Script otomatis commit + push ke GitHub"
}

# ── Main ──────────────────────────────────────────────────────────────────────
print_banner

case "${1:-}" in
  --push-only|-p)
    push_only
    ;;
  --push)
    auto_push "${2:-}"
    ;;
  --changelog-only|-c)
    changelog_only "${2:-}"
    ;;
  --help|-h)
    show_help
    ;;
  --*)
    log_error "Argumen tidak dikenal: ${1}"
    show_help
    exit 1
    ;;
  *)
    sprint_done "${1:-}"
    ;;
esac
