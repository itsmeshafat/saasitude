# 08 — Agent Setup

## Install order (once per repo)

1. Checkpoint baseline
   php artisan tyro-checkpoint:create clean-baseline --note="Fresh Tyro install"
   Then lock it (check `php artisan list tyro-checkpoint` for the lock command).

2. Laravel Boost
   composer require --dev laravel/boost && php artisan boost:install
   Select every coding agent you use. Enable guidelines and skills.
   Add `php artisan boost:update --ansi` to Composer post-update-cmd.

3. Tyro AI skill
   php artisan tyro-dashboard:setup-ai-skill
   Read what it installs and confirm.

4. Matt Pocock skills (mattpocock/skills, MIT, verified)
   One install method only:
   - Claude Code plugin: claude plugin install mattpocock-skills@claude-plugins-official
   - Any agent (editable): npx skills@latest add mattpocock/skills -a <agent>
   Then run /setup-matt-pocock-skills once. Set issue tracker and docs location when asked.

5. Context7 MCP (already connected in your Claude account)
   Use for Stripe, Paddle, SSLCommerz SDK and Laravel docs — not memory.

6. Design skills — see docs/07-design-system.md for hardened install commands.

7. Project-custom skills (write these yourself in .ai/skills/):
   - saas-conventions — workspace scoping, money in minor units, entitlement checks, events-first side effects
   - billing-gateway-adapter — how to add a gateway, contract tests every adapter must pass
   - tyro-dashboard-pages — when to use scaffold vs HasCrud vs hand-build; never edit vendor views directly
   - webhook-idempotency — the webhook_events pattern
   - saas-testing — factories, gateway fakes, simulating webhooks and IPN

## Matt Pocock skill map

| When | Skill / command |
|---|---|
| Align on a feature | /grill-with-docs, /grill-me |
| Turn discussion into spec | /to-spec |
| Break spec into tickets | /to-tickets |
| Implement a ticket | /implement (wraps /tdd + /code-review) |
| Implement whole spec | /implement-spec |
| Large planning | /wayfinder |
| Debug hard bugs | diagnosing-bugs |
| Test-first | tdd |
| Code review | code-review |
| Architecture health check | /improve-codebase-architecture |
| PR description | pr |
| Session handoff | /handoff, /retro |
| Human-only setup steps | wizard |

## Supply-chain rule

A skill is instructions your agent will follow with shell access. Read each SKILL.md before keeping it. Pin to a commit or version. Prefer MIT/Apache-licensed sources with active maintenance. Record versions in docs/skills-lock.md.

## AGENTS.md

Superseded by the real file at the repo root. The root `AGENTS.md` is the live, maintained copy (Parts 1–6: Tyro native features, gap analysis, non-negotiables, workflow, tools/skills, open decisions) — this doc file is no longer the source, don't hand-copy a block from here into the root file. If the root file and this doc ever disagree, the root file wins; update this note (not a duplicated block) when that happens.

One exception: `php artisan boost:install` / `boost:update` appends a `<laravel-boost-guidelines>` block to the bottom of the root `AGENTS.md` (and `CLAUDE.md`) automatically. That block is auto-generated and auto-synced by Boost — never hand-edit or duplicate it anywhere, including here.

## Done means
Tests pass, feature works in browser (desktop + 390px), audit/logging in place, docs updated, no silent TODOs.
