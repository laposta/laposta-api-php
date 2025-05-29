# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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