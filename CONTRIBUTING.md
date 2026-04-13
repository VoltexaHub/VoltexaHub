# Contributing to VoltexaHub

Thanks for your interest! VoltexaHub is solo-developed at the moment but contributions are welcome.

## Ground rules

- **License:** By contributing, you agree your contributions are licensed under AGPL-3.0-or-later.
- **Scope:** Check the roadmap in [README.md](README.md) before starting large work. Open an issue first for anything bigger than a small fix.
- **Style:** Follow the existing code style (Laravel Pint defaults for PHP, Prettier for JS). Don't refactor unrelated code in a feature PR.

## Development setup

See the Quickstart in [README.md](README.md). Everything runs in Docker.

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan migrate:fresh --seed --force
```

## Pull request checklist

- [ ] Branch from `main` as `feature/...` or `fix/...`
- [ ] Tests pass: `docker compose exec app php artisan test`
- [ ] Assets build: `docker compose exec node npm run build`
- [ ] Commit messages follow conventional commits (`feat:`, `fix:`, `docs:`, `chore:`, ...)
- [ ] PR description explains **why**, not just what

## Reporting issues

Use GitHub Issues. For bugs, include:

- VoltexaHub commit SHA
- Steps to reproduce
- Expected vs. actual behavior
- Relevant logs (`docker compose logs app`)

## Security

Do **not** file security issues publicly. Email the maintainer instead (see the project page for contact).
