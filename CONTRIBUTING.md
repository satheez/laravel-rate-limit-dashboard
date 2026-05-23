# Contributing

Thank you for your interest in contributing to Laravel Rate-Limit Dashboard.

## Local Setup

```bash
git clone https://github.com/satheez/laravel-rate-limit-dashboard.git
cd laravel-rate-limit-dashboard
composer install
```

## Running Tests

```bash
vendor/bin/pest
```

## Code Style

```bash
vendor/bin/pint
```

To check without fixing:

```bash
vendor/bin/pint --test
```

## Static Analysis

```bash
vendor/bin/phpstan analyse
```

## Automated Refactoring (Rector)

Check for suggested changes (dry-run):

```bash
vendor/bin/rector process --dry-run
```

Apply changes:

```bash
vendor/bin/rector process
```

Rector enforces PHP 8.2+ modernization, dead code removal, and early-return patterns. Run it before opening a PR and commit any changes it produces.

## Branch Naming

- `feature/<name>` — new features
- `fix/<name>` — bug fixes
- `chore/<name>` — tooling, dependency updates, documentation

## Pull Request Guidelines

- Keep PRs focused on a single concern.
- All new code must be covered by tests.
- PHPStan must pass at level 8 or higher.
- Pint formatting must be clean.
- Add an entry to `CHANGELOG.md` under `[Unreleased]`.

## Testing Expectations

- Unit tests: pure class logic, no filesystem or HTTP.
- Feature tests: use Orchestra Testbench, testing middleware injection and event emission.
