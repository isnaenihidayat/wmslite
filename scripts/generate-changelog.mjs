#!/usr/bin/env node
/**
 * ─────────────────────────────────────────────────────────────────────────────
 * generate-changelog.mjs — WMS Lite Changelog Generator
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * USAGE:
 *   node scripts/generate-changelog.mjs
 *   SPRINT_LABEL="Sprint 1.2" node scripts/generate-changelog.mjs
 *   AUTO=1 node scripts/generate-changelog.mjs  (fully non-interactive)
 *
 * OUTPUT:
 *   Prepends new entry ke CHANGELOG.md di root project
 *
 * CONVENTIONAL COMMITS yang dikenali:
 *   feat:     → ✨ Fitur Baru
 *   fix:      → 🐛 Bug Fix
 *   perf:     → ⚡ Performa
 *   refactor: → ♻️  Refactor
 *   chore:    → 🔧 Maintenance
 *   docs:     → 📝 Dokumentasi
 *   style:    → 💄 Tampilan
 *   test:     → 🧪 Testing
 *   build:    → 📦 Build
 *   ci:       → 🚀 CI/CD
 *   security: → 🔒 Keamanan
 *   sprint:   → 🏁 Sprint
 * ─────────────────────────────────────────────────────────────────────────────
 */

import { execSync }        from 'node:child_process'
import { readFileSync, writeFileSync, existsSync } from 'node:fs'
import { createInterface } from 'node:readline'
import { resolve, dirname } from 'node:path'
import { fileURLToPath }   from 'node:url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const ROOT      = resolve(__dirname, '..')
const CHANGELOG = resolve(ROOT, 'CHANGELOG.md')

// ── Commit type config ────────────────────────────────────────────────────────
const TYPE_MAP = {
  feat:     { emoji: '✨', label: 'Fitur Baru',    section: 'Added' },
  add:      { emoji: '✨', label: 'Fitur Baru',    section: 'Added' },
  fix:      { emoji: '🐛', label: 'Bug Fix',       section: 'Fixed' },
  perf:     { emoji: '⚡', label: 'Performa',      section: 'Changed' },
  refactor: { emoji: '♻️ ', label: 'Refactor',      section: 'Changed' },
  chore:    { emoji: '🔧', label: 'Maintenance',   section: 'Maintenance' },
  docs:     { emoji: '📝', label: 'Dokumentasi',   section: 'Documentation' },
  style:    { emoji: '💄', label: 'Tampilan/UI',   section: 'Changed' },
  test:     { emoji: '🧪', label: 'Testing',       section: 'Testing' },
  build:    { emoji: '📦', label: 'Build/Tooling', section: 'Build' },
  ci:       { emoji: '🚀', label: 'CI/CD',         section: 'Build' },
  security: { emoji: '🔒', label: 'Keamanan',      section: 'Security' },
  sprint:   { emoji: '🏁', label: 'Sprint',        section: 'Sprint' },
  revert:   { emoji: '⏪', label: 'Revert',        section: 'Reverted' },
}
const DEFAULT_TYPE = { emoji: '🔄', label: 'Update', section: 'Changed' }
const SECTION_ORDER = ['Sprint', 'Added', 'Fixed', 'Changed', 'Security', 'Documentation', 'Testing', 'Build', 'Maintenance', 'Reverted']

// ── Helpers ───────────────────────────────────────────────────────────────────
const rl = createInterface({ input: process.stdin, output: process.stdout })
const ask = (q) => new Promise(r => rl.question(q, r))
const isAuto = process.env.AUTO === '1'
const askOrAuto = async (q, fallback) => {
  if (isAuto) { process.stdout.write(q + fallback + '\n'); return fallback }
  return ask(q)
}

function run(cmd, opts = {}) {
  try { return execSync(cmd, { cwd: ROOT, encoding: 'utf8', ...opts }).trim() }
  catch { return '' }
}

