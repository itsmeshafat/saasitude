# 07 — Design System

## Two-skill setup

| Skill | Role in this project |
|---|---|
| `nextlevelbuilder/ui-ux-pro-max-skill` | Generate the one master design system (palette, type, spacing, style). Has a Laravel Blade stack guide. Run once per product. |
| `ArnavPuri/designskills` | Specific jobs: dashboard layout, mobile UI, landing page, email design, accessibility audit, dark mode, contrast check. |

Single source of truth: `design-system/<project>/MASTER.md` (written by ui-ux-pro-max, committed). All agents read MASTER.md before touching any UI. Page overrides only where genuinely needed.

Tyro Dashboard themes via shadcn CSS variables. All custom colours and type tokens land in `shadcn-theme.blade.php`. Publish with `php artisan tyro-dashboard:publish-style --theme-only`. Never hard-code hex values in Blade views.

## Hardened install commands

# ui-ux-pro-max — pinned version, offline (no GitHub download)
unset GITHUB_TOKEN UI_PRO_MAX_GITHUB_TOKEN
npm install -g ui-ux-pro-max-cli@2.15.0
cd /path/to/laravel-app
uipro init --ai claude --offline
# Remove skills you don't need:
# rm -rf .claude/skills/design .claude/skills/brand .claude/skills/banner-design
# Do NOT run `uipro update` — update deliberately with the pinned command above.

# Arnav Puri designskills — pinned to audited commit c7873015
git clone https://github.com/ArnavPuri/designskills.git /tmp/designskills
git -C /tmp/designskills checkout c7873015add8571ca95043febad0d9e6c97ee5c6
# Copy only what you need into your agent's skills folder:
mkdir -p .claude/skills
for skill in design-context ui-design dashboard-design mobile-ui-design landing-page-design \
  design-system color-palette typography dark-mode email-design design-critique \
  layout-composition image-generation; do
  cp -r /tmp/designskills/skills/$skill .claude/skills/
done

# Install Playwright + axe-core locally (removes unpinned CDN fallback in audit.mjs)
npm i -D playwright axe-core && npx playwright install chromium

## Gemini API key rules

- Do NOT put the key in any file inside the Laravel project or in .env.
- Export in the terminal where the agent runs: export GEMINI_API_KEY=...
- Or use ~/.claude/.env with chmod 600.
- Create a dedicated key with a billing cap in Google AI Studio. Rotate if it appears in any log or commit.
- Do not send client-confidential screens or customer data to image generation.

## Phase 6.5 prompt (run before Phase 7)

Goal: one design system for the whole SaaS boilerplate.
1) Read both design skills' SKILL.md files and report anything conflicting or unsafe. Do not install anything globally.
2) Use ui-ux-pro-max to generate a design system for "B2B SaaS with subscription billing and an admin console", stack = Laravel Blade + Tailwind. Show me 2 visual directions (palette, type pairing, density) and wait for my choice.
3) Persist the chosen direction to design-system/<project>/MASTER.md with page overrides only where needed (pricing, checkout, admin overview).
4) Translate MASTER.md tokens into Tyro Dashboard's shadcn CSS variables: run `php artisan tyro-dashboard:publish-style --theme-only`, edit shadcn-theme.blade.php for light and dark. No hard-coded hex in Blade views.
5) Also translate into Tailwind tokens for public pages (tailwind.config.js).
6) Run the design-context contrast script on every foreground/background pair. Fix all WCAG 2.2 AA failures.
7) Define the reusable Blade component kit: button, input, card, badge, table, empty state, plan card, usage meter, status pill (subscription states), toast, modal (reuse Tyro's showConfirm/showDanger).
8) Write docs/design-system-tokens.md and add .ai/skills/design-rules/SKILL.md so Boost loads it for every UI task.
Gate: run design-critique audit at 375, 768, 1024, 1440 px + dark mode. No critical findings before proceeding to Phase 7.

## Audit summary (2026-11-02)

Both repos audited at code level (scripts, SKILL.md files, data CSVs, CLI dist bundle). No malicious behaviour, install-time hooks, permission overrides or hidden Unicode found. Key risks: image generation sends prompts and reference images to Google Gemini — use a dedicated key and never send customer data. `uipro init` by default downloads the latest GitHub release zip — use --offline. Full findings in the master blueprint doc (Part 12).
