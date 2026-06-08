# WMS Lite — Warehouse Management System

> Sistem Manajemen Gudang untuk tracking inbound, outbound, shipment, dan inventory.

[![Release](https://img.shields.io/github/v/release/isnaenihidayat/wmslite?label=latest&color=0ea5e9)](https://github.com/isnaenihidayat/wmslite/releases)
[![Next.js](https://img.shields.io/badge/Frontend-Next.js%2015-black?logo=next.js)](apps/frontend/)
[![Laravel](https://img.shields.io/badge/Backend-Laravel%2011-red?logo=laravel)](apps/backend/)

---

## 🗂️ Struktur Monorepo

```
wmslite/
├── apps/
│   ├── frontend/          # Next.js 15 (Sprint 1.x — current)
│   └── backend/           # Laravel 11 (Sprint 2.x — upcoming)
├── protected/             # Yii 1.x legacy backend
├── scripts/               # Automation scripts
│   ├── sprint-done.sh     # Sprint selesai → changelog + push
│   ├── auto-push.sh       # Quick commit & push
│   └── generate-changelog.mjs  # Changelog generator
├── API_INVENTORY.md       # ~134 endpoint mapping (Yii → Laravel)
├── CHANGELOG.md           # Project changelog
└── tables_schema.sql      # Database schema
```

---

## 🚀 Menjalankan Frontend (Dev)

```bash
cd apps/frontend
npm install
npm run dev
# → http://localhost:3000
```

**Environment variables** — copy dan sesuaikan:
```bash
cp apps/frontend/.env.local.example apps/frontend/.env.local
```

---

## 📋 Scripts Automation

### Akhir Sprint (Changelog + Push)
```bash
bash scripts/sprint-done.sh "Sprint 1.2"
```
Menjalankan:
1. Generate `CHANGELOG.md` secara interaktif dari git commits
2. Buat git tag `v{version}`
3. Commit + push ke `origin/main`

### Quick Push (tanpa changelog)
```bash
bash scripts/auto-push.sh                    # auto commit message
bash scripts/auto-push.sh "feat: tambah X"   # custom message
bash scripts/auto-push.sh --watch            # mode watch (fswatch)
bash scripts/auto-push.sh --status           # cek git status
```

### Generate Changelog Saja
```bash
bash scripts/sprint-done.sh --changelog-only "Sprint 1.2"
# atau:
SPRINT_LABEL="Sprint 1.2" node scripts/generate-changelog.mjs
```

---

## 🗺️ Sprint Roadmap

| Sprint | Status | Scope |
|--------|--------|-------|
| Sprint 0.1 | ✅ Done | API Audit (134 endpoints mapped) |
| Sprint 1.1 | ✅ Done | Next.js 15 fondasi + design system |
| Sprint 1.2 | 🔄 Next | Shipment module |
| Sprint 1.3 | ⏳ | Inbound module |
| Sprint 1.4 | ⏳ | Outbound module |
| Sprint 1.5 | ⏳ | Reports + Dashboard live |
| Sprint 2.x | ⏳ | Laravel backend API |
| Sprint 3.x | ⏳ | Cutover + Yii retirement |

---

## 🛠️ Tech Stack

| Layer | Stack |
|-------|-------|
| Frontend | Next.js 15, TypeScript, Tailwind v4, Shadcn/ui |
| State | Zustand + TanStack Query |
| Auth | NextAuth.js v5 + Laravel Sanctum |
| Forms | React Hook Form + Zod |
| Tables | TanStack Table v8 |
| Charts | Recharts |
| Backend (target) | Laravel 11, PHP 8.3, MySQL |
| Legacy | Yii 1.x (sementara tetap jalan) |

---

## 📝 Changelog

Lihat [CHANGELOG.md](CHANGELOG.md) untuk history perubahan.