// ── Parse conventional commit ─────────────────────────────────────────────────
function parseCommitType(subject) {
  // Pattern: "type(scope): description" or "type: description"
  const match = subject.match(/^(\w+)(?:\([\w/-]+\))?!?\s*:\s*(.+)/)
  if (match) {
    const type  = match[1].toLowerCase()
    const desc  = match[2]
    const info  = TYPE_MAP[type] || DEFAULT_TYPE
    return { type, desc, ...info }
  }
  // Fallback: detect from keywords
  const s = subject.toLowerCase()
  if (/^feat|tambah|add |fitur/i.test(subject))    return { type:'feat',   desc: subject, ...TYPE_MAP.feat }
  if (/^fix|perbaik|bug/i.test(subject))            return { type:'fix',    desc: subject, ...TYPE_MAP.fix }
  if (/^security|keamanan/i.test(subject))          return { type:'security',desc: subject, ...TYPE_MAP.security }
  if (/^style|tampilan|ui/i.test(subject))          return { type:'style',  desc: subject, ...TYPE_MAP.style }
  if (/^docs|dokumentasi/i.test(subject))           return { type:'docs',   desc: subject, ...TYPE_MAP.docs }
  if (/^refactor|restruktur/i.test(subject))        return { type:'refactor',desc: subject, ...TYPE_MAP.refactor }
  if (/^perf|optimis/i.test(subject))               return { type:'perf',   desc: subject, ...TYPE_MAP.perf }
  if (/^test/i.test(subject))                       return { type:'test',   desc: subject, ...TYPE_MAP.test }
  if (/^build|bundle|compil/i.test(subject))        return { type:'build',  desc: subject, ...TYPE_MAP.build }
  if (/^sprint|selesai sprint/i.test(subject))      return { type:'sprint', desc: subject, ...TYPE_MAP.sprint }
  return { type: 'chore', desc: subject, ...DEFAULT_TYPE }
}

// ── Parse git log ─────────────────────────────────────────────────────────────
function getCommits(since) {
  const range = since ? `${since}..HEAD` : ''
  const fmt   = '%H\x1F%ad\x1F%s\x1F%an'
  const raw   = run(`git log ${range} --no-merges --format="${fmt}" --date=short`)
  if (!raw) return []
  return raw.split('\n').filter(Boolean).map(line => {
    const [hash, date, subject, author] = line.split('\x1F')
    const parsed = parseCommitType(subject || '')
    return { hash: hash?.slice(0,7) || '', date: date || '', subject: subject || '', author: author || '', ...parsed }
  })
}

