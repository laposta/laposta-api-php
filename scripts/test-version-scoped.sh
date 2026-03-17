#!/usr/bin/env bash

set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
autoload="${root_dir}/build/version-scoped/autoload.php"
metadata="${root_dir}/build/version-scoped/SCOPED.txt"

if [[ ! -f "${autoload}" ]]; then
    echo "Version-scoped autoload not found: ${autoload}" >&2
    exit 1
fi

if [[ ! -f "${metadata}" ]]; then
    echo "Version-scoped metadata not found: ${metadata}" >&2
    exit 1
fi

public_namespace="$(sed -n 's/^Public namespace: //p' "${metadata}" | head -n 1)"

if [[ -z "${public_namespace}" ]]; then
    echo "Unable to determine public namespace from ${metadata}" >&2
    exit 1
fi

AUTOLOAD="${autoload}" PUBLIC_NAMESPACE="${public_namespace}" php -r '
require getenv("AUTOLOAD");
$class = getenv("PUBLIC_NAMESPACE") . "\\Laposta";
new $class("x");
echo "ok";
'
