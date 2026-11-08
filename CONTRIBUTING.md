# Contributing to SaaSitude

Thanks for your interest in making SaaSitude better!

## Setup

```bash
git clone https://github.com/itsmeshafat/saasitude.git
cd saas-boilerplate-laravel
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Or just run the [web installer](docs/adr/ADR-013-web-installer.md) — it configures everything.

Read first: [`AGENTS.md`](AGENTS.md) (project rules), [`GLOSSARY.md`](GLOSSARY.md) (domain terms), relevant [ADRs](docs/adr/).

## Ground rules

1. **Business logic lives in Actions/Services.** Controllers and views stay thin; web and API controllers call the same Actions.
2. **Never check plan names in code.** Use `Entitlements::for($workspace)->can('key')`.
3. **Money is integer minor units + ISO currency.** No floats, ever.
4. **Payment state changes only via verified webhooks/IPN.** Return URLs display status only.
5. **Don't fork Tyro's auth flows** — hook events (`Registered`, etc.) instead. Check [Tyro's features](https://tyrolabs.dev) before building anything the package already ships.
6. **Audit trail** (`HasAuditTrail`) on every model an admin can mutate; `showDanger`/`showConfirm` on all destructive actions.

## Workflow

1. Branch from `main`: `feat/short-description`
2. **TDD**: write the failing test first (`php artisan make:test`), then make it pass. Billing tests use gateway fakes — never real API calls, never secrets in fixtures.
3. Before opening the PR:
   ```bash
   php artisan test                                # all green
   vendor/bin/pint                                  # style clean
   vendor/bin/phpstan analyse --memory-limit=1G     # Larastan level 5 clean
   ```
4. UI changes: check 1440px **and** 390px, light **and** dark mode; every screen needs empty/loading/error states.
5. Update `GLOSSARY.md` and the relevant ADR if behavior or terms changed. No silent TODOs — use `FIXME + issue ref`.

## Pull requests

- One logical change per PR, with tests proving it.
- Describe **what** and **why**; the diff shows the how.
- Screenshots for UI changes (both themes, both breakpoints).
- Security-sensitive code (webhooks, auth, secrets): call it out explicitly in the description.

## Reporting bugs & security

Bugs: [open an issue](https://github.com/itsmeshafat/saasitude/issues) with reproduction steps and stack trace.
Security: see [`SECURITY.md`](SECURITY.md) — please don't open public issues for vulnerabilities.
