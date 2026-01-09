# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2025-01-09

### Added

- Scoped distribution build tooling and release workflow for plugin-safe usage.
- Scoped build smoke test script.
- Third-party PSR license copies in `third-party-licenses/`.

### Changed

- Manual installation documentation to recommend the scoped build and clarify unscoped usage risks.

## [2.1.0] - 2025-01-06

### Added

- `ListApi::syncMembers()` for the JSON actions-based member sync endpoint, plus the accompanying `SyncAction` enum, integration test, and runnable example.

### Deprecated

- `ListApi::addOrUpdateMembers()` and `BulkMode` now point to the new syncMembers + SyncAction API.


## [2.0.1] - 2025-05-29

### Fixed

- Removed directories from `.gitattributes` that should be included in repository archives

## [2.0.0] - 2025-05-27

### Added

- API instance reuse via caching for improved performance
- Methods to manage caching behavior (enabling, disabling, clearing)
- Default use of cached instances in all API retrieval methods
- Expanded test coverage for instance reuse, disabling, and clearing of cached instances

## [2.0.0-rc.1] - 2025-05-20

### Changed

- Fully rewritten codebase; not backward compatible with v1.x
- Configuration and usage structure updated (see [README](README.md))
