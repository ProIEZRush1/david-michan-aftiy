#!/usr/bin/env sh
# Ejecuta la prueba E2E headless contra la app local.
# En entornos sin las librerías de sistema del navegador (~/.cache no escribible,
# faltan libglib/X11/fuentes), si existe un prefijo local de libs en /tmp/deblibs
# lo usamos vía LD_LIBRARY_PATH + fontconfig. Si el entorno ya trae todo, basta
# con `node tests/e2e/smoke.mjs`.
set -e
cd "$(dirname "$0")/../.."

PREFIX=/tmp/deblibs/prefix
if [ -d "$PREFIX" ]; then
    export LD_LIBRARY_PATH="$PREFIX/usr/lib/x86_64-linux-gnu:$PREFIX/lib/x86_64-linux-gnu:$PREFIX/usr/lib:$PREFIX/lib:${LD_LIBRARY_PATH:-}"
    [ -f /tmp/deblibs/fonts.conf ] && export FONTCONFIG_FILE=/tmp/deblibs/fonts.conf
    [ -d /tmp/pwhome ] || mkdir -p /tmp/pwhome
    export HOME=/tmp/pwhome
    export XDG_CACHE_HOME=/tmp/pwhome/.cache
fi

exec node tests/e2e/smoke.mjs
