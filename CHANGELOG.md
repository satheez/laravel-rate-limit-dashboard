# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial project structure.
- Documentation for architecture, checks, configuration, installation, output, scoring, and usage.
- Safe rate-limit instrumentation for numeric and named Laravel limiters, including multiple limits and custom responses.
- Queue-safe rate-limit events, raw event persistence, and minute/hour/day summary aggregation.
- Secured dashboard access, JSON API endpoints, health checks, runtime limiter configuration, config audit records, pruning, and threshold mail alerts.
- Self-contained operational dashboard UI with metrics, checks, limiter configuration, limiter activity, top offenders, and recent events.
- Tests for middleware behavior, storage aggregation, dashboard authorization, API output, pruning, and alerts.

### Changed
- Aligned documentation, CI, static analysis configuration, and package dependencies with the implemented package behavior.
