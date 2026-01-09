#!/usr/bin/env bash

set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
build_dir="${root_dir}/build/scoped"
work_dir="${root_dir}/build/scoped-work"

rm -rf "${build_dir}" "${work_dir}"
mkdir -p "${work_dir}"

cp -R "${root_dir}/src" "${work_dir}/src"
cp "${root_dir}/composer.json" "${work_dir}/composer.json"
if [[ -f "${root_dir}/composer.lock" ]]; then
    cp "${root_dir}/composer.lock" "${work_dir}/composer.lock"
fi

# Install production dependencies in a clean vendor dir to avoid dev packages in the build.
COMPOSER_VENDOR_DIR="${work_dir}/vendor" composer install \
    --working-dir "${work_dir}" \
    --no-dev \
    --prefer-dist \
    --no-progress \
    --no-scripts

# Scope vendor dependencies and update references in the source code.
SCOPER_BASE_DIR="${work_dir}" SCOPER_VENDOR_DIR="${work_dir}/vendor" \
    "${root_dir}/vendor/bin/php-scoper" add-prefix \
    --config "${root_dir}/scoper.inc.php" \
    --working-dir "${work_dir}" \
    --output-dir "${build_dir}"

# Regenerate the autoloader for the scoped build so prefixed namespaces resolve.
cp "${root_dir}/composer.json" "${build_dir}/composer.json"
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
