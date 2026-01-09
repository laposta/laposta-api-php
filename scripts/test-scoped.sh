#!/usr/bin/env bash

set -euo pipefail

root_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
autoload="${root_dir}/build/scoped/autoload.php"

if [[ ! -f "${autoload}" ]]; then
    echo "Scoped autoload not found: ${autoload}" >&2
    exit 1
fi

php -r "require '${autoload}'; new LapostaApi\\Laposta('x'); echo 'ok';"
