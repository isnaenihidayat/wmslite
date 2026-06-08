#!/usr/bin/env bash
# =============================================================================
# auto-push.sh — WMS Lite: Quick Auto Commit & Push
# =============================================================================
# Untuk commit biasa sehari-hari (bukan akhir sprint).
# Untuk akhir sprint + changelog, gunakan: bash scripts/sprint-done.sh
#
# USAGE:
#   bash scripts/auto-push.sh                    → auto commit pesan otomatis
#   bash scripts/auto-push.sh "feat: tambah X"  → commit dengan pesan custom
#   bash scripts/auto-push.sh --watch            → mode watch (auto tiap save)
#   bash scripts/auto-push.sh --status           → cek status saja
# =============================================================================

set -euo pipefail

REPO_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BRANCH="main"
REMOTE="origin"
REMOTE_URL="https://github.com/isnaenihidayat/wmslite.git"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; RESET='\033[0m'

log_info()    { echo -e "${BLUE}ℹ${RESET}  $*"; }
log_success() { echo -e "${GREEN}✓${RESET}  $*"; }
log_warn()    { echo -e "${YELLOW}⚠${RESET}  $*"; }
log_error()   { echo -e "${RED}✗${RESET}  $*" >&2; }
log_step()    { echo -e "\n${BOLD}${CYAN}▶ $*${RESET}"; }

print_banner() {
  echo -e "${BOLD}${CYAN}"
  echo "╔═══════════════════════════════════════════════╗"
  echo "║   WMS Lite — Auto Commit & Push               ║"
  echo "║   → github.com/isnaenihidayat/wmslite         ║"
  echo "╚═══════════════════════════════════════════════╝"
  echo -e "${RESET}"
}

verify_git() {
  cd "$REPO_DIR"
  if ! git rev-parse --git-dir &>/dev/null; then
    log_error "Bukan direktori git: $REPO_DIR"
    exit 1
  fi
  if ! git remote get-url "$REMOTE" &>/dev/null; then
    git remote add "$REMOTE" "$REMOTE_URL"
    log_info "Remote '$REMOTE' ditambahkan"
  fi
}

do_push() {
  if ! git rev-parse --abbrev-ref --symbolic-full-name "@{u}" &>/dev/null 2>&1; then
    log_step "Push pertama (setup upstream)..."
    git push -u "$REMOTE" "$BRANCH" 2>&1 || {
      log_warn "Pull dulu sebelum push..."
      git pull "$REMOTE" "$BRANCH" --allow-unrelated-histories --no-edit 2>&1 || true
      git push -u "$REMOTE" "$BRANCH"
    }
  else
    git push "$REMOTE" "$BRANCH"
  fi
}

do_commit_push() {
  local custom_msg="${1:-}"
  cd "$REPO_DIR"

  # Cek perubahan
  if git diff --quiet && git diff --cached --quiet && \
     [ -z "$(git ls-files --others --exclude-standard)" ]; then
    log_warn "Tidak ada perubahan untuk di-commit."
    return 0
  fi

  log_step "Status perubahan:"
  git status --short

  git add -A

  # Auto generate commit message
  if [ -z "$custom_msg" ]; then
    local timestamp file_count changed_files branch_name
    timestamp=$(date '+%Y-%m-%d %H:%M')
    file_count=$(git diff --cached --name-only | wc -l | tr -d ' ')
    changed_files=$(git diff --cached --name-only | head -5 | tr '\n' ', ' | sed 's/,$//')
    branch_name=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "main")

    # Detect dominant change type from file paths
    local dominant_type="chore"
    if git diff --cached --name-only | grep -q "apps/frontend/src"; then
      dominant_type="feat"
    fi
    if git diff --cached --name-only | grep -q "\.md$"; then
      dominant_type="docs"
    fi
    if git diff --cached --name-only | grep -q "scripts/"; then
      dominant_type="build"
    fi

    if [ "$file_count" -le 5 ]; then
      custom_msg="${dominant_type}: update ${changed_files} [${timestamp}]"
    else
      custom_msg="${dominant_type}: update ${file_count} file — ${timestamp}"
    fi
  fi

  log_step "Commit..."
  git commit -m "$custom_msg"
  log_success "\"$custom_msg\""

  log_step "Push ke GitHub (branch: $BRANCH)..."
  do_push
  log_success "Push berhasil → $REMOTE_URL"
}

do_status() {
  cd "$REPO_DIR"
  echo -e "\n${BOLD}📊 Git Status WMS Lite${RESET}"
  echo -e "${DIM}─────────────────────────${RESET}"
  git status
  echo ""
  echo -e "${BOLD}📝 5 Commit Terakhir:${RESET}"
  git log --oneline -5 2>/dev/null || echo "  (belum ada commit)"
  echo ""
  echo -e "${BOLD}🔖 Tag Terakhir:${RESET}"
  git describe --tags --abbrev=0 2>/dev/null || echo "  (belum ada tag)"
}

do_watch() {
  if ! command -v fswatch &>/dev/null; then
    log_error "fswatch tidak ditemukan. Install: brew install fswatch"
    exit 1
  fi

  log_step "Mode Watch aktif — memantau: $REPO_DIR"
  log_info "Tekan Ctrl+C untuk berhenti\n"

  local last_commit=0
  fswatch -r \
    --exclude="\\.git/" \
    --exclude="node_modules/" \
    --exclude="vendor/" \
    --exclude="\\.DS_Store" \
    --exclude="\\.next/" \
    --exclude="storage/logs/" \
    "$REPO_DIR" | while read -r _event; do
    local now diff
    now=$(date +%s)
    diff=$(( now - last_commit ))
    if [ "$diff" -ge 15 ]; then
      last_commit=$now
      log_info "Perubahan terdeteksi, auto-commit..."
      do_commit_push "" 2>/dev/null || log_warn "Commit gagal, akan dicoba lagi..."
    fi
  done
}

# ── ANSI tambahan ─────────────────────────────────────────────────────────────
DIM='\033[2m'

# ── Main ──────────────────────────────────────────────────────────────────────
print_banner
verify_git

case "${1:-}" in
  --watch|-w)
    do_watch
    ;;
  --status|-s)
    do_status
    ;;
  --help|-h)
    echo "USAGE:"
    echo "  bash scripts/auto-push.sh                   — auto commit & push"
    echo "  bash scripts/auto-push.sh \"feat: pesan\"     — commit dengan pesan"
    echo "  bash scripts/auto-push.sh --watch           — mode watch"
    echo "  bash scripts/auto-push.sh --status          — cek status git"
    echo ""
    echo "Untuk akhir sprint (dengan changelog):"
    echo "  bash scripts/sprint-done.sh \"Sprint 1.2\""
    ;;
  *)
    do_commit_push "${1:-}"
    ;;
esac