// ── Extract latest version from CHANGELOG.md ─────────────────────────────────
function getLatestVersion() {
  if (!existsSync(CHANGELOG)) return null
  const src  = readFileSync(CHANGELOG, 'utf8')
  const match = src.match(/^## \[(\d+\.\d+\.\d+)\]/m)
  return match ? match[1] : null
}

// ── Extract latest date from CHANGELOG.md ────────────────────────────────────
function getLatestDate() {
  if (!existsSync(CHANGELOG)) return null
  const src = readFileSync(CHANGELOG, 'utf8')
  const match = src.match(/^## .+ — (\d{4}-\d{2}-\d{2})/m)
  return match ? match[1] : null
}

function getLatestTag() {
  return run('git describe --tags --abbrev=0 2>/dev/null') || null
}

// ── Bump version ──────────────────────────────────────────────────────────────
function bump(v, type) {
  const [maj, min, pat] = (v || '0.0.0').split('.').map(Number)
  if (type === 'major') return `${maj+1}.0.0`
  if (type === 'minor') return `${maj}.${min+1}.0`
  return `${maj}.${min}.${pat+1}`
}

// ── Group commits by section ───────────────────────────────────────────────────
function groupBySection(commits) {
  const groups = {}
  for (const c of commits) {
    const sec = c.section || 'Changed'
    if (!groups[sec]) groups[sec] = []
    groups[sec].push(c)
  }
  return groups
}

// ── Format changelog entry (Keep a Changelog format) ─────────────────────────
function formatEntry({ version, date, sprintLabel, commits }) {
  const groups  = groupBySection(commits)
  const lines   = []
  const tagUrl  = `https://github.com/isnaenihidayat/wmslite/releases/tag/v${version}`
  const heading = sprintLabel
    ? `## [${version}] — ${date} (${sprintLabel})`
    : `## [${version}] — ${date}`

  lines.push(heading)
  lines.push('')

  for (const section of SECTION_ORDER) {
    if (!groups[section]?.length) continue
    lines.push(`### ${groups[section][0].emoji} ${section}`)
    for (const c of groups[section]) {
      // Format: - desc `[abc1234]`
      const shortDesc = c.desc.replace(/^[\w()!\s]+:\s*/, '').trim() || c.desc
      lines.push(`- ${shortDesc} (\`${c.hash}\`)`)
    }
    lines.push('')
  }

  // Commits table (compact)
  if (commits.length > 0) {
    lines.push('<details>')
    lines.push('<summary>📋 Detail Commits</summary>')
    lines.push('')
    lines.push('| Hash | Date | Message | Author |')
    lines.push('|------|------|---------|--------|')
    for (const c of commits) {
      const subject = c.subject.replace(/\|/g, '\\|').slice(0, 80)
      lines.push(`| \`${c.hash}\` | ${c.date} | ${subject} | ${c.author} |`)
    }
    lines.push('')
    lines.push('</details>')
    lines.push('')
  }

  return lines.join('\n')
}

// ── Initialize CHANGELOG.md if it doesn't exist ───────────────────────────────
function initChangelog() {
  const header = `# Changelog — WMS Lite

Semua perubahan penting pada project **WMS Lite** didokumentasikan di file ini.

Format mengikuti [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).  
Versioning mengikuti [Semantic Versioning](https://semver.org/).

> 🔗 Repository: [github.com/isnaenihidayat/wmslite](https://github.com/isnaenihidayat/wmslite)

---

`
  writeFileSync(CHANGELOG, header, 'utf8')
  console.log('  📄  CHANGELOG.md dibuat baru.')
}

// ── Prepend entry to CHANGELOG.md ────────────────────────────────────────────
function prependEntry(entry) {
  if (!existsSync(CHANGELOG)) initChangelog()
  let src = readFileSync(CHANGELOG, 'utf8')
  // Find the separator ---
  const sep = '\n---\n\n'
  const idx = src.indexOf(sep)
  if (idx !== -1) {
    src = src.slice(0, idx + sep.length) + entry + '\n' + src.slice(idx + sep.length)
  } else {
    // Just prepend after header
    src = src + '\n' + entry + '\n'
  }
  writeFileSync(CHANGELOG, src, 'utf8')
}

// ── Create git tag ────────────────────────────────────────────────────────────
function createTag(version, message) {
  try {
    run(`git tag -a "v${version}" -m "${message}"`)
    console.log(`  🏷️   Git tag v${version} dibuat`)
    return true
  } catch(e) {
    console.log(`  ⚠️   Tag sudah ada atau gagal dibuat: ${e.message}`)
    return false
  }
}

// ── Main ─────────────────────────────────────────────────────────────────────
async function main() {
  console.log('\n╔══════════════════════════════════════════════════════╗')
  console.log('║   WMS Lite — Changelog Generator                    ║')
  console.log('╚══════════════════════════════════════════════════════╝\n')

  // ── Collect context
  const sprintLabel    = process.env.SPRINT_LABEL || ''
  const latestVersion  = getLatestVersion() || '0.0.0'
  const latestDate     = getLatestDate()
  const latestGitTag   = getLatestTag()

  console.log(`  📌  Versi terakhir : v${latestVersion}`)
  console.log(`  📅  Entri terakhir : ${latestDate ?? '(belum ada)'}`)
  console.log(`  🏷️   Git tag terakhir: ${latestGitTag ?? '(belum ada)'}`)
  if (sprintLabel) console.log(`  🏁  Sprint label  : ${sprintLabel}`)
  console.log()

  // ── Ambil commits baru
  // Prioritas: sejak git tag terakhir, atau sejak tanggal terakhir di changelog
  let sinceSha = latestGitTag || ''
  const allCommits = getCommits(sinceSha)

  // Filter: exclude chore(sprint) commits (agar changelog release itu sendiri tidak masuk)
  const commits = allCommits.filter(c => {
    const skip = /^chore\(sprint\)|^chore: selesai sprint/i.test(c.subject)
    return !skip
  })

  if (commits.length === 0) {
    console.log('  ✅  Tidak ada commit baru sejak entry terakhir.\n')
    const cont = await askOrAuto('  Tetap buat entry manual? (y/N): ', 'n')
    if (cont.toLowerCase() !== 'y') { rl.close(); return }
  } else {
    console.log(`  🔍  ${commits.length} commit baru ditemukan:\n`)
    commits.slice(0, 15).forEach((c, i) => {
      console.log(`  ${String(i+1).padStart(2)}. [${c.hash}] ${c.emoji} ${c.date} — ${c.subject.slice(0,70)}`)
    })
    if (commits.length > 15) console.log(`       ... dan ${commits.length - 15} lainnya`)
    console.log()
  }

  // ── Version bump
  console.log(`  Versi saat ini: ${BOLD}v${latestVersion}${RESET}`)
  console.log(`  Pilih jenis bump:`)
  console.log(`    ${DIM}(1) patch${RESET}  v${bump(latestVersion,'patch')}  — bug fix / maintenance`)
  console.log(`    ${DIM}(2) minor${RESET}  v${bump(latestVersion,'minor')}  — fitur baru (sprint baru ✓)`)
  console.log(`    ${DIM}(3) major${RESET}  v${bump(latestVersion,'major')}  — breaking changes`)
  console.log(`    ${DIM}(4) custom${RESET} — ketik sendiri`)
  const bumpChoice = await askOrAuto('\n  Pilihan [1/2/3/4]: ', '2')

  let newVersion
  if      (bumpChoice.trim() === '2') newVersion = bump(latestVersion, 'minor')
  else if (bumpChoice.trim() === '3') newVersion = bump(latestVersion, 'major')
  else if (bumpChoice.trim() === '4') newVersion = (await askOrAuto('  Versi custom (e.g. 1.5.0): ', '1.0.0')).trim()
  else                                 newVersion = bump(latestVersion, 'patch')

  const today = new Date().toISOString().split('T')[0]
  const releaseDate = (await askOrAuto(`  Tanggal rilis [${today}]: `, today)).trim() || today

  // ── Sprint label (jika belum di-set via env)
  let finalSprintLabel = sprintLabel
  if (!finalSprintLabel && !isAuto) {
    finalSprintLabel = (await ask('  Label sprint (kosongkan jika bukan sprint release): ')).trim()
  }

  // ── Buat entry
  const entry = formatEntry({ version: newVersion, date: releaseDate, sprintLabel: finalSprintLabel, commits })

  console.log('\n  ─── Preview Changelog Entry ───────────────────────────')
  console.log(entry)
  console.log('  ──────────────────────────────────────────────────────\n')

  const confirm = await askOrAuto('  Tulis ke CHANGELOG.md? (Y/n): ', 'y')
  if (confirm.toLowerCase() === 'n') {
    console.log('  Dibatalkan.\n')
    rl.close()
    return
  }

  // ── Tulis ke CHANGELOG.md
  prependEntry(entry)
  console.log(`\n  ✅  CHANGELOG.md diperbarui dengan v${newVersion}!`)

  // ── Tawarkan git tag
  const tagMsg = finalSprintLabel
    ? `WMS Lite v${newVersion} — ${finalSprintLabel}`
    : `WMS Lite v${newVersion}`
  const doTag = await askOrAuto(`  Buat git tag v${newVersion}? (Y/n): `, 'y')
  if (doTag.toLowerCase() !== 'n') {
    createTag(newVersion, tagMsg)
  }

  console.log(`\n  📋  File diperbarui:`)
  console.log(`      CHANGELOG.md`)
  console.log(`\n  Changelog untuk ${finalSprintLabel || `v${newVersion}`} siap! 🎉\n`)

  rl.close()
}

// ── ANSI helpers (dipakai dalam string interpolation) ─────────────────────────
const BOLD = '\x1b[1m'
const DIM  = '\x1b[2m'
const RESET = '\x1b[0m'

main().catch(err => {
  console.error('\n❌  Error:', err.message)
  process.exit(1)
})
