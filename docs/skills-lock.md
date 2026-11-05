# Skills Lock

Installed skills and their content hashes are tracked automatically by the `skills` CLI in `skills-lock.json` at the repo root — don't hand-maintain a duplicate list here.

## Installed packages

| Package | Installed via | Agents |
|---|---|---|
| `mattpocock/skills` | `npx skills@latest add mattpocock/skills -a '*' -y` (2026-11-03) | Claude Code + Eve natively; Codex, Gemini, Kilo, OpenCode, generic `.ai` symlinked from `.agents/skills/` (OpenCode reads `.agents/skills/` directly — no separate folder needed); Command Code and ZCode got their own `.commandcode/skills/` and `.zcode/skills/` symlinked the same way (those folders didn't exist before — created them); Cursor copied (matches its existing convention — see `.cursor/skills/`) |
| Laravel Boost project skills (deploying-to-cloud, infer-conventions, laravel-best-practices, tailwindcss-development, testing-best-practices, tyro-dashboard) | `php artisan boost:install` | same distribution pattern as above |
| `devops-engineer` v1.2.0 (Jeffallan, MIT) | copied from `.commandcode/skills/devops-engineer/` into `.zcode/skills/devops-engineer/` (2026-11-05) | ZCode only |

## Not yet installed

Design skills (`nextlevelbuilder/ui-ux-pro-max-skill`, `ArnavPuri/designskills`) — deliberately deferred to Phase 6.5. Hardened install commands are in `docs/07-design-system.md`.

## Restoring / updating

- Restore from lock on a fresh clone: `npx skills@latest experimental_install`
- Update to latest: `npx skills@latest update` (review the diff before committing — do not run on autopilot per AGENTS.md's third-party skill rules)

## Done

- `/setup-matt-pocock-skills` — run 2026-11-04. Issue tracker set to local files, docs location set to `docs/`.
