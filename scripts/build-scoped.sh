#!/usr/bin/env bash

set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
scoped_build_dir="${root_dir}/build/scoped"
scoped_work_dir="${root_dir}/build/scoped-work"
version_scoped_build_dir="${root_dir}/build/version-scoped"
version_scoped_work_dir="${root_dir}/build/version-scoped-work"
build_target="${1:-scoped}"

is_semver() {
    [[ "${1}" =~ ^v?[0-9]+\.[0-9]+\.[0-9]+([-.+][0-9A-Za-z.-]+)?$ ]]
}

detect_release_version() {
    if [[ -n "${LAPOSTA_RELEASE_VERSION:-}" ]]; then
        if ! is_semver "${LAPOSTA_RELEASE_VERSION}"; then
            echo "Invalid LAPOSTA_RELEASE_VERSION: ${LAPOSTA_RELEASE_VERSION}" >&2
            exit 1
        fi

        printf '%s\n' "${LAPOSTA_RELEASE_VERSION}"
        return
    fi

    if [[ -n "${GITHUB_REF_NAME:-}" ]] && is_semver "${GITHUB_REF_NAME}"; then
        printf '%s\n' "${GITHUB_REF_NAME}"
        return
    fi

    local changelog_version
    changelog_version="$(
        sed -nE 's/^## \[([^]]+)\].*/\1/p' "${root_dir}/CHANGELOG.md" \
            | awk '$0 != "Unreleased" { print; exit }' \
            | head -n 1
    )"
    if [[ -n "${changelog_version}" ]]; then
        printf '%s\n' "${changelog_version}"
        return
    fi

    local tag_version
    tag_version="$(git -C "${root_dir}" tag --sort=-version:refname | head -n 1 || true)"
    if [[ -n "${tag_version}" ]]; then
        printf '%s\n' "${tag_version}"
        return
    fi

    echo "Unable to determine release version. Set LAPOSTA_RELEASE_VERSION." >&2
    exit 1
}

build_namespace_suffix() {
    local version="${1#v}"
    if [[ ! "${version}" =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)(.*)?$ ]]; then
        echo "Invalid semantic version: ${1}" >&2
        exit 1
    fi

    local suffix="${BASH_REMATCH[1]}${BASH_REMATCH[2]}${BASH_REMATCH[3]}"
    local extra="${BASH_REMATCH[4]}"

    if [[ -n "${extra}" ]]; then
        local segment sanitized=""
        extra="${extra#-}"
        extra="${extra#+}"
        IFS='.-+' read -r -a parts <<< "${extra}"
        for segment in "${parts[@]}"; do
            segment="${segment//[^[:alnum:]]/}"
            if [[ -z "${segment}" ]]; then
                continue
            fi

            sanitized+="${segment^}"
        done
        suffix+="${sanitized}"
    fi

    printf '%s\n' "${suffix}"
}

prepare_work_dir() {
    local work_dir="$1"
    local public_namespace="$2"

    rm -rf "${work_dir}"
    mkdir -p "${work_dir}"

    cp -R "${root_dir}/src" "${work_dir}/src"
    cp "${root_dir}/composer.json" "${work_dir}/composer.json"
    if [[ -f "${root_dir}/composer.lock" ]]; then
        cp "${root_dir}/composer.lock" "${work_dir}/composer.lock"
    fi

    if [[ "${public_namespace}" == 'LapostaApi' ]]; then
        return
    fi

    PUBLIC_NAMESPACE="${public_namespace}" find "${work_dir}/src" -type f -name '*.php' -exec \
        perl -0pi -e '
            my $namespace = $ENV{PUBLIC_NAMESPACE};
            s/\bLapostaApi\\/$namespace . "\\"/ge;
            s/\bnamespace\s+LapostaApi\b/"namespace " . $namespace/ge;
        ' {} +

    PUBLIC_NAMESPACE="${public_namespace}" perl -0pi -e '
        my $namespace = $ENV{PUBLIC_NAMESPACE};
        s/"LapostaApi\\\\":/"$namespace\\\\":/g;
        s/"LapostaApi\\\\Tests\\\\":/"$namespace\\\\Tests\\\\":/g;
    ' "${work_dir}/composer.json"
}

build_distribution() {
    local work_dir="$1"
    local build_dir="$2"
    local public_namespace="$3"
    local vendor_prefix="$4"
    local description="$5"

    prepare_work_dir "${work_dir}" "${public_namespace}"
    rm -rf "${build_dir}"

    # Install production dependencies in a clean vendor dir to avoid dev packages in the build.
    COMPOSER_VENDOR_DIR="${work_dir}/vendor" composer install \
        --working-dir "${work_dir}" \
        --no-dev \
        --prefer-dist \
        --no-progress \
        --no-scripts

    # Scope vendor dependencies and update references in the source code.
    SCOPER_BASE_DIR="${work_dir}" \
        SCOPER_VENDOR_DIR="${work_dir}/vendor" \
        SCOPER_PREFIX="${vendor_prefix}" \
        SCOPER_EXCLUDE_NAMESPACES="${public_namespace}" \
        "${root_dir}/vendor/bin/php-scoper" add-prefix \
        --config "${root_dir}/scoper.inc.php" \
        --working-dir "${work_dir}" \
        --output-dir "${build_dir}"

    # Regenerate the autoloader for the scoped build so prefixed namespaces resolve.
    cp "${work_dir}/composer.json" "${build_dir}/composer.json"
    composer dump-autoload --working-dir "${build_dir}" --classmap-authoritative --no-dev
    rm "${build_dir}/composer.json"

    # Provide a simple entrypoint autoloader for the scoped distribution.
    cat > "${build_dir}/autoload.php" <<'PHP'
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';
PHP

    cp "${root_dir}/LICENSE" "${root_dir}/README.md" "${root_dir}/CHANGELOG.md" "${build_dir}/"
    cp -R "${root_dir}/third-party-licenses" "${build_dir}/third-party-licenses"

    cat > "${build_dir}/SCOPED.txt" <<TXT
This is the ${description} distribution of laposta-api-php.
Release version: ${release_version}
Public namespace: ${public_namespace}
Vendor prefix: ${vendor_prefix}
TXT
}

release_version="$(detect_release_version)"
version_suffix="$(build_namespace_suffix "${release_version}")"
versioned_namespace="LapostaApi${version_suffix}"

case "${build_target}" in
    scoped)
        rm -rf "${scoped_build_dir}" "${scoped_work_dir}"
        build_distribution \
            "${scoped_work_dir}" \
            "${scoped_build_dir}" \
            'LapostaApi' \
            'LapostaApi\Vendor' \
            'scoped (prefixed)'
        ;;
    version-scoped)
        rm -rf "${version_scoped_build_dir}" "${version_scoped_work_dir}"
        build_distribution \
            "${version_scoped_work_dir}" \
            "${version_scoped_build_dir}" \
            "${versioned_namespace}" \
            "${versioned_namespace}\Vendor" \
            'version-scoped (prefixed + versioned namespace)'
        ;;
    all)
        rm -rf "${scoped_build_dir}" "${scoped_work_dir}" "${version_scoped_build_dir}" "${version_scoped_work_dir}"
        build_distribution \
            "${scoped_work_dir}" \
            "${scoped_build_dir}" \
            'LapostaApi' \
            'LapostaApi\Vendor' \
            'scoped (prefixed)'
        build_distribution \
            "${version_scoped_work_dir}" \
            "${version_scoped_build_dir}" \
            "${versioned_namespace}" \
            "${versioned_namespace}\Vendor" \
            'version-scoped (prefixed + versioned namespace)'
        ;;
    *)
        echo "Usage: $0 [scoped|version-scoped|all]" >&2
        exit 1
        ;;
esac
